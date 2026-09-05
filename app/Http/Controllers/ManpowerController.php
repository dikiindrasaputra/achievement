<?php

namespace App\Http\Controllers;

use App\Models\Manpower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManpowerController extends Controller
{
    public function index(Request $request)
    {
        $query = Manpower::query()
            ->when($request->contract_type, fn($q, $type) => $q->byContractType($type))
            ->when($request->vehicle_type, fn($q, $type) => $q->byVehicleType($type))
            ->when($request->search, fn($q, $search) => $q->search($search));

        if (!$request->has('show_inactive')) {
            $query->active();
        }

        $manpower = $query->orderBy('nip')->get();

        return view('manpower.index', compact('manpower'));
    }

    public function create()
    {
        return view('manpower.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|min:6|unique:manpower,nip',
            'full_name' => 'required|string|max:255',
            'vehicle_type' => 'required|in:2wh,4wh',
            'contract_type' => 'required|in:dedicated,mitra',
            'start_date' => 'required|date',
        ]);

        $validated['created_by'] = Auth::id();

        Manpower::create($validated);

        return redirect()->route('manpower.index')
            ->with('success', 'Manpower berhasil ditambahkan.');
    }

    public function edit(Manpower $manpower)
    {
        return view('manpower.edit', compact('manpower'));
    }

    public function update(Request $request, Manpower $manpower)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'vehicle_type' => 'required|in:2wh,4wh',
            'contract_type' => 'required|in:dedicated,mitra',
            'start_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $manpower->update($validated);

        return redirect()->route('manpower.index')
            ->with('success', 'Manpower berhasil diperbarui.');
    }

    public function destroy(Manpower $manpower)
    {
        $manpower->update(['is_active' => false]);

        return redirect()->route('manpower.index')
            ->with('success', 'Manpower berhasil dinonaktifkan.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_data' => 'required|string',
            'vehicle_type' => 'required|in:2wh,4wh',
            'contract_type' => 'required|in:dedicated,mitra',
            'start_date' => 'required|date',
        ]);

        $lines = explode("\n", $request->import_data);
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/\[(\d{6,10})\](.+)/', $line, $matches)) {
                $nip = $matches[1];
                $fullName = trim($matches[2]);

                if (Manpower::where('nip', $nip)->exists()) {
                    $skipped++;
                    $errors[] = "NIP {$nip} sudah ada";
                    continue;
                }

                Manpower::create([
                    'nip' => $nip,
                    'full_name' => $fullName,
                    'vehicle_type' => $request->vehicle_type,
                    'contract_type' => $request->contract_type,
                    'start_date' => $request->start_date,
                    'created_by' => Auth::id(),
                ]);

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

        return redirect()->route('manpower.index')
            ->with('success', $message);
    }
}
