<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Title -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Edit Target: {{ $target->name }}</h2>
            </div>

            <form action="{{ route('targets.update', $target) }}" method="POST" id="targetForm">
                @csrf
                @method('PUT')

                <!-- Target Info -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">Target Information</h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Target Name</label>
                            <input type="text" name="name" value="{{ old('name', $target->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Target</label>
                            <input type="number" name="monthly_target" value="{{ old('monthly_target', $target->monthly_target) }}" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('monthly_target') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Year & Week Selection -->
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year</label>
                            <input type="number" name="year" id="targetYear" value="{{ old('year', $target->year) }}" min="2024" required onchange="updateWeekDates()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Week Number</label>
                            <input type="number" name="week_number" id="targetWeekNumber" value="{{ old('week_number', $target->week_number) }}" min="1" max="53" required onchange="updateWeekDates()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('week_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Period (Auto-calculated)</label>
                            <div class="mt-1 block w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700" id="weekPeriodDisplay">
                                {{ $target->start_date->format('d M') }} - {{ $target->end_date->format('d M Y') }}
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Target -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Weekly Target (per orang)</label>
                            <input type="number" name="weekly_target_global" id="weekly_target_global" value="{{ old('weekly_target_global', $target->items->first()->weekly_target ?? 0) }}" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('weekly_target_global') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Apply All Days in Week</label>
                            <div class="mt-2 flex items-center h-10">
                                <input type="checkbox" name="apply_all_days" id="apply_all_days" value="1" {{ old('apply_all_days', $target->apply_all_days) ? 'checked' : '' }} onchange="toggleDayInputs()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label for="apply_all_days" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Centang = Daily Target masuk ke Day 1-6
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Target (saat Apply All Days) -->
                    @php $existingItem = $target->items->first(); @endphp
                    <div id="dailySection" class="{{ old('apply_all_days', $target->apply_all_days) ? '' : 'hidden' }}">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Daily Target (per orang)</label>
                                <input type="number" name="daily_target_global" id="daily_target_global" value="{{ old('daily_target_global', $existingItem->daily_target ?? 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('daily_target_global') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-gray-500">Angka ini akan masuk ke Day 1 sampai Day 6</p>
                            </div>
                        </div>
                    </div>

                    <!-- Day 1-6 Inputs (saat tidak Apply All Days) -->
                    <div id="daySection" class="{{ old('apply_all_days', $target->apply_all_days) ? 'hidden' : '' }}">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Target Per Hari (per orang)</label>
                        <div class="grid grid-cols-6 gap-4">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Day 1</label>
                                <input type="number" name="day_1_global" value="{{ old('day_1_global', $existingItem->day_1 ?? 0) }}" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Day 2</label>
                                <input type="number" name="day_2_global" value="{{ old('day_2_global', $existingItem->day_2 ?? 0) }}" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Day 3</label>
                                <input type="number" name="day_3_global" value="{{ old('day_3_global', $existingItem->day_3 ?? 0) }}" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Day 4</label>
                                <input type="number" name="day_4_global" value="{{ old('day_4_global', $existingItem->day_4 ?? 0) }}" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Day 5</label>
                                <input type="number" name="day_5_global" value="{{ old('day_5_global', $existingItem->day_5 ?? 0) }}" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Day 6</label>
                                <input type="number" name="day_6_global" value="{{ old('day_6_global', $existingItem->day_6 ?? 0) }}" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manpower Selection -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Select Manpower</h3>
                        <div class="flex gap-2">
                            <button type="button" onclick="selectAll()" class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">Select All</button>
                            <button type="button" onclick="deselectAll()" class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">Deselect All</button>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="flex gap-4 mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Contract Type</label>
                            <select id="filterContract" onchange="filterManpower()" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All</option>
                                <option value="dedicated">Dedicated</option>
                                <option value="mitra">Mitra</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Vehicle Type</label>
                            <select id="filterVehicle" onchange="filterManpower()" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All</option>
                                <option value="2wh">2 Wheels</option>
                                <option value="4wh">4 Wheels</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Search</label>
                            <input type="text" id="filterSearch" onkeyup="filterManpower()" placeholder="NIP or Name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    @error('manpower_ids') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror

                    <!-- Manpower Table -->
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-600 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left">
                                        <input type="checkbox" id="checkAll" onchange="toggleAll(this)" class="rounded border-gray-300 text-indigo-600">
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Contract</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Current Week</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($manpower as $person)
                                    @php
                                        $isInTarget = $target->items->contains('manpower_id', $person->id);
                                    @endphp
                                    <tr class="manpower-row" 
                                        data-contract="{{ $person->contract_type }}" 
                                        data-vehicle="{{ $person->vehicle_type }}"
                                        data-nip="{{ $person->nip }}"
                                        data-name="{{ strtolower($person->full_name) }}">
                                        <td class="px-4 py-2">
                                            <input type="checkbox" name="manpower_ids[]" value="{{ $person->id }}" {{ $isInTarget ? 'checked' : '' }} class="manpower-check rounded border-gray-300 text-indigo-600">
                                        </td>
                                        <td class="px-4 py-2 text-sm font-mono">{{ $person->nip }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $person->full_name }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $person->contract_type == 'dedicated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($person->contract_type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $person->vehicle_type == '2wh' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                {{ strtoupper($person->vehicle_type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm">{{ $person->start_date->format('d M Y') }}</td>
                                        <td class="px-4 py-2 text-sm text-center">Week {{ $person->getWeekNumber() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-between items-center">
                        <p class="text-sm text-gray-600">
                            Selected: <span id="selectedCount" class="font-semibold">{{ $target->items->count() }}</span> manpower
                        </p>
                        <div class="flex gap-2">
                            <a href="{{ route('targets.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Update Target</button>
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
            
            if (!year || !weekNumber) {
                document.getElementById('weekPeriodDisplay').textContent = '-';
                return;
            }

            // ISO 8601: January 4 is always in Week 1
            const jan4 = new Date(year, 0, 4);
            // Find Monday of Week 1 (dayOfWeek: 0=Sun,1=Mon...6=Sat → ISO: 1=Mon...7=Sun)
            const dayOfWeek = jan4.getDay() === 0 ? 7 : jan4.getDay();
            const week1Monday = new Date(jan4);
            week1Monday.setDate(jan4.getDate() - (dayOfWeek - 1));

            const startDate = new Date(week1Monday);
            startDate.setDate(week1Monday.getDate() + (weekNumber - 1) * 7);
            
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6);

            const options = { day: 'numeric', month: 'short' };
            const startStr = startDate.toLocaleDateString('en-US', options);
            const endStr = endDate.toLocaleDateString('en-US', { ...options, year: 'numeric' });
            
            document.getElementById('weekPeriodDisplay').textContent = `${startStr} - ${endStr}`;
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
                const matchContract = !contract || row.dataset.contract === contract;
                const matchVehicle = !vehicle || row.dataset.vehicle === vehicle;
                const matchSearch = !search || 
                    row.dataset.nip.includes(search) || 
                    row.dataset.name.includes(search);

                row.style.display = (matchContract && matchVehicle && matchSearch) ? '' : 'none';
            });
        }

        function selectAll() {
            document.querySelectorAll('.manpower-row:not([style*="display: none"]) .manpower-check').forEach(cb => {
                cb.checked = true;
            });
            updateTotal();
        }

        function deselectAll() {
            document.querySelectorAll('.manpower-check').forEach(cb => {
                cb.checked = false;
            });
            updateTotal();
        }

        function toggleAll(source) {
            document.querySelectorAll('.manpower-check').forEach(cb => {
                cb.checked = source.checked;
            });
            updateTotal();
        }

        function updateTotal() {
            const checked = document.querySelectorAll('.manpower-check:checked').length;
            document.getElementById('selectedCount').textContent = checked;
        }

        // Initialize
        toggleDayInputs();
        updateTotal();
    </script>
</x-app-layout>
