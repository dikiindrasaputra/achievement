<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Manpower;
use App\Models\Target;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AchievementController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();

        // Year selector: available years from earliest target to current year + 1
        $minYear = Target::min('year') ?? (int) now()->format('Y');
        $maxYear = (int) now()->format('Y') + 1;
        $availableYears = range($minYear, $maxYear);

        $query = Manpower::active()
            ->with([
                'achievements' => function ($q) use ($date) {
                    $q->whereDate('date', $date);
                },
                'whitelists' => function ($q) use ($date) {
                    $q->whereDate('date', $date);
                },
            ])
            ->when($request->contract_type, fn($q, $type) => $q->byContractType($type))
            ->when($request->vehicle_type, fn($q, $type) => $q->byVehicleType($type))
            ->when($request->search, fn($q, $search) => $q->search($search));

        $manpower = $query->orderBy('nip')->paginate(50)->withQueryString();

        // Hitung ringkasan
        $weeklySummary = [
            'total_target' => 0,
            'total_achievement' => 0,
            'total_whitelisted' => 0,
        ];

        foreach ($manpower as $person) {
            $dailyTarget = $person->getActiveDailyTarget($date);
            $carryover = $person->getExpectedCarryover($date);
            $isWhitelisted = $person->whitelists->isNotEmpty();
            $effectiveTarget = $isWhitelisted ? 0 : ($dailyTarget + $carryover);

            $weeklySummary['total_target'] += $effectiveTarget;
            $existingAchievement = $person->achievements->first();
            $weeklySummary['total_achievement'] += $existingAchievement->achievement ?? 0;
            if ($isWhitelisted) {
                $weeklySummary['total_whitelisted']++;
            }
        }

        $weeklySummary['progress'] = $weeklySummary['total_target'] > 0
            ? round(($weeklySummary['total_achievement'] / $weeklySummary['total_target']) * 100, 1)
            : 0;

        // Hitung week days berdasarkan ISO 8601
        $manpowerModel = new Manpower();
        $weekNumber = $manpowerModel->getWeekNumber($date);
        $weekStart = $manpowerModel->getWeekStartDate($date);
        $weekEnd = $manpowerModel->getWeekEndDate($date);

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $dayDate = $weekStart->copy()->addDays($i);
            $weekDays[] = [
                'day_number' => $i + 1,
                'date' => $dayDate->format('Y-m-d'),
                'date_display' => $dayDate->format('d M'),
                'day_name' => $dayDate->translatedFormat('D'),
                'is_selected' => $dayDate->isSameDay($date),
                'is_today' => $dayDate->isToday(),
            ];
        }

        $prevWeekDate = $weekStart->copy()->subWeek()->format('Y-m-d');
        $nextWeekDate = $weekEnd->copy()->addDay()->format('Y-m-d');

        return view('achievements.index', compact('manpower', 'date', 'weeklySummary', 'weekDays', 'prevWeekDate', 'nextWeekDate', 'availableYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'manpower_id' => 'required|exists:manpower,id',
            'date' => 'required|date',
            'achievement' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $achievement = Achievement::saveWithCarryover(
            $validated['manpower_id'],
            $validated['date'],
            $validated['achievement'],
            $validated['notes'] ?? null,
            Auth::id()
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Achievement berhasil disimpan.']);
        }

        return redirect()->route('achievements.index', ['date' => $validated['date']])
            ->with('success', 'Achievement berhasil disimpan.');
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'achievements' => 'required|array',
            'achievements.*.manpower_id' => 'required|exists:manpower,id',
            'achievements.*.achievement' => 'required|integer|min:0',
            'achievements.*.notes' => 'nullable|string|max:255',
        ]);

        foreach ($validated['achievements'] as $item) {
            Achievement::saveWithCarryover(
                $item['manpower_id'],
                $validated['date'],
                $item['achievement'],
                $item['notes'] ?? null,
                Auth::id()
            );
        }

        return redirect()->route('achievements.index', ['date' => $validated['date']])
            ->with('success', count($validated['achievements']) . ' achievements berhasil disimpan.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_data' => 'required|string',
            'date' => 'required|date',
        ]);

        $lines = explode("\n", $request->import_data);
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Format: [NIP]Name\tAchievement
            if (preg_match('/\[(\d{6,10})\][^\t]*\t(\d+)/', $line, $matches)) {
                $nip = $matches[1];
                $achievement = (int) $matches[2];

                $manpower = Manpower::where('nip', $nip)->first();
                if (!$manpower) {
                    $skipped++;
                    $errors[] = "NIP {$nip} tidak ditemukan";
                    continue;
                }

                // Check if target exists for this manpower on this date
                $date = Carbon::parse($request->date);
                $hasTarget = $manpower->targets()
                    ->whereHas('target', function ($q) use ($date) {
                        $q->where('start_date', '<=', $date)
                            ->where('end_date', '>=', $date);
                    })->exists();

                if (!$hasTarget) {
                    $skipped++;
                    $errors[] = "NIP {$nip} tidak ada target";
                    continue;
                }

                Achievement::saveWithCarryover(
                    $manpower->id,
                    $request->date,
                    $achievement,
                    null,
                    Auth::id()
                );

                $imported++;
            } else {
                $skipped++;
                $errors[] = "Format salah: {$line}";
            }
        }

        $message = "Import selesai: {$imported} berhasil, {$skipped} dilewati";
        if (!empty($errors)) {
            $message .= ". Errors: " . implode(', ', array_slice($errors, 0, 5));
        }

        return redirect()->route('achievements.index', ['date' => $request->date])
            ->with('success', $message);
    }

    public function weeklyProductivity(Request $request, Manpower $manpower)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();

        $data = Achievement::getWeeklyAccumulation($manpower->id, $date);

        return view('achievements.weekly-productivity', compact('manpower', 'data'));
    }

    public function storeWeeklyAccumulation(Request $request, Manpower $manpower)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($request->date);
        $weekStart = $manpower->getWeekStartDate($date);
        $weekEnd = $manpower->getWeekEndDate($date);

        $dailyTarget = $manpower->getActiveDailyTarget($date);
        $weeklyTarget = $manpower->getActiveWeeklyTarget($date);

        $achievements = Achievement::where('manpower_id', $manpower->id)
            ->whereBetween('date', [$weekStart, $date])
            ->orderBy('date')
            ->get();

        $totalAchievement = $achievements->sum('achievement');
        $totalCarryover = $achievements->last()->carryover ?? 0;

        $remainingDays = max(1, $date->diffInDays($weekEnd));
        $remainingTarget = max(0, $weeklyTarget - $totalAchievement);
        $suggestedDaily = ceil($remainingTarget / $remainingDays);

        $currentDate = $date->copy()->addDay();
        while ($currentDate->lte($weekEnd) && $currentDate->year == $date->year) {
            $prevDate = $currentDate->copy()->subDay();
            $prevAchievement = Achievement::where('manpower_id', $manpower->id)
                ->where('date', $prevDate)
                ->first();

            $carryover = $prevAchievement ? $prevAchievement->calculateNextDayCarryover() : 0;

            Achievement::updateOrCreate(
                [
                    'manpower_id' => $manpower->id,
                    'date' => $currentDate,
                ],
                [
                    'carryover' => $carryover,
                    'notes' => "Auto-suggested: {$suggestedDaily}/day",
                ]
            );

            $currentDate->addDay();
        }

        return redirect()->route('achievements.index', ['date' => $request->date])
            ->with('success', "Weekly accumulation applied. Suggested daily: {$suggestedDaily}");
    }

    public function getManpowerInfo(Request $request)
    {
        $request->validate([
            'manpower_id' => 'required|exists:manpower,id',
            'date' => 'required|date',
        ]);

        $manpower = Manpower::findOrFail($request->manpower_id);
        $date = Carbon::parse($request->date);

        $dailyTarget = $manpower->getActiveDailyTarget($date);
        $existingAchievement = $manpower->achievements()
            ->whereDate('date', $date)
            ->first();
        $isWhitelisted = $manpower->whitelists()
            ->whereDate('date', $date)
            ->exists();

        // Gunakan getExpectedCarryover untuk hitung real-time
        $carryover = $manpower->getExpectedCarryover($date);
        $effectiveTarget = $isWhitelisted ? 0 : ($dailyTarget + $carryover);
        $achievementValue = $existingAchievement->achievement ?? 0;

        return response()->json([
            'manpower_id' => $manpower->id,
            'nip' => $manpower->nip,
            'full_name' => $manpower->full_name,
            'contract_type' => $manpower->contract_type,
            'vehicle_type' => $manpower->vehicle_type,
            'week_number' => $manpower->getWeekNumber($date),
            'day_in_week' => $manpower->getDayInWeek($date),
            'daily_target' => $dailyTarget,
            'carryover' => $carryover,
            'effective_target' => $effectiveTarget,
            'existing_achievement' => $achievementValue,
            'is_whitelisted' => $isWhitelisted,
            'has_existing' => $existingAchievement !== null,
        ]);
    }

    public function getDailyBreakdown(Request $request)
    {
        $request->validate([
            'manpower_id' => 'required|exists:manpower,id',
            'date' => 'required|date',
        ]);

        $manpower = Manpower::findOrFail($request->manpower_id);
        $date = Carbon::parse($request->date);

        // Hitung tanggal awal dan akhir minggu
        $weekStart = $manpower->getWeekStartDate($date);
        $weekEnd = $manpower->getWeekEndDate($date);

        // Ambil semua achievement dalam minggu ini
        $achievements = $manpower->achievements()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get()
            ->keyBy(fn($a) => $a->date->format('Y-m-d'));

        // Ambul semua whitelist dalam minggu ini
        $whitelists = $manpower->whitelists()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get()
            ->keyBy(fn($w) => $w->date->format('Y-m-d'));

        // Target harian
        $dailyTarget = $manpower->getActiveDailyTarget($date);
        $weeklyTarget = $manpower->getActiveWeeklyTarget($date);

        // Build data per hari
        $days = [];
        $totalAchievement = 0;
        $totalTarget = 0;
        $daysWithAchievement = 0;

        $currentDate = $weekStart->copy();
        while ($currentDate->lte($weekEnd)) {
            $dateStr = $currentDate->format('Y-m-d');
            $isWhitelisted = $whitelists->has($dateStr);
            $dayAchievement = $achievements->get($dateStr);

            // Hitung carryover untuk hari ini
            $carryover = $manpower->getExpectedCarryover($currentDate);
            $effectiveTarget = $isWhitelisted ? 0 : ($dailyTarget + $carryover);
            $achievementValue = $dayAchievement ? $dayAchievement->achievement : 0;
            $percentage = $effectiveTarget > 0 ? round(($achievementValue / $effectiveTarget) * 100, 0) : 0;

            $days[] = [
                'date' => $dateStr,
                'day_name' => $currentDate->translatedFormat('l'),
                'daily_target' => $dailyTarget,
                'carryover' => $carryover,
                'effective_target' => $effectiveTarget,
                'achievement' => $achievementValue,
                'percentage' => $percentage,
                'is_whitelisted' => $isWhitelisted,
                'is_today' => $currentDate->isSameDay($date),
            ];

            if (!$isWhitelisted) {
                $totalTarget += $dailyTarget;
                $totalAchievement += $achievementValue;
                if ($achievementValue > 0) {
                    $daysWithAchievement++;
                }
            }

            $currentDate->addDay();
        }

        // Hitung rata-rata
        $avgAchievement = $daysWithAchievement > 0 ? round($totalAchievement / $daysWithAchievement, 0) : 0;
        $avgAllDays = count($days) > 0 ? round($totalAchievement / count($days), 0) : 0;

        return response()->json([
            'manpower_id' => $manpower->id,
            'nip' => $manpower->nip,
            'full_name' => $manpower->full_name,
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
            'daily_target' => $dailyTarget,
            'weekly_target' => $weeklyTarget,
            'total_achievement' => $totalAchievement,
            'total_target' => $totalTarget,
            'days_with_achievement' => $daysWithAchievement,
            'avg_achievement' => $avgAchievement,
            'avg_all_days' => $avgAllDays,
            'is_above_target' => $avgAchievement >= $dailyTarget,
            'days' => $days,
        ]);
    }
}
