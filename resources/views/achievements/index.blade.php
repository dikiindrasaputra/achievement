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
                    <div class="flex items-center justify-between mb-3">
                        <a href="{{ route('achievements.index', array_merge(request()->query(), ['date' => $prevWeekDate])) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 transition">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <span class="text-xs font-semibold text-gray-600">{{ \Carbon\Carbon::parse($weekDays[0]['date'])->format('d M') }} - {{ \Carbon\Carbon::parse($weekDays[6]['date'])->format('d M Y') }}</span>
                        <a href="{{ route('achievements.index', array_merge(request()->query(), ['date' => $nextWeekDate])) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 transition">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

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

            {{-- ============ DESKTOP: Two Column Layout ============ --}}
            <div class="hidden lg:grid lg:grid-cols-5 lg:gap-4">

                {{-- Left: Status Manpower --}}
                <div class="col-span-3 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-semibold">Status Manpower</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr class="text-[11px] text-gray-500">
                                    <th class="px-3 py-2 text-left">NIP</th>
                                    <th class="px-3 py-2 text-left">Name</th>
                                    <th class="px-3 py-2 text-center">W/D</th>
                                    <th class="px-3 py-2 text-center">Target</th>
                                    <th class="px-3 py-2 text-center">Carry</th>
                                    <th class="px-3 py-2 text-center">Eff</th>
                                    <th class="px-3 py-2 text-center">Ach</th>
                                    <th class="px-3 py-2 text-center">%</th>
                                    <th class="px-3 py-2 text-center">Status</th>
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
                                    <tr class="manpower-row {{ $isWhitelisted ? 'bg-purple-50 dark:bg-purple-900' : '' }} hover:bg-orange-50 cursor-pointer transition-colors"
                                        onclick="selectManpower({{ $person->id }})"
                                        data-manpower-id="{{ $person->id }}">
                                        <td class="px-3 py-2 font-mono text-xs">{{ $person->nip }}</td>
                                        <td class="px-3 py-2 text-xs font-medium">{{ $person->full_name }}</td>
                                        <td class="px-3 py-2 text-xs text-center">{{ $person->getWeekNumber($date) }}/{{ $person->getDayInWeek($date) }}</td>
                                        <td class="px-3 py-2 text-xs text-center font-semibold">{{ $dailyTarget }}</td>
                                        <td class="px-3 py-2 text-xs text-center font-semibold {{ $carryover > 0 ? 'text-red-600' : ($carryover < 0 ? 'text-green-600' : 'text-gray-500') }}">{{ $carryover > 0 ? '+' : '' }}{{ $carryover }}</td>
                                        <td class="px-3 py-2 text-xs text-center font-semibold text-blue-600">
                                            @if ($isWhitelisted) WL @else {{ $effectiveTarget }} @endif
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
                                    <tr><td colspan="9" class="px-4 py-6 text-center text-xs text-gray-400">No manpower found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                        {{ $manpower->links() }}
                    </div>
                </div>

                {{-- Right: All-Day Input Panel --}}
                <div class="col-span-2">
                    {{-- Placeholder --}}
                    <div id="rightPanelDefault" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-400">Pilih Manpower</p>
                            <p class="text-xs text-gray-300 mt-1">Klik baris di tabel untuk input pencapaian</p>
                            <div class="mt-6 space-y-3 text-left">
                                @foreach([3,2,4] as $w)
                                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                        <div class="w-8 h-8 rounded-lg bg-gray-200 shrink-0"></div>
                                        <div class="flex-1 space-y-1.5">
                                            <div class="h-2.5 bg-gray-200 rounded" style="width: {{ $w * 20 }}%"></div>
                                            <div class="h-2 bg-gray-100 rounded" style="width: {{ $w * 15 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Active Panel --}}
                    <div id="rightPanelActive" class="hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">

                        {{-- Whitelist Notice --}}
                        <div id="whitelistNotice" class="hidden p-4 bg-purple-50 border-b border-purple-200 text-center">
                            <p class="text-purple-700 text-xs font-semibold">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Manpower di-whitelist hari ini. Target = 0.
                            </p>
                        </div>

                        {{-- Manpower Info --}}
                        <div id="manpowerInfoSection" class="p-4 border-b border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
                                        <span id="infoAvatar" class="text-sm font-bold text-orange-600"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <p id="infoName" class="text-sm font-bold text-gray-800 truncate"></p>
                                        <p id="infoNip" class="text-[10px] font-mono text-gray-400"></p>
                                    </div>
                                </div>
                                <span id="infoContractBadge" class="px-2 py-0.5 text-[10px] font-semibold rounded-full shrink-0"></span>
                            </div>
                            {{-- Weekly summary --}}
                            <div class="grid grid-cols-4 gap-2 mt-2">
                                <div class="bg-blue-50 rounded-lg p-1.5 text-center"><p class="text-[9px] text-gray-400">Daily Tgt</p><p id="bdDailyTarget" class="text-xs font-bold text-blue-600"></p></div>
                                <div class="bg-blue-50 rounded-lg p-1.5 text-center"><p class="text-[9px] text-gray-400">Weekly Tgt</p><p id="bdWeeklyTarget" class="text-xs font-bold text-blue-600"></p></div>
                                <div class="bg-green-50 rounded-lg p-1.5 text-center"><p class="text-[9px] text-gray-400">Total Ach</p><p id="bdTotalAchievement" class="text-xs font-bold text-green-600"></p></div>
                                <div class="bg-gray-50 rounded-lg p-1.5 text-center"><p class="text-[9px] text-gray-400">Verdict</p><p id="bdVerdict" class="text-xs font-bold"></p></div>
                            </div>
                        </div>

                        {{-- All Days Input Table --}}
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-600">Input Semua Hari</span>
                                <span class="text-[10px] text-gray-400">Klik ✓ untuk simpan per hari</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-[11px]">
                                    <thead class="text-gray-400 border-b">
                                        <tr>
                                            <th class="py-1.5 text-center w-12">Hari</th>
                                            <th class="py-1.5 text-center w-10">Tgt</th>
                                            <th class="py-1.5 text-center w-10">Carry</th>
                                            <th class="py-1.5 text-center w-10">Eff</th>
                                            <th class="py-1.5 text-center">Ach</th>
                                            <th class="py-1.5 text-center w-12">%</th>
                                            <th class="py-1.5 text-center w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="allDaysTableBody" class="divide-y divide-gray-50"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ MOBILE: Stacked Layout ============ --}}
            <div class="lg:hidden space-y-4">

                {{-- Input Achievement --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-sm font-semibold mb-3 text-gray-700">Input Pencapaian</h3>

                    <div class="flex gap-2 mb-3">
                        <select id="manpowerPickerMobile" onchange="selectManpower(this.value)" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
                            <option value="">-- Pilih Manpower --</option>
                            @foreach ($manpower as $person)
                                <option value="{{ $person->id }}">{{ $person->nip }} - {{ $person->full_name }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="document.getElementById('manpowerPickerMobile').value = ''; hideManpowerInfo();" class="px-3 py-2 bg-gray-200 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-300 transition shrink-0">Reset</button>
                    </div>

                    <div id="manpowerInfoSectionMobile" class="hidden">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-3">
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <div><p class="text-[10px] text-gray-400">NIP</p><p id="infoNipMobile" class="font-mono text-xs font-semibold"></p></div>
                                <div><p class="text-[10px] text-gray-400">Nama</p><p id="infoNameMobile" class="text-xs font-semibold truncate"></p></div>
                                <div><p class="text-[10px] text-gray-400">Contract</p><p id="infoContractMobile" class="text-xs font-semibold"></p></div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <div><p class="text-[10px] text-gray-400">Week/Day</p><p id="infoWeekDayMobile" class="text-xs font-semibold"></p></div>
                                <div><p class="text-[10px] text-gray-400">Daily Target</p><p id="infoDailyTargetMobile" class="text-xs font-bold text-blue-600"></p></div>
                                <div><p class="text-[10px] text-gray-400">Carryover</p><p id="infoCarryoverMobile" class="text-xs font-semibold"></p></div>
                            </div>
                            <div class="pt-2 border-t border-gray-200 dark:border-gray-600 grid grid-cols-3 gap-2">
                                <div><p class="text-[10px] text-gray-400">Eff Target</p><p id="infoEffectiveTargetMobile" class="text-sm font-bold text-blue-600"></p></div>
                                <div><p class="text-[10px] text-gray-400">Status</p><div id="infoStatusMobile"></div></div>
                                <div><p class="text-[10px] text-gray-400">Progress</p><div id="infoProgressMobile"></div></div>
                            </div>
                        </div>

                        <form action="{{ route('achievements.store') }}" method="POST" id="achievementFormMobile">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                            <input type="hidden" name="manpower_id" id="inputManpowerIdMobile">
                            <div class="flex gap-2 items-end">
                                <div class="flex-1">
                                    <label class="block text-[10px] text-gray-400 mb-0.5">Achievement</label>
                                    <input type="number" name="achievement" id="inputAchievementMobile" min="0" value="0" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm text-center font-bold">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-[10px] text-gray-400 mb-0.5">Notes</label>
                                    <input type="text" name="notes" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-xs">
                                </div>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition shrink-0">Simpan</button>
                            </div>
                        </form>
                    </div>

                    <div id="whitelistNoticeMobile" class="hidden bg-purple-50 border border-purple-200 rounded-lg p-3 text-center">
                        <p class="text-purple-700 text-xs font-semibold">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Manpower di-whitelist. Target = 0.
                        </p>
                    </div>
                </div>

                {{-- Daily Breakdown --}}
                <div id="dailyBreakdownSectionMobile" class="hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-semibold text-gray-600">Detail Mingguan: <span id="breakdownNameMobile"></span></span>
                        <button onclick="document.getElementById('dailyBreakdownSectionMobile').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mb-2">
                        <div><p class="text-[10px] text-gray-400">Daily Tgt</p><p id="bdDailyTargetMobile" class="text-xs font-bold text-blue-600"></p></div>
                        <div><p class="text-[10px] text-gray-400">Weekly Tgt</p><p id="bdWeeklyTargetMobile" class="text-xs font-bold text-blue-600"></p></div>
                        <div><p class="text-[10px] text-gray-400">Total Ach</p><p id="bdTotalAchievementMobile" class="text-xs font-bold text-green-600"></p></div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[11px]">
                            <thead class="text-gray-400 border-b"><tr><th class="py-1 text-center">Hari</th><th class="py-1 text-center">Tgt</th><th class="py-1 text-center">Ach</th><th class="py-1 text-center">%</th><th class="py-1 text-center">Sts</th></tr></thead>
                            <tbody id="breakdownTableBodyMobile" class="divide-y divide-gray-50"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Manpower Status Cards --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-semibold">Status Manpower</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
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
                    <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                        {{ $manpower->links() }}
                    </div>
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

    {{-- Hidden manpower data for JS --}}
    <script type="application/json" id="manpowerData">
        @json($manpower->keyBy('id')->map(fn($p) => ['nip' => $p->nip, 'name' => $p->full_name, 'contract' => $p->contract_type]))
    </script>

    <script>
        const dateValue = '{{ $date->format('Y-m-d') }}';
        const apiUrl = '{{ route("api.achievements.manpower-info") }}';
        const breakdownApiUrl = '{{ route("api.achievements.daily-breakdown") }}';
        const allManpower = JSON.parse(document.getElementById('manpowerData').textContent);

        function changeYear() {
            const year = document.getElementById('yearPicker').value;
            window.location.href = '{{ route("achievements.index") }}?date=' + year + '-01-01';
        }

        function selectManpower(id) {
            if (!id) { hideManpowerInfo(); return; }

            // Highlight row in desktop table
            document.querySelectorAll('.manpower-row').forEach(r => r.classList.remove('bg-orange-50', 'dark:bg-orange-900/20'));
            const row = document.querySelector(`[data-manpower-id="${id}"]`);
            if (row) row.classList.add('bg-orange-50', 'dark:bg-orange-900/20');

            // Set mobile picker
            const mp = document.getElementById('manpowerPickerMobile');
            if (mp) mp.value = id;

            loadManpowerInfo(id);
        }

        function loadManpowerInfo(id) {
            if (!id) id = document.getElementById('manpowerPickerMobile')?.value;
            if (!id) { hideManpowerInfo(); return; }

            fetch(`${apiUrl}?manpower_id=${id}&date=${dateValue}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    const isDesktop = window.innerWidth >= 1024;
                    const suffix = isDesktop ? '' : 'Mobile';

                    if (data.is_whitelisted) {
                        document.getElementById(`manpowerInfoSection${suffix}`).classList.add('hidden');
                        document.getElementById(`whitelistNotice${suffix}`).classList.remove('hidden');
                        document.getElementById(`dailyBreakdownSection${suffix}`)?.classList.add('hidden');
                        if (!isDesktop) document.getElementById('manpowerInfoSectionMobile').classList.add('hidden');
                        if (isDesktop) {
                            document.getElementById('rightPanelDefault').classList.add('hidden');
                            document.getElementById('rightPanelActive').classList.remove('hidden');
                        }
                        return;
                    }

                    document.getElementById(`whitelistNotice${suffix}`).classList.add('hidden');
                    document.getElementById(`manpowerInfoSection${suffix}`).classList.remove('hidden');

                    if (isDesktop) {
                        document.getElementById('rightPanelDefault').classList.add('hidden');
                        document.getElementById('rightPanelActive').classList.remove('hidden');
                        document.getElementById('infoAvatar').textContent = data.full_name.charAt(0);
                        const cb = document.getElementById('infoContractBadge');
                        cb.textContent = data.contract_type === 'dedicated' ? 'Dedicated' : 'Mitra';
                        cb.className = `px-2 py-0.5 text-[10px] font-semibold rounded-full shrink-0 ${data.contract_type === 'dedicated' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}`;
                    }

                    if (document.getElementById(`inputManpowerId${suffix}`)) document.getElementById(`inputManpowerId${suffix}`).value = data.manpower_id;
                    document.getElementById(`infoNip${suffix}`).textContent = data.nip;
                    document.getElementById(`infoName${suffix}`).textContent = data.full_name;
                    if (document.getElementById(`infoContract${suffix}`)) document.getElementById(`infoContract${suffix}`).textContent = data.contract_type === 'dedicated' ? 'Dedicated' : 'Mitra';
                    if (document.getElementById(`infoWeekDay${suffix}`)) document.getElementById(`infoWeekDay${suffix}`).textContent = `W${data.week_number}/D${data.day_in_week}`;
                    if (document.getElementById(`infoDailyTarget${suffix}`)) document.getElementById(`infoDailyTarget${suffix}`).textContent = data.daily_target;
                    if (document.getElementById(`infoCarryover${suffix}`)) document.getElementById(`infoCarryover${suffix}`).textContent = data.carryover;
                    if (document.getElementById(`infoEffectiveTarget${suffix}`)) document.getElementById(`infoEffectiveTarget${suffix}`).textContent = data.effective_target;
                    if (document.getElementById(`inputAchievement${suffix}`)) document.getElementById(`inputAchievement${suffix}`).value = data.existing_achievement;

                    if (document.getElementById(`infoStatus${suffix}`)) {
                        const pct = data.effective_target > 0 ? (data.existing_achievement / data.effective_target) * 100 : 0;
                        const sc = pct >= 100 ? 'bg-green-100 text-green-800' : (pct >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                        const st = pct >= 100 ? 'Achieved' : (pct >= 50 ? 'Partial' : 'Low');
                        document.getElementById(`infoStatus${suffix}`).innerHTML = `<span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full ${sc}">${st} ${Math.round(pct)}%</span>`;
                        const pc = pct >= 100 ? 'bg-green-600' : (pct >= 50 ? 'bg-yellow-600' : 'bg-red-600');
                        document.getElementById(`infoProgress${suffix}`).innerHTML = `<div class="w-full bg-gray-200 rounded-full h-1.5 mt-1"><div class="${pc} h-1.5 rounded-full" style="width: ${Math.min(pct, 100)}%"></div></div>`;
                    }

                    loadDailyBreakdown(id, suffix);
                })
                .catch(e => { console.error('manpower-info error:', e); alert('Gagal memuat data: ' + e.message); });
        }

        function loadDailyBreakdown(id, suffix = '') {
            fetch(`${breakdownApiUrl}?manpower_id=${id}&date=${dateValue}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    if (document.getElementById(`bdDailyTarget${suffix}`)) {
                        document.getElementById(`bdDailyTarget${suffix}`).textContent = data.daily_target;
                        document.getElementById(`bdWeeklyTarget${suffix}`).textContent = data.weekly_target;
                        document.getElementById(`bdTotalAchievement${suffix}`).textContent = data.total_achievement;
                        document.getElementById(`bdVerdict${suffix}`).innerHTML = data.is_above_target
                            ? '<span class="text-green-600">✓ Above</span>'
                            : '<span class="text-red-600">✗ Below</span>';
                    }

                    if (suffix === 'Mobile') return;

                    // Desktop: render all days with editable inputs
                    const tbody = document.getElementById('allDaysTableBody');
                    tbody.innerHTML = data.days.map(day => {
                        const isSelected = day.is_today;
                        const rowBg = isSelected ? 'bg-orange-50 dark:bg-orange-900/10' : '';
                        const carryClass = day.carryover > 0 ? 'text-red-500' : (day.carryover < 0 ? 'text-green-500' : 'text-gray-400');
                        const carryPrefix = day.carryover > 0 ? '+' : '';
                        const noData = day.achievement === 0 && !day.is_whitelisted;
                        const achClass = day.achievement > 0 ? 'text-green-600 font-bold' : 'text-gray-300';

                        if (day.is_whitelisted) {
                            return `<tr class="${rowBg}"><td class="py-1.5 text-center font-medium">${day.day_name}</td><td class="py-1.5 text-center text-gray-400">0</td><td class="py-1.5 text-center text-gray-400">0</td><td class="py-1.5 text-center text-gray-400">0</td><td class="py-1.5 text-center"><span class="text-purple-500 text-[10px]">WL</span></td><td class="py-1.5 text-center"><span class="text-purple-500">✓</span></td><td class="py-1.5 text-center">-</td></tr>`;
                        }

                        return `<tr class="${rowBg}" data-day-date="${day.date}">
                            <td class="py-1.5 text-center font-medium ${isSelected ? 'text-orange-700' : ''}">${day.day_name}${isSelected ? ' ●' : ''}</td>
                            <td class="py-1.5 text-center text-gray-600">${day.daily_target}</td>
                            <td class="py-1.5 text-center ${carryClass}">${carryPrefix}${day.carryover}</td>
                            <td class="py-1.5 text-center font-semibold text-blue-600">${day.effective_target}</td>
                            <td class="py-1.5 text-center">
                                <div class="flex flex-col items-center">
                                    <input type="number" min="0" value="${day.achievement}"
                                        class="day-ach-input w-14 text-center text-xs font-bold rounded border ${isSelected ? 'border-orange-300 focus:ring-orange-500 focus:border-orange-500' : 'border-gray-200 focus:ring-orange-500 focus:border-orange-500'} py-0.5"
                                        data-date="${day.date}" data-manpower-id="${data.manpower_id}"
                                        onchange="autoSaveDay(this, event)" onkeydown="autoSaveDay(this, event)">
                                    ${day.achievement === 0 && !day.is_whitelisted ? `<span class="text-[9px] text-orange-400 mt-0.5">rekom: ${day.effective_target}</span>` : ''}
                                </div>
                            </td>
                            <td class="py-1.5 text-center">
                                <span class="day-pct ${day.percentage >= 100 ? 'text-green-600' : (day.percentage >= 50 ? 'text-yellow-600' : 'text-red-600')}">${day.percentage}%</span>
                            </td>
                            <td class="py-1.5 text-center">
                                <button type="button" onclick="saveDayAchievement(this)" class="save-day-btn text-green-500 hover:text-green-700 transition" title="Simpan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </td>
                        </tr>`;
                    }).join('');
                })
                .catch(e => console.error(e));
        }

        function autoSaveDay(input, e) {
            if (e && e.key === 'Enter') {
                saveDayAchievement(input);
            }
        }

        function saveDayAchievement(el) {
            const row = el.closest('tr');
            const input = row.querySelector('.day-ach-input');
            const date = input.dataset.date;
            const manpowerId = input.dataset.manpowerId;
            const achievement = parseInt(input.value) || 0;

            const btn = row.querySelector('.save-day-btn');
            btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';

            fetch('{{ route("achievements.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ manpower_id: manpowerId, date: date, achievement: achievement })
            })
            .then(r => r.json())
            .then(res => {
                btn.innerHTML = '<svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                loadDailyBreakdown(manpowerId);
            })
            .catch(e => {
                console.error(e);
                btn.innerHTML = '<svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                setTimeout(() => {
                    btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                }, 2000);
            });
        }

        function hideManpowerInfo() {
            ['manpowerInfoSection', 'whitelistNotice', 'dailyBreakdownSection'].forEach(id => {
                document.getElementById(id)?.classList.add('hidden');
                document.getElementById(id + 'Mobile')?.classList.add('hidden');
            });
            document.getElementById('inputManpowerId')?.setAttribute('value', '');
            document.getElementById('inputManpowerIdMobile')?.setAttribute('value', '');
            // Show default panel on desktop
            document.getElementById('rightPanelDefault')?.classList.remove('hidden');
            document.getElementById('rightPanelActive')?.classList.add('hidden');
            // Remove row highlight
            document.querySelectorAll('.manpower-row').forEach(r => r.classList.remove('bg-orange-50', 'dark:bg-orange-900/20'));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const slider = document.getElementById('daySlider');
            if (slider) {
                const selected = slider.querySelector('[class*="border-orange-500"]');
                if (selected) selected.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
            }
        });
    </script>
</x-app-layout>
