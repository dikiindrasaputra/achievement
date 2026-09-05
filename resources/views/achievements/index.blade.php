<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Header: Back + Import --}}
            <div class="flex items-center justify-between mb-6">
                <a href="{{ route('dashboard') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 transition shrink-0">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import
                </button>
            </div>

            {{-- Year + Week Nav --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-4 p-4">
                <div class="flex items-center gap-3 mb-3">
                    <select id="yearPicker" onchange="changeYear()" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" {{ $date->format('Y') == $year ? 'selected' : '' }}>
                                {{ $year }}{{ $year == now()->format('Y') ? ' (Sekarang)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if ($weekDays)
                    {{-- Week Navigation --}}
                    <div class="flex items-center justify-between mb-3">
                        <a href="{{ route('achievements.index', array_merge(request()->query(), ['date' => $prevWeekDate])) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 transition">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <span class="text-xs font-semibold text-gray-600">{{ \Carbon\Carbon::parse($weekDays[0]['date'])->format('d M') }} - {{ \Carbon\Carbon::parse($weekDays[6]['date'])->format('d M Y') }}</span>
                        <a href="{{ route('achievements.index', array_merge(request()->query(), ['date' => $nextWeekDate])) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 transition">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    {{-- Day Slider --}}
                    <div class="flex gap-2 overflow-x-auto pb-2 snap-x snap-mandatory -mx-1 px-1" id="daySlider">
                        @foreach ($weekDays as $day)
                            <a href="{{ route('achievements.index', array_merge(request()->query(), ['date' => $day['date']])) }}"
                               class="snap-center shrink-0 w-[72px] text-center py-2.5 px-2 rounded-xl border-2 transition-all {{ $day['is_selected'] ? 'border-orange-500 bg-orange-50 text-orange-700 shadow-sm' : ($day['is_today'] ? 'border-blue-300 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-600') }}">
                                <div class="text-[10px] font-medium uppercase">{{ $day['day_name'] }}</div>
                                <div class="text-sm font-bold leading-tight">D{{ $day['day_number'] }}</div>
                                <div class="text-[10px] {{ $day['is_selected'] ? 'text-orange-600' : 'text-gray-400' }}">{{ $day['date_display'] }}</div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-4 p-3">
                <form method="GET" class="space-y-2">
                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                    <div class="flex gap-2">
                        <select name="contract_type" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
                            <option value="">Semua Kontrak</option>
                            <option value="dedicated" {{ request('contract_type') == 'dedicated' ? 'selected' : '' }}>Dedicated</option>
                            <option value="mitra" {{ request('contract_type') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                        </select>
                        <select name="vehicle_type" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
                            <option value="">Semua Kendaraan</option>
                            <option value="2wh" {{ request('vehicle_type') == '2wh' ? 'selected' : '' }}>2 Wheels</option>
                            <option value="4wh" {{ request('vehicle_type') == '4wh' ? 'selected' : '' }}>4 Wheels</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIP atau Nama..." class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
                        <button type="submit" class="px-4 py-2 bg-orange-500 text-white text-xs font-medium rounded-lg hover:bg-orange-600 transition shrink-0">Filter</button>
                    </div>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-3">
                    <p class="text-[10px] text-gray-400 uppercase">Target</p>
                    <p class="text-lg font-bold text-blue-600">{{ number_format($weeklySummary['total_target']) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-3">
                    <p class="text-[10px] text-gray-400 uppercase">Pencapaian</p>
                    <p class="text-lg font-bold text-green-600">{{ number_format($weeklySummary['total_achievement']) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-3">
                    <p class="text-[10px] text-gray-400 uppercase">Progress</p>
                    <p class="text-lg font-bold {{ $weeklySummary['progress'] >= 100 ? 'text-green-600' : ($weeklySummary['progress'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">{{ $weeklySummary['progress'] }}%</p>
                    <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5">
                        <div class="{{ $weeklySummary['progress'] >= 100 ? 'bg-green-600' : ($weeklySummary['progress'] >= 50 ? 'bg-yellow-600' : 'bg-red-600') }} h-1.5 rounded-full" style="width: {{ min($weeklySummary['progress'], 100) }}%"></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-3">
                    <p class="text-[10px] text-gray-400 uppercase">Whitelist</p>
                    <p class="text-lg font-bold text-purple-600">{{ $weeklySummary['total_whitelisted'] }}</p>
                </div>
            </div>

            {{-- Input Achievement --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
                <h3 class="text-sm font-semibold mb-3 text-gray-700">Input Pencapaian</h3>

                {{-- Manpower Picker --}}
                <div class="flex gap-2 mb-3">
                    <select id="manpowerPicker" onchange="loadManpowerInfo()" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
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
                    <button type="button" onclick="document.getElementById('manpowerPicker').value = ''; hideManpowerInfo();" class="px-3 py-2 bg-gray-200 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-300 transition shrink-0">Reset</button>
                </div>

                {{-- Manpower Info --}}
                <div id="manpowerInfoSection" class="hidden">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-3">
                        <div class="grid grid-cols-3 gap-2 mb-2">
                            <div><p class="text-[10px] text-gray-400">NIP</p><p id="infoNip" class="font-mono text-xs font-semibold"></p></div>
                            <div><p class="text-[10px] text-gray-400">Nama</p><p id="infoName" class="text-xs font-semibold truncate"></p></div>
                            <div><p class="text-[10px] text-gray-400">Contract</p><p id="infoContract" class="text-xs font-semibold"></p></div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 mb-2">
                            <div><p class="text-[10px] text-gray-400">Week/Day</p><p id="infoWeekDay" class="text-xs font-semibold"></p></div>
                            <div><p class="text-[10px] text-gray-400">Daily Target</p><p id="infoDailyTarget" class="text-xs font-bold text-blue-600"></p></div>
                            <div><p class="text-[10px] text-gray-400">Carryover</p><p id="infoCarryover" class="text-xs font-semibold"></p></div>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600 grid grid-cols-3 gap-2">
                            <div><p class="text-[10px] text-gray-400">Effective Target</p><p id="infoEffectiveTarget" class="text-sm font-bold text-blue-600"></p></div>
                            <div><p class="text-[10px] text-gray-400">Status</p><div id="infoStatus"></div></div>
                            <div><p class="text-[10px] text-gray-400">Progress</p><div id="infoProgress"></div></div>
                        </div>
                    </div>

                    {{-- Input Form --}}
                    <form action="{{ route('achievements.store') }}" method="POST" id="achievementForm">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                        <input type="hidden" name="manpower_id" id="inputManpowerId">
                        <div class="flex gap-2 items-end">
                            <div class="flex-1">
                                <label class="block text-[10px] text-gray-400 mb-0.5">Achievement</label>
                                <input type="number" name="achievement" id="inputAchievement" min="0" value="0" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm text-center font-bold">
                            </div>
                            <div class="flex-1">
                                <label class="block text-[10px] text-gray-400 mb-0.5">Notes</label>
                                <input type="text" name="notes" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
                            </div>
                            <button type="submit" id="btnSave" class="px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition shrink-0">Simpan</button>
                            <button type="button" onclick="loadManpowerInfo()" class="px-4 py-2 bg-gray-500 text-white text-xs font-medium rounded-lg hover:bg-gray-600 transition shrink-0">Refresh</button>
                        </div>
                    </form>
                </div>

                {{-- Whitelist Notice --}}
                <div id="whitelistNotice" class="hidden bg-purple-50 dark:bg-purple-900 border border-purple-200 dark:border-purple-700 rounded-lg p-3 text-center">
                    <p class="text-purple-700 dark:text-purple-300 text-xs font-semibold">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Manpower di-whitelist hari ini. Target = 0.
                    </p>
                </div>
            </div>

            {{-- Daily Breakdown --}}
            <div id="dailyBreakdownSection" class="hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden mb-4">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-sm font-semibold truncate"><span id="breakdownName"></span></h3>
                    <button onclick="document.getElementById('dailyBreakdownSection').classList.add('hidden')" class="w-6 h-6 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 transition shrink-0">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div><p class="text-[10px] text-gray-400">Daily Target</p><p id="bdDailyTarget" class="text-sm font-bold text-blue-600"></p></div>
                        <div><p class="text-[10px] text-gray-400">Weekly Target</p><p id="bdWeeklyTarget" class="text-sm font-bold text-blue-600"></p></div>
                        <div><p class="text-[10px] text-gray-400">Total Achievement</p><p id="bdTotalAchievement" class="text-sm font-bold text-green-600"></p></div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div><p class="text-[10px] text-gray-400">Hari Aktif</p><p id="bdDaysActive" class="text-sm font-bold"></p></div>
                        <div><p class="text-[10px] text-gray-400">Rata-rata</p><p id="bdAvgAchievement" class="text-sm font-bold"></p></div>
                        <div><p class="text-[10px] text-gray-400">Verdict</p><p id="bdVerdict" class="text-sm font-bold"></p></div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="text-gray-500 border-b">
                                <tr>
                                    <th class="py-1.5 text-center">Hari</th>
                                    <th class="py-1.5 text-center">Target</th>
                                    <th class="py-1.5 text-center">Carry</th>
                                    <th class="py-1.5 text-center">Eff</th>
                                    <th class="py-1.5 text-center">Achieve</th>
                                    <th class="py-1.5 text-center">%</th>
                                    <th class="py-1.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="breakdownTableBody" class="divide-y divide-gray-50"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Manpower Status Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold">Status Manpower</h3>
                </div>

                {{-- Mobile cards --}}
                <div class="lg:hidden divide-y divide-gray-50">
                    @forelse ($manpower as $person)
                        @php
                            $existingAchievement = $person->achievements->first();
                            $isWhitelisted = $person->whitelists->isNotEmpty();
                            $dailyTarget = $person->getActiveDailyTarget($date);
                            $carryover = $person->getExpectedCarryover($date);
                            $effectiveTarget = $isWhitelisted ? 0 : ($dailyTarget + $carryover);
                            $achievementValue = $existingAchievement->achievement ?? 0;
                            $percentage = $effectiveTarget > 0 ? ($achievementValue / $effectiveTarget) * 100 : ($isWhitelisted ? 100 : 0);
                            $status = $isWhitelisted ? 'whitelisted' : ($percentage >= 100 ? 'achieved' : ($percentage >= 50 ? 'partial' : 'low'));
                        @endphp
                        <div class="p-3 hover:bg-gray-50 cursor-pointer" onclick="selectManpower({{ $person->id }})">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="font-mono text-xs text-gray-500 shrink-0">{{ $person->nip }}</span>
                                    <span class="text-xs font-semibold truncate">{{ $person->full_name }}</span>
                                </div>
                                <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full {{ $status == 'achieved' ? 'bg-green-100 text-green-700' : ($status == 'partial' ? 'bg-yellow-100 text-yellow-700' : ($status == 'whitelisted' ? 'bg-purple-100 text-purple-700' : 'bg-red-100 text-red-700')) }}">
                                    {{ $isWhitelisted ? 'WL' : number_format($percentage, 0) . '%' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-[10px] text-gray-400">
                                <span>W{{ $person->getWeekNumber($date) }}/D{{ $person->getDayInWeek($date) }}</span>
                                <span>Tgt: <span class="font-bold text-gray-600">{{ $effectiveTarget }}</span></span>
                                <span>Ach: <span class="font-bold {{ $achievementValue > 0 ? 'text-green-600' : 'text-gray-400' }}">{{ $achievementValue }}</span></span>
                                @if ($carryover != 0)
                                    <span class="{{ $carryover > 0 ? 'text-red-500' : 'text-green-500' }}">Carry: {{ $carryover > 0 ? '+' : '' }}{{ $carryover }}</span>
                                @endif
                            </div>
                            @if (!$isWhitelisted && $effectiveTarget > 0)
                                <div class="mt-1.5 w-full bg-gray-200 rounded-full h-1">
                                    <div class="{{ $percentage >= 100 ? 'bg-green-500' : ($percentage >= 50 ? 'bg-yellow-500' : 'bg-red-500') }} h-1 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-gray-400">No manpower found.</div>
                    @endforelse
                </div>

                {{-- Desktop table --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr class="text-[11px] text-gray-500">
                                <th class="px-3 py-2 text-left">NIP</th>
                                <th class="px-3 py-2 text-left">Name</th>
                                <th class="px-3 py-2 text-left">Contract</th>
                                <th class="px-3 py-2 text-center">W/D</th>
                                <th class="px-3 py-2 text-center">Target</th>
                                <th class="px-3 py-2 text-center">Carry</th>
                                <th class="px-3 py-2 text-center">Eff Target</th>
                                <th class="px-3 py-2 text-center">Achieve</th>
                                <th class="px-3 py-2 text-center">Status</th>
                                <th class="px-3 py-2 text-center">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($manpower as $person)
                                @php
                                    $existingAchievement = $person->achievements->first();
                                    $isWhitelisted = $person->whitelists->isNotEmpty();
                                    $dailyTarget = $person->getActiveDailyTarget($date);
                                    $carryover = $person->getExpectedCarryover($date);
                                    $effectiveTarget = $isWhitelisted ? 0 : ($dailyTarget + $carryover);
                                    $achievementValue = $existingAchievement->achievement ?? 0;
                                    $percentage = $effectiveTarget > 0 ? ($achievementValue / $effectiveTarget) * 100 : ($isWhitelisted ? 100 : 0);
                                    $status = $isWhitelisted ? 'whitelisted' : ($percentage >= 100 ? 'achieved' : ($percentage >= 50 ? 'partial' : 'low'));
                                @endphp
                                <tr class="{{ $isWhitelisted ? 'bg-purple-50 dark:bg-purple-900' : '' }} hover:bg-gray-50 cursor-pointer" onclick="selectManpower({{ $person->id }})">
                                    <td class="px-3 py-2 font-mono text-xs">{{ $person->nip }}</td>
                                    <td class="px-3 py-2 text-xs">{{ $person->full_name }}</td>
                                    <td class="px-3 py-2">
                                        <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full {{ $person->contract_type == 'dedicated' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($person->contract_type) }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-center">{{ $person->getWeekNumber($date) }}/{{ $person->getDayInWeek($date) }}</td>
                                    <td class="px-3 py-2 text-xs text-center font-semibold">{{ $dailyTarget }}</td>
                                    <td class="px-3 py-2 text-xs text-center font-semibold {{ $carryover > 0 ? 'text-red-600' : ($carryover < 0 ? 'text-green-600' : 'text-gray-500') }}">{{ $carryover > 0 ? '+' : '' }}{{ $carryover }}</td>
                                    <td class="px-3 py-2 text-xs text-center font-semibold {{ $isWhitelisted ? 'text-purple-600' : ($carryover < 0 ? 'text-green-600' : 'text-blue-600') }}">
                                        @if ($isWhitelisted)
                                            <span class="inline-flex items-center"><svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>WL</span>
                                        @else
                                            {{ $effectiveTarget }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs text-center font-bold {{ $achievementValue > 0 ? 'text-green-600' : 'text-gray-400' }}">{{ $achievementValue }}</td>
                                    <td class="px-3 py-2 text-center">
                                        @if ($isWhitelisted)
                                            <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-purple-100 text-purple-700">WL</span>
                                        @else
                                            <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full {{ $status == 'achieved' ? 'bg-green-100 text-green-700' : ($status == 'partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ number_format($percentage, 0) }}%</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @if (!$isWhitelisted && $effectiveTarget > 0)
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="{{ $percentage >= 100 ? 'bg-green-500' : ($percentage >= 50 ? 'bg-yellow-500' : 'bg-red-500') }} h-1.5 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-4 py-6 text-center text-xs text-gray-400">No manpower found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $manpower->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Import Modal --}}
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
            window.location.href = '{{ route("achievements.index") }}?date=' + year + '-01-01';
        }

        function selectManpower(id) {
            document.getElementById('manpowerPicker').value = id;
            loadManpowerInfo();
        }

        function loadManpowerInfo() {
            const manpowerId = document.getElementById('manpowerPicker').value;
            if (!manpowerId) { hideManpowerInfo(); return; }

            fetch(`${apiUrl}?manpower_id=${manpowerId}&date=${dateValue}`)
                .then(r => r.json())
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
                    document.getElementById('infoWeekDay').textContent = `W${data.week_number}/D${data.day_in_week}`;
                    document.getElementById('infoDailyTarget').textContent = data.daily_target;
                    document.getElementById('infoCarryover').textContent = data.carryover;
                    document.getElementById('infoEffectiveTarget').textContent = data.effective_target;
                    document.getElementById('inputAchievement').value = data.existing_achievement;

                    const pct = data.effective_target > 0 ? (data.existing_achievement / data.effective_target) * 100 : 0;
                    const sc = pct >= 100 ? 'bg-green-100 text-green-800' : (pct >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                    const st = pct >= 100 ? 'Achieved' : (pct >= 50 ? 'Partial' : 'Low');
                    document.getElementById('infoStatus').innerHTML = `<span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full ${sc}">${st} ${Math.round(pct)}%</span>`;
                    const pc = pct >= 100 ? 'bg-green-600' : (pct >= 50 ? 'bg-yellow-600' : 'bg-red-600');
                    document.getElementById('infoProgress').innerHTML = `<div class="w-full bg-gray-200 rounded-full h-1.5 mt-1"><div class="${pc} h-1.5 rounded-full" style="width: ${Math.min(pct, 100)}%"></div></div>`;

                    loadDailyBreakdown(manpowerId);
                })
                .catch(e => { console.error(e); alert('Gagal memuat data'); });
        }

        function loadDailyBreakdown(manpowerId) {
            fetch(`${breakdownApiUrl}?manpower_id=${manpowerId}&date=${dateValue}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('dailyBreakdownSection').classList.remove('hidden');
                    document.getElementById('breakdownName').textContent = `${data.nip} - ${data.full_name}`;
                    document.getElementById('bdDailyTarget').textContent = data.daily_target;
                    document.getElementById('bdWeeklyTarget').textContent = data.weekly_target;
                    document.getElementById('bdTotalAchievement').textContent = data.total_achievement;
                    document.getElementById('bdDaysActive').textContent = `${data.days_with_achievement}/${data.days.length}`;
                    document.getElementById('bdAvgAchievement').textContent = data.avg_achievement;
                    document.getElementById('bdVerdict').innerHTML = data.is_above_target
                        ? '<span class="text-green-600">✓ Above</span>'
                        : '<span class="text-red-600">✗ Below</span>';

                    document.getElementById('breakdownTableBody').innerHTML = data.days.map(day => {
                        const bg = day.is_whitelisted ? 'bg-purple-50' : (day.is_today ? 'bg-blue-50' : '');
                        const pc = day.percentage >= 100 ? 'text-green-600' : (day.percentage >= 50 ? 'text-yellow-600' : 'text-red-600');
                        const sl = day.is_whitelisted ? '<span class="text-purple-600">WL</span>' : (day.percentage >= 100 ? '<span class="text-green-600">✓</span>' : (day.percentage > 0 ? `<span class="${pc}">${day.percentage}%</span>` : '-'));
                        return `<tr class="${bg}"><td class="py-1.5 text-center">${day.day_name}</td><td class="py-1.5 text-center">${day.is_whitelisted ? 0 : day.daily_target}</td><td class="py-1.5 text-center ${day.carryover > 0 ? 'text-red-600' : (day.carryover < 0 ? 'text-green-600' : '')}">${day.carryover}</td><td class="py-1.5 text-center font-semibold">${day.effective_target}</td><td class="py-1.5 text-center font-bold">${day.achievement}</td><td class="py-1.5 text-center ${pc}">${day.percentage}%</td><td class="py-1.5 text-center">${sl}</td></tr>`;
                    }).join('');
                })
                .catch(e => console.error(e));
        }

        function hideManpowerInfo() {
            document.getElementById('manpowerInfoSection').classList.add('hidden');
            document.getElementById('whitelistNotice').classList.add('hidden');
            document.getElementById('dailyBreakdownSection').classList.add('hidden');
            document.getElementById('inputManpowerId').value = '';
        }

        // Auto-scroll day slider to selected day
        document.addEventListener('DOMContentLoaded', () => {
            const slider = document.getElementById('daySlider');
            if (slider) {
                const selected = slider.querySelector('[class*="border-orange-500"]');
                if (selected) selected.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
            }
        });
    </script>
</x-app-layout>
