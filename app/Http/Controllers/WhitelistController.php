<?php

namespace App\Http\Controllers;

use App\Models\Whitelist;
use App\Models\Manpower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WhitelistController extends Controller
{
    public function index(Request $request)
    {
        $manpowerModel = new Manpower();
        $now = Carbon::now();

        // Week navigation
        $weekNumber = $request->week_number ? (int) $request->week_number : $manpowerModel->getWeekNumber($now);
        $year = $request->year ? (int) $request->year : $now->year;
        $weekStart = Manpower::getYearStart(Carbon::createFromDate($year, 1, 4))->copy()->addWeeks($weekNumber - 1);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // Build week days (ISO 8601: Mon=1..Sun=7)
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

        // Paginated manpower query
        $perPage = 20;
        $page = $request->page ?? 1;

        $paginator = Manpower::active()
            ->with(['whitelists' => function ($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('date', [$weekStart, $weekEnd]);
            }])
            ->when($request->contract_type, fn($q, $type) => $q->byContractType($type))
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->orderBy('nip')
            ->paginate($perPage, ['*'], 'page', $page);

        $manpower = $paginator->values();

        if ($request->ajax() || $request->wantsJson()) {
            $rows = view('whitelists._rows', compact('manpower', 'weekDays', 'weekStart', 'weekEnd'))->render();
            return response()->json([
                'html' => $rows,
                'hasMore' => $paginator->hasMorePages(),
            ]);
        }

        return view('whitelists.index', compact('manpower', 'weekDays', 'weekNumber', 'year', 'weekStart', 'weekEnd'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'manpower_id' => 'required|exists:manpower,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        if (!Whitelist::isAvailable($validated['manpower_id'], $validated['date'])) {
            return back()->with('error', 'Whitelist quota habis untuk minggu ini.');
        }

        Whitelist::create([
            'manpower_id' => $validated['manpower_id'],
            'date' => $validated['date'],
            'reason' => $validated['reason'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Whitelist berhasil ditambahkan.');
    }

    public function destroy(Whitelist $whitelist)
    {
        $whitelist->delete();

        return back()->with('success', 'Whitelist berhasil dihapus.');
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'manpower_ids' => 'required|array|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $added = 0;
        $skipped = 0;

        foreach ($validated['manpower_ids'] as $manpowerId) {
            if (Whitelist::isAvailable($manpowerId, $validated['date'])) {
                Whitelist::create([
                    'manpower_id' => $manpowerId,
                    'date' => $validated['date'],
                    'reason' => $validated['reason'] ?? null,
                    'created_by' => Auth::id(),
                ]);
                $added++;
            } else {
                $skipped++;
            }
        }

        return back()->with('success', "Whitelist: {$added} ditambahkan, {$skipped} dilewati (quota habis).");
    }
}
