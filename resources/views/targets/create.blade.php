<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Header: Back + Title --}}
            <div class="flex items-center gap-3 mb-6">
                <a href="{{ route('targets.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 transition shrink-0">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200">Buat Target Baru</h2>
            </div>

            <form action="{{ route('targets.store') }}" method="POST" id="targetForm">
                @csrf

                {{-- Target Info --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 mb-4">
                    <h3 class="text-sm font-semibold mb-3 text-gray-700">Informasi Target</h3>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Target</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm" placeholder="e.g., Target September 2026">
                            @error('name') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Monthly Target</label>
                            <input type="number" name="monthly_target" value="{{ old('monthly_target', 0) }}" min="0" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                            @error('monthly_target') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Year & Week --}}
                    <div class="grid grid-cols-3 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                            <input type="number" name="year" id="targetYear" value="{{ old('year', $currentYear) }}" min="2024" required onchange="updateWeekDates()" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                            @error('year') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Minggu</label>
                            <input type="number" name="week_number" id="targetWeekNumber" value="{{ old('week_number', $currentWeek) }}" min="1" max="53" required onchange="updateWeekDates()" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                            @error('week_number') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Periode</label>
                            <div class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600 min-h-[38px] flex items-center" id="weekPeriodDisplay">-</div>
                        </div>
                    </div>

                    {{-- Weekly Target --}}
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Weekly Target/orang</label>
                            <input type="number" name="weekly_target_global" id="weekly_target_global" value="{{ old('weekly_target_global', 0) }}" min="0" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                            @error('weekly_target_global') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Apply All Days</label>
                            <div class="flex items-center h-10">
                                <input type="checkbox" name="apply_all_days" id="apply_all_days" value="1" {{ old('apply_all_days', 1) ? 'checked' : '' }} onchange="toggleDayInputs()" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                <label for="apply_all_days" class="ml-2 text-xs text-gray-500">Daily → Day 1-6</label>
                            </div>
                        </div>
                    </div>

                    {{-- Daily Target (apply_all_days) --}}
                    <div id="dailySection" class="{{ old('apply_all_days', 1) ? '' : 'hidden' }}">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Daily Target/orang</label>
                                <input type="number" name="daily_target_global" id="daily_target_global" value="{{ old('daily_target_global', 0) }}" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                                @error('daily_target_global') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                                <p class="text-[10px] text-gray-400 mt-0.5">Masuk ke Day 1-6</p>
                            </div>
                        </div>
                    </div>

                    {{-- Day 1-6 (custom) --}}
                    <div id="daySection" class="{{ old('apply_all_days', 1) ? 'hidden' : '' }}">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Target Per Hari</label>
                        <div class="grid grid-cols-6 gap-2">
                            @foreach([1,2,3,4,5,6] as $d)
                                <div>
                                    <label class="block text-[10px] text-gray-400 mb-0.5">D{{ $d }}</label>
                                    <input type="number" name="day_{{ $d }}_global" value="{{ old("day_{$d}_global", 0) }}" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm text-center">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Manpower Selection --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-semibold text-gray-700">Pilih Manpower</h3>
                        <div class="flex gap-1.5">
                            <button type="button" onclick="selectAll()" class="px-2.5 py-1 bg-green-600 text-white text-[11px] font-medium rounded-lg hover:bg-green-700 transition">All</button>
                            <button type="button" onclick="deselectAll()" class="px-2.5 py-1 bg-gray-500 text-white text-[11px] font-medium rounded-lg hover:bg-gray-600 transition">Clear</button>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="flex gap-2 mb-3">
                        <select id="filterContract" onchange="filterManpower()" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
                            <option value="">Semua Kontrak</option>
                            <option value="dedicated">Dedicated</option>
                            <option value="mitra">Mitra</option>
                        </select>
                        <select id="filterVehicle" onchange="filterManpower()" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
                            <option value="">Semua Kendaraan</option>
                            <option value="2wh">2 Wheels</option>
                            <option value="4wh">4 Wheels</option>
                        </select>
                        <input type="text" id="filterSearch" onkeyup="filterManpower()" placeholder="Cari..." class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
                    </div>

                    @error('manpower_ids') <p class="text-red-500 text-[11px] mb-2">{{ $message }}</p> @enderror

                    {{-- Manpower Table --}}
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr class="text-[11px] text-gray-500">
                                    <th class="px-2 py-2 text-left"><input type="checkbox" id="checkAll" onchange="toggleAll(this)" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"></th>
                                    <th class="px-2 py-2 text-left">NIP</th>
                                    <th class="px-2 py-2 text-left">Name</th>
                                    <th class="px-2 py-2 text-left hidden sm:table-cell">Contract</th>
                                    <th class="px-2 py-2 text-left hidden sm:table-cell">Vehicle</th>
                                    <th class="px-2 py-2 text-left hidden sm:table-cell">Start</th>
                                    <th class="px-2 py-2 text-center hidden sm:table-cell">Week</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($manpower as $person)
                                    <tr class="manpower-row hover:bg-gray-50"
                                        data-contract="{{ $person->contract_type }}"
                                        data-vehicle="{{ $person->vehicle_type }}"
                                        data-nip="{{ $person->nip }}"
                                        data-name="{{ strtolower($person->full_name) }}">
                                        <td class="px-2 py-2">
                                            <input type="checkbox" name="manpower_ids[]" value="{{ $person->id }}" class="manpower-check rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                        </td>
                                        <td class="px-2 py-2 font-mono text-xs">{{ $person->nip }}</td>
                                        <td class="px-2 py-2">
                                            <div class="font-medium text-xs truncate max-w-[120px] sm:max-w-none">{{ $person->full_name }}</div>
                                            <div class="text-[10px] text-gray-400 sm:hidden">
                                                <span class="px-1 py-0.5 rounded {{ $person->contract_type == 'dedicated' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($person->contract_type) }}</span>
                                                <span class="px-1 py-0.5 rounded {{ $person->vehicle_type == '2wh' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">{{ strtoupper($person->vehicle_type) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-2 py-2 hidden sm:table-cell">
                                            <span class="px-2 inline-flex text-xs font-semibold rounded-full {{ $person->contract_type == 'dedicated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($person->contract_type) }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-2 hidden sm:table-cell">
                                            <span class="px-2 inline-flex text-xs font-semibold rounded-full {{ $person->vehicle_type == '2wh' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                {{ strtoupper($person->vehicle_type) }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-2 text-xs hidden sm:table-cell">{{ $person->start_date->format('d M Y') }}</td>
                                        <td class="px-2 py-2 text-xs text-center hidden sm:table-cell">Wk {{ $person->getWeekNumber() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Bottom bar --}}
                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-xs text-gray-500">Terpilih: <span id="selectedCount" class="font-bold text-gray-800">0</span></p>
                        <div class="flex gap-2">
                            <a href="{{ route('targets.index') }}" class="px-3 py-2 bg-gray-200 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-300 transition">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">Buat Target</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateWeekDates() {
            const year = document.getElementById('targetYear').value;
            const weekNumber = document.getElementById('targetWeekNumber').value;
            if (!year || !weekNumber) { document.getElementById('weekPeriodDisplay').textContent = '-'; return; }
            const jan4 = new Date(year, 0, 4);
            const dayOfWeek = jan4.getDay() === 0 ? 7 : jan4.getDay();
            const week1Monday = new Date(jan4);
            week1Monday.setDate(jan4.getDate() - (dayOfWeek - 1));
            const startDate = new Date(week1Monday);
            startDate.setDate(week1Monday.getDate() + (weekNumber - 1) * 7);
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6);
            const opts = { day: 'numeric', month: 'short' };
            document.getElementById('weekPeriodDisplay').textContent =
                startDate.toLocaleDateString('en-US', opts) + ' - ' + endDate.toLocaleDateString('en-US', { ...opts, year: 'numeric' });
        }

        function toggleDayInputs() {
            const applyAll = document.getElementById('apply_all_days').checked;
            document.getElementById('dailySection').classList.toggle('hidden', !applyAll);
            document.getElementById('daySection').classList.toggle('hidden', applyAll);
        }

        function filterManpower() {
            const contract = document.getElementById('filterContract').value;
            const vehicle = document.getElementById('filterVehicle').value;
            const search = document.getElementById('filterSearch').value.toLowerCase();
            document.querySelectorAll('.manpower-row').forEach(row => {
                const match = (!contract || row.dataset.contract === contract) &&
                              (!vehicle || row.dataset.vehicle === vehicle) &&
                              (!search || row.dataset.nip.includes(search) || row.dataset.name.includes(search));
                row.style.display = match ? '' : 'none';
            });
        }

        function selectAll() {
            document.querySelectorAll('.manpower-row:not([style*="display: none"]) .manpower-check').forEach(cb => cb.checked = true);
            updateTotal();
        }

        function deselectAll() {
            document.querySelectorAll('.manpower-check').forEach(cb => cb.checked = false);
            updateTotal();
        }

        function toggleAll(source) {
            document.querySelectorAll('.manpower-check').forEach(cb => cb.checked = source.checked);
            updateTotal();
        }

        function updateTotal() {
            document.getElementById('selectedCount').textContent = document.querySelectorAll('.manpower-check:checked').length;
        }

        toggleDayInputs();
        updateWeekDates();
    </script>
</x-app-layout>
