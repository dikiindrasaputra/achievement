<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Title & Action -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Achievement Input</h2>
                <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    Import Text
                </button>
            </div>

            <!-- Week Day Selector & Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 p-4">
                {{-- Year Selector --}}
                <div class="flex items-center gap-4 mb-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tahun:</label>
                    <select id="yearPicker" onchange="changeYear()" class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" {{ $date->format('Y') == $year ? 'selected' : '' }}>
                                {{ $year }}{{ $year == now()->format('Y') ? ' (Sekarang)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Week Navigation --}}
                @if ($weekDays)
                    <div class="flex items-center justify-between mb-4">
                        <a href="{{ route('achievements.index', array_merge(request()->query(), ['date' => $prevWeekDate])) }}" 
                           class="inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Minggu Sebelumnya
                        </a>
                        <div class="text-center">
                            <span class="text-sm font-semibold text-gray-700">
                                Minggu {{ $weekDays[0]['day_number'] > 1 ? '' : '' }}{{ \Carbon\Carbon::parse($weekDays[0]['date'])->format('d M') }} - {{ \Carbon\Carbon::parse($weekDays[6]['date'])->format('d M Y') }}
                            </span>
                        </div>
                        <a href="{{ route('achievements.index', array_merge(request()->query(), ['date' => $nextWeekDate])) }}" 
                           class="inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors">
                            Minggu Berikutnya
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>

                    {{-- Day Buttons --}}
                    <div class="flex gap-2 mb-4">
                        @foreach ($weekDays as $day)
                            <a href="{{ route('achievements.index', array_merge(request()->query(), ['date' => $day['date']])) }}" 
                               class="flex-1 text-center px-3 py-3 rounded-lg border-2 transition-all {{ $day['is_selected'] ? 'border-orange-500 bg-orange-50 text-orange-700 font-bold shadow-sm' : ($day['is_today'] ? 'border-blue-300 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-gray-700') }}">
                                <div class="text-xs font-medium uppercase">{{ $day['day_name'] }}</div>
                                <div class="text-lg font-bold leading-tight">Day {{ $day['day_number'] }}</div>
                                <div class="text-xs {{ $day['is_selected'] ? 'text-orange-600' : 'text-gray-500' }}">{{ $day['date_display'] }}</div>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Filters --}}
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract Type</label>
                        <select name="contract_type" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All</option>
                            <option value="dedicated" {{ request('contract_type') == 'dedicated' ? 'selected' : '' }}>Dedicated</option>
                            <option value="mitra" {{ request('contract_type') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle Type</label>
                        <select name="vehicle_type" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All</option>
                            <option value="2wh" {{ request('vehicle_type') == '2wh' ? 'selected' : '' }}>2 Wheels</option>
                            <option value="4wh" {{ request('vehicle_type') == '4wh' ? 'selected' : '' }}>4 Wheels</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="NIP or Name" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">Filter</button>
                </form>
            </div>

            <!-- Ringkasan Mingguan -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Target Hari Ini</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($weeklySummary['total_target']) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Pencapaian Hari Ini</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($weeklySummary['total_achievement']) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Progress</p>
                    <div class="flex items-center gap-2">
                        <p class="text-2xl font-bold {{ $weeklySummary['progress'] >= 100 ? 'text-green-600' : ($weeklySummary['progress'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $weeklySummary['progress'] }}%
                        </p>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="{{ $weeklySummary['progress'] >= 100 ? 'bg-green-600' : ($weeklySummary['progress'] >= 50 ? 'bg-yellow-600' : 'bg-red-600') }} h-2 rounded-full" style="width: {{ min($weeklySummary['progress'], 100) }}%"></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Whitelist Hari Ini</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $weeklySummary['total_whitelisted'] }} orang</p>
                </div>
            </div>

            <!-- Input Achievement Section -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Input Pencapaian</h3>
                
                <!-- Manpower Picker -->
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Manpower</label>
                        <select id="manpowerPicker" onchange="loadManpowerInfo()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- Pilih Manpower --</option>
                            @foreach ($manpower as $person)
                                <option value="{{ $person->id }}" 
                                    data-nip="{{ $person->nip }}"
                                    data-name="{{ $person->full_name }}"
                                    data-contract="{{ $person->contract_type }}">
                                    {{ $person->nip }} - {{ $person->full_name }} ({{ ucfirst($person->contract_type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">&nbsp;</label>
                        <button type="button" onclick="document.getElementById('manpowerPicker').value = ''; hideManpowerInfo();" class="mt-1 block w-full px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 text-sm">Reset</button>
                    </div>
                </div>

                <!-- Manpower Info & Input Form (hidden by default) -->
                <div id="manpowerInfoSection" class="hidden">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-6 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">NIP</p>
                                <p id="infoNip" class="font-mono font-semibold"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Nama</p>
                                <p id="infoName" class="font-semibold"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Contract</p>
                                <p id="infoContract" class="font-semibold"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Week/Day</p>
                                <p id="infoWeekDay" class="font-semibold"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Daily Target</p>
                                <p id="infoDailyTarget" class="font-bold text-blue-600"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Carryover</p>
                                <p id="infoCarryover" class="font-semibold"></p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Effective Target</p>
                                    <p id="infoEffectiveTarget" class="text-xl font-bold text-blue-600"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Status</p>
                                    <p id="infoStatus"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Progress</p>
                                    <div id="infoProgress"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Form -->
                    <form action="{{ route('achievements.store') }}" method="POST" id="achievementForm">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                        <input type="hidden" name="manpower_id" id="inputManpowerId">
                        
                        <div class="grid grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Achievement</label>
                                <input type="number" name="achievement" id="inputAchievement" min="0" value="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center text-lg font-bold">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (optional)</label>
                                <input type="text" name="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <button type="submit" id="btnSave" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold">Simpan</button>
                            </div>
                            <div>
                                <button type="button" onclick="loadManpowerInfo()" class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">Refresh</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Whitelist Notice (hidden by default) -->
                <div id="whitelistNotice" class="hidden bg-purple-50 dark:bg-purple-900 border border-purple-200 dark:border-purple-700 rounded-lg p-4 text-center">
                    <p class="text-purple-700 dark:text-purple-300 font-semibold">
                        <svg class="w-5 h-5 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Manpower ini di-whitelist hari ini. Target = 0, tidak perlu input pencapaian.
                    </p>
                </div>
            </div>

            <!-- Daily Breakdown Section (hidden by default) -->
            <div id="dailyBreakdownSection" class="hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Detail Mingguan: <span id="breakdownName"></span></h3>
                    <button onclick="document.getElementById('dailyBreakdownSection').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Summary -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-6 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Daily Target</p>
                            <p id="bdDailyTarget" class="text-lg font-bold text-blue-600"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Weekly Target</p>
                            <p id="bdWeeklyTarget" class="text-lg font-bold text-blue-600"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Total Achievement</p>
                            <p id="bdTotalAchievement" class="text-lg font-bold text-green-600"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Hari Aktif</p>
                            <p id="bdDaysActive" class="text-lg font-bold"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Rata-rata/Hari Aktif</p>
                            <p id="bdAvgAchievement" class="text-lg font-bold"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Verdict</p>
                            <p id="bdVerdict" class="text-lg font-bold"></p>
                        </div>
                    </div>
                </div>

                <!-- Daily Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hari</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Target</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Carryover</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Effective Target</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Achievement</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Persentase</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody id="breakdownTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Status Table (Read Only) -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold">Status Semua Manpower</h3>
                </div>

                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contract</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Week/Day</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Target</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Carryover</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Effective Target</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Achievement</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($manpower as $person)
                            @php
                                $existingAchievement = $person->achievements->first();
                                $isWhitelisted = $person->whitelists->isNotEmpty();
                                $dailyTarget = $person->getActiveDailyTarget($date);
                                // Hitung carryover real-time dari pencapaian kemarin
                                $carryover = $person->getExpectedCarryover($date);
                                $effectiveTarget = $isWhitelisted ? 0 : ($dailyTarget + $carryover);
                                $achievementValue = $existingAchievement->achievement ?? 0;
                                $percentage = $effectiveTarget > 0 ? ($achievementValue / $effectiveTarget) * 100 : ($isWhitelisted ? 100 : 0);
                                $status = $isWhitelisted ? 'whitelisted' : ($percentage >= 100 ? 'achieved' : ($percentage >= 50 ? 'partial' : 'low'));
                            @endphp
                            <tr class="{{ $isWhitelisted ? 'bg-purple-50 dark:bg-purple-900' : '' }} hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" onclick="selectManpower({{ $person->id }})">
                                <td class="px-4 py-3 text-sm font-mono">{{ $person->nip }}</td>
                                <td class="px-4 py-3 text-sm">{{ $person->full_name }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $person->contract_type == 'dedicated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($person->contract_type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    {{ $person->getWeekNumber($date) }}/{{ $person->getDayInWeek($date) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-semibold">
                                    {{ $dailyTarget }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-semibold {{ $carryover > 0 ? 'text-red-600' : ($carryover < 0 ? 'text-green-600' : 'text-gray-500') }}">
                                    @if ($carryover > 0)
                                        +{{ $carryover }}
                                    @else
                                        {{ $carryover }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-semibold {{ $isWhitelisted ? 'text-purple-600' : ($carryover < 0 ? 'text-green-600' : 'text-blue-600') }}">
                                    @if ($isWhitelisted)
                                        <span class="inline-flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                            WHITELIST
                                        </span>
                                    @else
                                        {{ $effectiveTarget }}
                                        @if ($carryover < 0)
                                            <span class="text-xs text-green-600 block">↓ Target berkurang</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-bold {{ $achievementValue > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $achievementValue }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($isWhitelisted)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                            Whitelist
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status == 'achieved' ? 'bg-green-100 text-green-800' : ($status == 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ number_format($percentage, 0) }}%
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if (!$isWhitelisted && $effectiveTarget > 0)
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="{{ $percentage >= 100 ? 'bg-green-600' : ($percentage >= 50 ? 'bg-yellow-600' : 'bg-red-600') }} h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                    @elseif ($isWhitelisted)
                                        <div class="w-full bg-purple-200 rounded-full h-2">
                                            <div class="bg-purple-600 h-2 rounded-full" style="width: 100%"></div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No manpower found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $manpower->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Import Achievements</h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('achievements.import') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paste Achievement Data</label>
                    <textarea name="import_data" rows="10" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" placeholder="[1298306]PERI YULIANTO	86
[1294275]RIFALDI ANGGI SAPUTRA	160
[1285927]M RAMADANI	0"></textarea>
                    <p class="mt-1 text-sm text-gray-500">Format: [NIP]Nama TAB Jumlah (copas dari Excel)</p>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Import</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dateValue = '{{ $date->format('Y-m-d') }}';
        const currentYear = {{ $date->format('Y') }};
        const apiUrl = '{{ route("api.achievements.manpower-info") }}';
        const breakdownApiUrl = '{{ route("api.achievements.daily-breakdown") }}';

        function changeYear() {
            const year = document.getElementById('yearPicker').value;
            // Navigate to Jan 1 of selected year
            window.location.href = '{{ route("achievements.index") }}?date=' + year + '-01-01';
        }

        function selectManpower(id) {
            document.getElementById('manpowerPicker').value = id;
            loadManpowerInfo();
        }

        function loadManpowerInfo() {
            const manpowerId = document.getElementById('manpowerPicker').value;
            if (!manpowerId) {
                hideManpowerInfo();
                return;
            }

            fetch(`${apiUrl}?manpower_id=${manpowerId}&date=${dateValue}`)
                .then(response => response.json())
                .then(data => {
                    if (data.is_whitelisted) {
                        document.getElementById('manpowerInfoSection').classList.add('hidden');
                        document.getElementById('whitelistNotice').classList.remove('hidden');
                        document.getElementById('dailyBreakdownSection').classList.add('hidden');
                        return;
                    }

                    document.getElementById('whitelistNotice').classList.add('hidden');
                    document.getElementById('manpowerInfoSection').classList.remove('hidden');

                    document.getElementById('inputManpowerId').value = data.manpower_id;
                    document.getElementById('infoNip').textContent = data.nip;
                    document.getElementById('infoName').textContent = data.full_name;
                    document.getElementById('infoContract').textContent = data.contract_type === 'dedicated' ? 'Dedicated' : 'Mitra';
                    document.getElementById('infoWeekDay').textContent = `Week ${data.week_number}/Day ${data.day_in_week}`;
                    document.getElementById('infoDailyTarget').textContent = data.daily_target;
                    document.getElementById('infoCarryover').textContent = data.carryover;
                    document.getElementById('infoEffectiveTarget').textContent = data.effective_target;
                    document.getElementById('inputAchievement').value = data.existing_achievement;

                    const percentage = data.effective_target > 0 ? (data.existing_achievement / data.effective_target) * 100 : 0;
                    const statusClass = percentage >= 100 ? 'bg-green-100 text-green-800' : (percentage >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                    const statusText = percentage >= 100 ? 'Achieved' : (percentage >= 50 ? 'Partial' : 'Low');
                    
                    document.getElementById('infoStatus').innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${statusText} ${Math.round(percentage)}%</span>`;
                    
                    const progressColor = percentage >= 100 ? 'bg-green-600' : (percentage >= 50 ? 'bg-yellow-600' : 'bg-red-600');
                    document.getElementById('infoProgress').innerHTML = `
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="${progressColor} h-2 rounded-full" style="width: ${Math.min(percentage, 100)}%"></div>
                        </div>
                    `;

                    // Load daily breakdown
                    loadDailyBreakdown(manpowerId);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat data manpower');
                });
        }

        function loadDailyBreakdown(manpowerId) {
            fetch(`${breakdownApiUrl}?manpower_id=${manpowerId}&date=${dateValue}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('dailyBreakdownSection').classList.remove('hidden');
                    document.getElementById('breakdownName').textContent = `${data.nip} - ${data.full_name}`;
                    
                    document.getElementById('bdDailyTarget').textContent = data.daily_target;
                    document.getElementById('bdWeeklyTarget').textContent = data.weekly_target;
                    document.getElementById('bdTotalAchievement').textContent = data.total_achievement;
                    document.getElementById('bdDaysActive').textContent = `${data.days_with_achievement} / ${data.days.length} hari`;
                    document.getElementById('bdAvgAchievement').textContent = data.avg_achievement;
                    
                    const verdictEl = document.getElementById('bdVerdict');
                    if (data.is_above_target) {
                        verdictEl.innerHTML = `<span class="text-green-600">✓ Above Target</span>`;
                    } else {
                        verdictEl.innerHTML = `<span class="text-red-600">✗ Below Target</span>`;
                    }

                    const tbody = document.getElementById('breakdownTableBody');
                    tbody.innerHTML = data.days.map(day => {
                        const statusBg = day.is_whitelisted ? 'bg-purple-50 dark:bg-purple-900' : (day.is_today ? 'bg-blue-50 dark:bg-blue-900' : '');
                        const percentageClass = day.percentage >= 100 ? 'text-green-600' : (day.percentage >= 50 ? 'text-yellow-600' : 'text-red-600');
                        const statusLabel = day.is_whitelisted ? '<span class="text-purple-600">Whitelisted</span>' : 
                            (day.percentage >= 100 ? '<span class="text-green-600">✓</span>' : 
                            (day.percentage > 0 ? `<span class="text-yellow-600">${day.percentage}%</span>` : '-'));
                        
                        return `
                            <tr class="${statusBg}">
                                <td class="px-4 py-3 text-center text-sm">${day.day_name}</td>
                                <td class="px-4 py-3 text-center text-sm font-mono">${day.date}</td>
                                <td class="px-4 py-3 text-center text-sm">${day.is_whitelisted ? 0 : day.daily_target}</td>
                                <td class="px-4 py-3 text-center text-sm ${day.carryover > 0 ? 'text-red-600' : (day.carryover < 0 ? 'text-green-600' : '')}">${day.carryover}</td>
                                <td class="px-4 py-3 text-center text-sm font-semibold">${day.effective_target}</td>
                                <td class="px-4 py-3 text-center text-sm font-bold">${day.achievement}</td>
                                <td class="px-4 py-3 text-center text-sm ${percentageClass}">${day.percentage}%</td>
                                <td class="px-4 py-3 text-center text-sm">${statusLabel}</td>
                            </tr>
                        `;
                    }).join('');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function hideManpowerInfo() {
            document.getElementById('manpowerInfoSection').classList.add('hidden');
            document.getElementById('whitelistNotice').classList.add('hidden');
            document.getElementById('dailyBreakdownSection').classList.add('hidden');
            document.getElementById('inputManpowerId').value = '';
        }
    </script>
</x-app-layout>
