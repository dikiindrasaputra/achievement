<?php

namespace App\Http\Controllers;

use App\Models\Target;
use App\Models\TargetItem;
use App\Models\Manpower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TargetController extends Controller
{
    public function index(Request $request)
    {
        // Year filter
        $minYear = Target::min('year') ?? (int) now()->format('Y');
        $maxYear = (int) now()->format('Y') + 1;
        $availableYears = range($minYear, $maxYear);
        $selectedYear = $request->year ? (int) $request->year : (int) now()->format('Y');

        $targets = Target::with(['items.manpower'])
            ->where('year', $selectedYear)
            ->orderByDesc('week_number')
            ->get();

        $selectedTarget = null;
        if ($request->target_id) {
            $selectedTarget = Target::with(['items.manpower'])->find($request->target_id);
        } elseif ($targets->isNotEmpty()) {
            $selectedTarget = $targets->first();
        }

        return view('targets.index', compact('targets', 'selectedTarget', 'availableYears', 'selectedYear'));
    }

    public function create()
    {
        $manpower = Manpower::active()->orderBy('nip')->get();
        $currentYear = (int) now()->format('Y');
        $currentWeek = now()->weekOfYear;

        return view('targets.create', compact('manpower', 'currentYear', 'currentWeek'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2024',
            'week_number' => 'required|integer|min:1|max:53',
            'monthly_target' => 'required|integer|min:0',
            'weekly_target_global' => 'required|integer|min:0',
            'apply_all_days' => 'boolean',
            'daily_target_global' => 'nullable|integer|min:0',
            'day_1_global' => 'nullable|integer|min:0',
            'day_2_global' => 'nullable|integer|min:0',
            'day_3_global' => 'nullable|integer|min:0',
            'day_4_global' => 'nullable|integer|min:0',
            'day_5_global' => 'nullable|integer|min:0',
            'day_6_global' => 'nullable|integer|min:0',
            'manpower_ids' => 'required|array|min:1',
        ]);

        // Auto-calculate start_date dan end_date dari year + week_number
        $startDate = Target::calcStartDate($validated['year'], $validated['week_number']);
        $endDate = Target::calcEndDate($validated['year'], $validated['week_number']);

        DB::beginTransaction();

        try {
            $target = Target::create([
                'name' => $validated['name'],
                'year' => $validated['year'],
                'week_number' => $validated['week_number'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'monthly_target' => $validated['monthly_target'],
                'apply_all_days' => isset($validated['apply_all_days']),
                'created_by' => Auth::id(),
            ]);

            $applyAll = isset($validated['apply_all_days']);
            $weeklyTarget = $validated['weekly_target_global'];
            $dailyTarget = $validated['daily_target_global'] ?? 0;

            $manpower = Manpower::whereIn('id', $validated['manpower_ids'])->get();

            foreach ($validated['manpower_ids'] as $manpowerId) {
                $person = $manpower->first(fn($m) => $m->id === $manpowerId);

                $itemData = [
                    'target_id' => $target->id,
                    'manpower_id' => $manpowerId,
                    'daily_target' => $dailyTarget,
                    'weekly_target' => $weeklyTarget,
                ];

                if ($applyAll) {
                    $itemData['day_1'] = $dailyTarget;
                    $itemData['day_2'] = $dailyTarget;
                    $itemData['day_3'] = $dailyTarget;
                    $itemData['day_4'] = $dailyTarget;
                    $itemData['day_5'] = $dailyTarget;
                    $itemData['day_6'] = $dailyTarget;
                } else {
                    $itemData['day_1'] = $validated['day_1_global'] ?? 0;
                    $itemData['day_2'] = $validated['day_2_global'] ?? 0;
                    $itemData['day_3'] = $validated['day_3_global'] ?? 0;
                    $itemData['day_4'] = $validated['day_4_global'] ?? 0;
                    $itemData['day_5'] = $validated['day_5_global'] ?? 0;
                    $itemData['day_6'] = $validated['day_6_global'] ?? 0;
                }

                // Zero out days before manpower's start_date
                if ($person && $person->start_date) {
                    $personStart = \Carbon\Carbon::parse($person->start_date);
                    for ($d = 1; $d <= 6; $d++) {
                        $dayDate = $startDate->copy()->addDays($d - 1);
                        if ($personStart->gt($dayDate)) {
                            $itemData["day_{$d}"] = 0;
                        }
                    }
                }

                TargetItem::create($itemData);
            }

            DB::commit();

            return redirect()->route('targets.index')
                ->with('success', 'Target berhasil dibuat dengan ' . count($validated['manpower_ids']) . ' manpower.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat target: ' . $e->getMessage());
        }
    }

    public function show(Target $target)
    {
        $target->load(['items.manpower']);

        return view('targets.show', compact('target'));
    }

    public function edit(Target $target)
    {
        $target->load(['items.manpower']);
        $manpower = Manpower::active()->orderBy('nip')->get();

        return view('targets.edit', compact('target', 'manpower'));
    }

    public function update(Request $request, Target $target)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2024',
            'week_number' => 'required|integer|min:1|max:53',
            'monthly_target' => 'required|integer|min:0',
            'weekly_target_global' => 'required|integer|min:0',
            'apply_all_days' => 'boolean',
            'daily_target_global' => 'nullable|integer|min:0',
            'day_1_global' => 'nullable|integer|min:0',
            'day_2_global' => 'nullable|integer|min:0',
            'day_3_global' => 'nullable|integer|min:0',
            'day_4_global' => 'nullable|integer|min:0',
            'day_5_global' => 'nullable|integer|min:0',
            'day_6_global' => 'nullable|integer|min:0',
            'manpower_ids' => 'required|array|min:1',
        ]);

        // Auto-calculate start_date dan end_date dari year + week_number
        $startDate = Target::calcStartDate($validated['year'], $validated['week_number']);
        $endDate = Target::calcEndDate($validated['year'], $validated['week_number']);

        DB::beginTransaction();

        try {
            $target->update([
                'name' => $validated['name'],
                'year' => $validated['year'],
                'week_number' => $validated['week_number'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'monthly_target' => $validated['monthly_target'],
                'apply_all_days' => isset($validated['apply_all_days']),
            ]);

            $target->items()->delete();

            $applyAll = isset($validated['apply_all_days']);
            $weeklyTarget = $validated['weekly_target_global'];
            $dailyTarget = $validated['daily_target_global'] ?? 0;

            $manpower = Manpower::whereIn('id', $validated['manpower_ids'])->get();

            foreach ($validated['manpower_ids'] as $manpowerId) {
                $person = $manpower->first(fn($m) => $m->id === $manpowerId);

                $itemData = [
                    'target_id' => $target->id,
                    'manpower_id' => $manpowerId,
                    'daily_target' => $dailyTarget,
                    'weekly_target' => $weeklyTarget,
                ];

                if ($applyAll) {
                    $itemData['day_1'] = $dailyTarget;
                    $itemData['day_2'] = $dailyTarget;
                    $itemData['day_3'] = $dailyTarget;
                    $itemData['day_4'] = $dailyTarget;
                    $itemData['day_5'] = $dailyTarget;
                    $itemData['day_6'] = $dailyTarget;
                } else {
                    $itemData['day_1'] = $validated['day_1_global'] ?? 0;
                    $itemData['day_2'] = $validated['day_2_global'] ?? 0;
                    $itemData['day_3'] = $validated['day_3_global'] ?? 0;
                    $itemData['day_4'] = $validated['day_4_global'] ?? 0;
                    $itemData['day_5'] = $validated['day_5_global'] ?? 0;
                    $itemData['day_6'] = $validated['day_6_global'] ?? 0;
                }

                // Zero out days before manpower's start_date
                if ($person && $person->start_date) {
                    $personStart = \Carbon\Carbon::parse($person->start_date);
                    for ($d = 1; $d <= 6; $d++) {
                        $dayDate = $startDate->copy()->addDays($d - 1);
                        if ($personStart->gt($dayDate)) {
                            $itemData["day_{$d}"] = 0;
                        }
                    }
                }

                TargetItem::create($itemData);
            }

            DB::commit();

            return redirect()->route('targets.index')
                ->with('success', 'Target berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal update target: ' . $e->getMessage());
        }
    }

    public function destroy(Target $target)
    {
        $target->delete();

        return redirect()->route('targets.index')
            ->with('success', 'Target berhasil dihapus.');
    }

    public function getManpower(Request $request)
    {
        $query = Manpower::active();

        if ($request->contract_type) {
            $query->byContractType($request->contract_type);
        }

        if ($request->vehicle_type) {
            $query->byVehicleType($request->vehicle_type);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        $manpower = $query->orderBy('nip')->get();

        return response()->json($manpower);
    }
}
