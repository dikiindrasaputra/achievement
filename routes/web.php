<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ManpowerController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\WhitelistController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::get('/api/landing/load-more', [LandingPageController::class, 'loadMore'])->name('landing.load-more');

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    $now = \Carbon\Carbon::now();
    $manpowerModel = new \App\Models\Manpower();
    $weekNumber = $manpowerModel->getWeekNumber($now);
    $weekStart = $manpowerModel->getWeekStartDate($now);
    $weekEnd = $manpowerModel->getWeekEndDate($now);

    $contractType = $request->input('contract_type');
    $vehicleType = $request->input('vehicle_type');

    // Build week days
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

    $query = \App\Models\Manpower::active()
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
        ]);

    if ($contractType && in_array($contractType, ['dedicated', 'mitra'])) {
        $query->where('contract_type', $contractType);
    }
    if ($vehicleType && in_array($vehicleType, ['2wh', '4wh'])) {
        $query->where('vehicle_type', $vehicleType);
    }

    $manpower = $query->orderBy('nip')
        ->get()
        ->filter(fn($p) => $p->targets->isNotEmpty())
        ->values();

    $productivity = $manpower->map(function ($person) use ($weekDays, $now) {
        $targetItem = $person->targets->first();
        $dailyTarget = $targetItem->daily_target;
        $weeklyTarget = $targetItem->weekly_target;

        $achievements = $person->achievements->keyBy(fn($a) => $a->date->format('Y-m-d'));
        $whitelists = $person->whitelists->keyBy(fn($w) => $w->date->format('Y-m-d'));

        $days = [];
        $totalAchievement = 0;
        $activeDays = 0;
        $carryover = 0;

        foreach ($weekDays as $day) {
            $dateStr = $day['date'];
            $dayCarbon = \Carbon\Carbon::parse($dateStr);
            $dayNumber = $day['day_number'];
            $isWhitelisted = $whitelists->has($dateStr);
            $achievementRecord = $achievements->get($dateStr);
            $hasAchievement = $achievementRecord !== null;
            $achievementValue = $hasAchievement ? $achievementRecord->achievement : 0;

            if ($dayNumber === 1) {
                $carryover = 0;
            }

            if ($isWhitelisted) {
                $days[$dayNumber] = ['achievement' => null, 'is_whitelisted' => true, 'carryover' => 0, 'effective_target' => 0];
                continue;
            }

            $personStart = $person->start_date ? \Carbon\Carbon::parse($person->start_date) : null;
            if ($personStart && $dayCarbon->lt($personStart)) {
                $days[$dayNumber] = ['achievement' => 0, 'is_whitelisted' => false, 'carryover' => 0, 'effective_target' => 0];
                continue;
            }

            $effectiveTarget = $dailyTarget + $carryover;
            $dayCarryover = $carryover;

            if ($hasAchievement) {
                $totalAchievement += $achievementValue;
                $activeDays++;
                $carryover = $effectiveTarget - $achievementValue;
            }

            $days[$dayNumber] = [
                'achievement' => $achievementValue,
                'is_whitelisted' => false,
                'carryover' => $dayCarryover,
                'effective_target' => $effectiveTarget,
            ];
        }

        $weeklyAvg = $activeDays > 0 ? round($totalAchievement / $activeDays) : 0;
        $gap = $weeklyTarget - $totalAchievement;

        return [
            'name' => $person->full_name,
            'nip' => $person->nip,
            'contract_type' => $person->contract_type,
            'vehicle_type' => $person->vehicle_type,
            'daily_target' => $dailyTarget,
            'weekly_target' => $weeklyTarget,
            'days' => $days,
            'weekly_avg' => $weeklyAvg,
            'total_achievement' => $totalAchievement,
            'gap' => $gap,
            'active_days' => $activeDays,
        ];
    })->sortByDesc('total_achievement')->values();

    $productivity = $productivity->map(function ($item, $idx) {
        $item['rank'] = $idx + 1;
        return $item;
    });

    return view('dashboard', compact('productivity', 'weekDays', 'weekNumber', 'weekStart', 'weekEnd', 'contractType', 'vehicleType'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Manpower
    Route::resource('manpower', ManpowerController::class)->except(['show']);
    Route::post('manpower/import', [ManpowerController::class, 'import'])->name('manpower.import');

    // Targets
    Route::resource('targets', TargetController::class);
    Route::get('api/manpower', [TargetController::class, 'getManpower'])->name('api.manpower');

    // Achievements
    Route::get('achievements', [AchievementController::class, 'index'])->name('achievements.index');
    Route::post('achievements', [AchievementController::class, 'store'])->name('achievements.store');
    Route::post('achievements/bulk', [AchievementController::class, 'bulkStore'])->name('achievements.bulk-store');
    Route::post('achievements/import', [AchievementController::class, 'import'])->name('achievements.import');
    Route::get('achievements/{manpower}/weekly', [AchievementController::class, 'weeklyProductivity'])->name('achievements.weekly-productivity');
    Route::post('achievements/{manpower}/weekly/apply', [AchievementController::class, 'storeWeeklyAccumulation'])->name('achievements.store-weekly-accumulation');
    Route::get('api/achievements/manpower-info', [AchievementController::class, 'getManpowerInfo'])->name('api.achievements.manpower-info');
    Route::get('api/achievements/daily-breakdown', [AchievementController::class, 'getDailyBreakdown'])->name('api.achievements.daily-breakdown');

    // Whitelists
    Route::get('whitelists', [WhitelistController::class, 'index'])->name('whitelists.index');
    Route::post('whitelists', [WhitelistController::class, 'store'])->name('whitelists.store');
    Route::post('whitelists/bulk', [WhitelistController::class, 'bulkStore'])->name('whitelists.bulk-store');
    Route::delete('whitelists/{whitelist}', [WhitelistController::class, 'destroy'])->name('whitelists.destroy');
});

require __DIR__.'/auth.php';
