<?php

namespace App\Http\Controllers;

use App\Models\Manpower;
use App\Models\Achievement;
use App\Models\Whitelist;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    private function getWeekInfo()
    {
        $now = Carbon::now();
        $manpowerModel = new Manpower();
        $weekNumber = $manpowerModel->getWeekNumber($now);
        $weekStart = $manpowerModel->getWeekStartDate($now);
        $weekEnd = $manpowerModel->getWeekEndDate($now);

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $dayDate = $weekStart->copy()->addDays($i);
            $weekDays[] = [
                'day_number' => $i + 1,
                'date' => $dayDate->format('Y-m-d'),
                'date_display' => $dayDate->format('d M'),
                'day_name' => $dayDate->translatedFormat('D'),
                'is_past' => $dayDate->lt($now->copy()->startOfDay()),
                'is_today' => $dayDate->isSameDay($now),
            ];
        }

        return compact('now', 'weekNumber', 'weekStart', 'weekEnd', 'weekDays');
    }

    private function buildPersonData($person, $weekDays, $now)
    {
        $targetItem = $person->targets->first();
        $dailyTarget = $targetItem->daily_target;

        $achievements = $person->achievements->keyBy(fn($a) => $a->date->format('Y-m-d'));
        $whitelists = $person->whitelists->keyBy(fn($w) => $w->date->format('Y-m-d'));

        $days = [];
        $totalAchievement = 0;
        $carryover = 0;

        foreach ($weekDays as $day) {
            $dateStr = $day['date'];
            $dayCarbon = Carbon::parse($dateStr);
            $dayNumber = $day['day_number'];
            $isWhitelisted = $whitelists->has($dateStr);
            $achievementRecord = $achievements->get($dateStr);
            $hasAchievement = $achievementRecord !== null;
            $achievementValue = $hasAchievement ? $achievementRecord->achievement : null;

            if ($dayNumber === 1) $carryover = 0;

            if ($isWhitelisted) {
                $days[] = [
                    'day_number' => $dayNumber, 'date' => $dateStr,
                    'date_display' => $day['date_display'], 'day_name' => $day['day_name'],
                    'is_past' => $day['is_past'], 'is_today' => $day['is_today'],
                    'is_whitelisted' => true, 'daily_target' => $dailyTarget,
                    'carryover' => 0, 'effective_target' => 0, 'achievement' => 0,
                    'has_achievement' => false, 'status' => 'whitelisted',
                ];
                continue;
            }

            $personStart = $person->start_date ? Carbon::parse($person->start_date) : null;
            if ($personStart && $dayCarbon->lt($personStart)) {
                $days[] = [
                    'day_number' => $dayNumber, 'date' => $dateStr,
                    'date_display' => $day['date_display'], 'day_name' => $day['day_name'],
                    'is_past' => $day['is_past'], 'is_today' => $day['is_today'],
                    'is_whitelisted' => false, 'daily_target' => $dailyTarget,
                    'carryover' => 0, 'effective_target' => 0, 'achievement' => 0,
                    'has_achievement' => false, 'status' => 'not_joined',
                ];
                continue;
            }

            $effectiveTarget = $dailyTarget + $carryover;
            $dayCarryover = $carryover;

            if ($hasAchievement) {
                $totalAchievement += $achievementValue;
                $carryover = $effectiveTarget - $achievementValue;
            }

            $days[] = [
                'day_number' => $dayNumber, 'date' => $dateStr,
                'date_display' => $day['date_display'], 'day_name' => $day['day_name'],
                'is_past' => $day['is_past'], 'is_today' => $day['is_today'],
                'is_whitelisted' => false, 'daily_target' => $dailyTarget,
                'carryover' => $dayCarryover, 'effective_target' => $effectiveTarget,
                'achievement' => $achievementValue ?? 0, 'has_achievement' => $hasAchievement,
                'status' => $hasAchievement ? ($effectiveTarget > 0 && ($achievementValue / $effectiveTarget) >= 1 ? 'achieved' : (($achievementValue / $effectiveTarget) >= 0.5 ? 'partial' : 'low')) : 'pending',
            ];
        }

        $hasPastWhitelist = false;
        $futureDays = 0;
        foreach ($days as $d) {
            if ($d['is_whitelisted'] || $d['status'] === 'not_joined') {
                if (($d['is_past'] || $d['is_today']) && $d['is_whitelisted']) $hasPastWhitelist = true;
                continue;
            }
            if (!$d['has_achievement'] && !$d['is_past'] && !$d['is_today']) $futureDays++;
        }

        $currentDayNeedsInput = false;
        foreach ($days as $d) {
            if ($d['is_today'] && !$d['has_achievement'] && !$d['is_whitelisted'] && $d['status'] !== 'not_joined') {
                $currentDayNeedsInput = true;
                break;
            }
        }

        $remainingDays = $futureDays + ($currentDayNeedsInput ? 1 : 0);
        if (!$hasPastWhitelist && $currentDayNeedsInput) $remainingDays = max(1, $remainingDays);

        $weeklyTargetConst = $dailyTarget * 6;

        return [
            'id' => $person->id,
            'nip' => $person->nip,
            'name' => $person->full_name,
            'contract_type' => $person->contract_type,
            'vehicle_type' => $person->vehicle_type,
            'daily_target' => $dailyTarget,
            'weekly_target' => $weeklyTargetConst,
            'weekly_achievement' => $totalAchievement,
            'is_target_met' => $weeklyTargetConst > 0 ? $totalAchievement >= $weeklyTargetConst : true,
            'days' => $days,
            'last_carryover' => $carryover,
        ];
    }

    private function getManpowerQuery($weekStart, $weekEnd)
    {
        return Manpower::active()
            ->whereHas('targets', function ($q) use ($weekStart, $weekEnd) {
                $q->whereHas('target', function ($q2) use ($weekStart, $weekEnd) {
                    $q2->where('start_date', '<=', $weekEnd)
                        ->where('end_date', '>=', $weekStart);
                });
            })
            ->with([
                'targets' => function ($q) use ($weekStart, $weekEnd) {
                    $q->whereHas('target', function ($q2) use ($weekStart, $weekEnd) {
                        $q2->where('start_date', '<=', $weekEnd)
                            ->where('end_date', '>=', $weekStart);
                    })->with('target');
                },
                'achievements' => function ($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('date', [$weekStart, $weekEnd]);
                },
                'whitelists' => function ($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('date', [$weekStart, $weekEnd]);
                },
            ])
            ->orderBy('nip');
    }

    public function index()
    {
        extract($this->getWeekInfo());

        $perPage = 20;
        $paginator = $this->getManpowerQuery($weekStart, $weekEnd)
            ->paginate($perPage);

        $data = $paginator->map(fn($person) => $this->buildPersonData($person, $weekDays, $now));

        $topAchievers = Manpower::active()
            ->with([
                'targets' => function ($q) use ($weekStart, $weekEnd) {
                    $q->whereHas('target', function ($q2) use ($weekStart, $weekEnd) {
                        $q2->where('start_date', '<=', $weekEnd)
                            ->where('end_date', '>=', $weekStart);
                    })->with('target');
                },
                'achievements' => function ($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('date', [$weekStart, $weekEnd]);
                },
            ])
            ->get()
            ->filter(fn($p) => $p->targets->isNotEmpty())
            ->map(fn($person) => [
                'name' => $person->full_name,
                'nip' => $person->nip,
                'weekly_achievement' => $person->achievements->sum('achievement'),
                'weekly_target' => ($person->targets->first()->daily_target ?? 0) * 6,
            ])
            ->sortByDesc('weekly_achievement')
            ->take(5)
            ->values();

        $totalManpower = Manpower::active()
            ->whereHas('targets', function ($q) use ($weekStart, $weekEnd) {
                $q->whereHas('target', function ($q2) use ($weekStart, $weekEnd) {
                    $q2->where('start_date', '<=', $weekEnd)
                        ->where('end_date', '>=', $weekStart);
                });
            })->count();

        return view('landing', compact('data', 'weekDays', 'weekNumber', 'weekStart', 'weekEnd', 'topAchievers', 'totalManpower'))
            ->with('hasMore', $paginator->hasMorePages());
    }

    public function loadMore(Request $request)
    {
        extract($this->getWeekInfo());

        $page = $request->page ?? 2;
        $perPage = 20;

        $paginator = $this->getManpowerQuery($weekStart, $weekEnd)
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->map(fn($person) => $this->buildPersonData($person, $weekDays, $now));

        return response()->json([
            'data' => $data,
            'hasMore' => $paginator->hasMorePages(),
        ]);
    }
}
