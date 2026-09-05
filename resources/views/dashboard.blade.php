<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Productivity by Delivered -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                    <h3 class="text-lg font-semibold">Productivity by Delivered</h3>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2 flex-1 sm:flex-none">
                            <select name="contract_type" onchange="this.form.submit()" class="flex-1 sm:flex-none px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">All Contract</option>
                                <option value="dedicated" {{ ($contractType ?? '') === 'dedicated' ? 'selected' : '' }}>Dedicated</option>
                                <option value="mitra" {{ ($contractType ?? '') === 'mitra' ? 'selected' : '' }}>Mitra</option>
                            </select>
                            <select name="vehicle_type" onchange="this.form.submit()" class="flex-1 sm:flex-none px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">All Vehicle</option>
                                <option value="2wh" {{ ($vehicleType ?? '') === '2wh' ? 'selected' : '' }}>2 Wheels</option>
                                <option value="4wh" {{ ($vehicleType ?? '') === '4wh' ? 'selected' : '' }}>4 Wheels</option>
                            </select>
                        </form>
                        <button onclick="exportToExcel()" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition flex items-center gap-2 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="hidden sm:inline">Export Excel</span>
                            <span class="sm:hidden">Export</span>
                        </button>
                    </div>
                </div>

                {{-- Desktop: Table view --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm hidden lg:table">
                        <thead>
                            <tr class="text-xs text-gray-500 border-b">
                                <th class="text-left py-2 px-2">Rider Name</th>
                                <th class="text-center py-2 px-2">Target Daily</th>
                                <th class="text-center py-2 px-2">Target Weekly</th>
                                @foreach ($weekDays as $day)
                                    <th class="text-center py-2 px-2 {{ $day['is_today'] ? 'bg-orange-50 font-bold' : '' }}">
                                        {{ $day['day_name'] }}<br><span class="text-[10px]">{{ $day['date_display'] }}</span>
                                    </th>
                                @endforeach
                                <th class="text-center py-2 px-2">Avg/Day</th>
                                <th class="text-center py-2 px-2">Total</th>
                                <th class="text-center py-2 px-2">GAP</th>
                                <th class="text-center py-2 px-2">Status</th>
                                <th class="text-center py-2 px-2">Rank</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($productivity as $person)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2 px-2">
                                        @php
                                            $nameParts = explode(' ', trim($person['name']));
                                            $displayName = count($nameParts) >= 2 ? $nameParts[0].' '.$nameParts[1] : (strlen($person['name']) > 14 ? substr($person['name'], 0, 14).'...' : $person['name']);
                                        @endphp
                                        <div class="font-medium text-sm text-gray-800">{{ $displayName }}</div>
                                        <div class="text-xs text-gray-400">{{ $person['nip'] }}</div>
                                    </td>
                                    <td class="text-center py-2 px-2 font-semibold">{{ $person['daily_target'] }}</td>
                                    <td class="text-center py-2 px-2 font-semibold">{{ $person['weekly_target'] }}</td>
                                    @foreach ($weekDays as $day)
                                        @php
                                            $dayData = $person['days'][$day['day_number']] ?? null;
                                        @endphp
                                        <td class="text-center py-2 px-2 {{ $day['is_today'] ? 'bg-orange-50' : '' }}">
                                            @if ($dayData && $dayData['is_whitelisted'])
                                                <span class="text-purple-400 text-xs">OFF</span>
                                            @elseif ($dayData && $dayData['achievement'] > 0)
                                                <span class="font-semibold text-green-600">{{ $dayData['achievement'] }}</span>
                                                @if ($dayData['carryover'] > 0)
                                                    <div class="text-[9px] text-orange-500">+{{ $dayData['carryover'] }} co</div>
                                                @endif
                                            @elseif ($day['is_past'])
                                                <span class="text-red-400 font-semibold">0</span>
                                                @if (isset($dayData['effective_target']) && $dayData['effective_target'] > $person['daily_target'])
                                                    <div class="text-[9px] text-orange-500">tgt {{ $dayData['effective_target'] }}</div>
                                                @endif
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center py-2 px-2 font-semibold text-blue-600">{{ $person['weekly_avg'] }}</td>
                                    <td class="text-center py-2 px-2 font-bold">{{ $person['total_achievement'] }}</td>
                                    <td class="text-center py-2 px-2 font-semibold {{ $person['gap'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $person['gap'] > 0 ? '-' . $person['gap'] : '+' . abs($person['gap']) }}
                                    </td>
                                    <td class="text-center py-2 px-2">
                                        @if ($person['weekly_avg'] >= $person['daily_target'])
                                            <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-700">Achieved</span>
                                        @else
                                            <span class="text-xs font-medium px-2 py-1 rounded-full bg-red-100 text-red-700">Not Achieved</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-2 px-2">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold text-white"
                                              style="background-color: {{ $person['rank'] <= 3 ? ($person['rank'] == 1 ? '#16a34a' : ($person['rank'] == 2 ? '#22c55e' : '#4ade80')) : '#9ca3af' }};">
                                            {{ $person['rank'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 9 + count($weekDays) }}" class="text-center py-8 text-gray-400">Belum ada data pencapaian</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile: Card view with lazy load --}}
                @php
                    $weekDaysJson = json_encode($weekDays);
                    $productivityJson = json_encode($productivity->values());
                @endphp
                <div class="lg:hidden" x-data="mobileCards()" x-init="init()">
                    <div class="space-y-3">
                        <template x-for="(person, idx) in visibleCards" :key="person.nip">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                {{-- Header: Rank + Name + Status --}}
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold text-white shrink-0"
                                          :style="'background-color:' + (person.rank <= 3 ? (person.rank == 1 ? '#16a34a' : (person.rank == 2 ? '#22c55e' : '#4ade80')) : '#9ca3af')">
                                        <span x-text="person.rank"></span>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-sm text-gray-800 leading-tight" x-text="formatName(person.name)"></div>
                                        <div class="text-[11px] text-gray-400" x-text="person.nip"></div>
                                    </div>
                                    <span class="text-xs font-medium px-2 py-1 rounded-full shrink-0"
                                          :class="person.weekly_avg >= person.daily_target ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                          x-text="person.weekly_avg >= person.daily_target ? 'Achieved' : 'Not Achieved'">
                                    </span>
                                </div>

                                {{-- Stats row --}}
                                <div class="grid grid-cols-4 gap-2 text-center mb-3">
                                    <div>
                                        <div class="text-[10px] text-gray-400">Daily</div>
                                        <div class="text-xs font-bold text-gray-800" x-text="person.daily_target"></div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400">Weekly</div>
                                        <div class="text-xs font-bold text-gray-800" x-text="person.weekly_target"></div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400">Total</div>
                                        <div class="text-xs font-bold text-gray-800" x-text="person.total_achievement"></div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400">GAP</div>
                                        <div class="text-xs font-bold"
                                             :class="person.gap > 0 ? 'text-red-600' : 'text-green-600'"
                                             x-text="(person.gap > 0 ? '-' : '+') + Math.abs(person.gap)"></div>
                                    </div>
                                </div>

                                {{-- Day chips --}}
                                <div class="grid grid-cols-7 gap-1">
                                    <template x-for="day in weekDays" :key="day.day_number">
                                        <div class="text-center rounded-md py-1.5"
                                             :class="day.is_today ? 'border border-orange-300 bg-orange-50' : 'bg-white border border-gray-100'">
                                            <div class="text-[9px] text-gray-400 leading-none" x-text="day.day_name"></div>
                                            <template x-if="person.days[day.day_number] && person.days[day.day_number].is_whitelisted">
                                                <div class="text-[10px] text-purple-400 font-medium leading-tight mt-0.5">OFF</div>
                                            </template>
                                            <template x-if="person.days[day.day_number] && !person.days[day.day_number].is_whitelisted && person.days[day.day_number].achievement > 0">
                                                <div class="text-[11px] text-green-600 font-bold leading-tight mt-0.5" x-text="person.days[day.day_number].achievement"></div>
                                            </template>
                                            <template x-if="!person.days[day.day_number] && day.is_past">
                                                <div class="text-[11px] text-red-400 font-bold leading-tight mt-0.5">0</div>
                                            </template>
                                            <template x-if="!person.days[day.day_number] && !day.is_past">
                                                <div class="text-[11px] text-gray-300 leading-tight mt-0.5">-</div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Loading / sentinel --}}
                    <div x-ref="loadMoreSentinel" x-show="hasMore" class="h-1"></div>
                    <div x-show="loading" class="text-center py-4">
                        <div class="inline-flex items-center gap-2 text-gray-400 text-sm">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memuat...
                        </div>
                    </div>
                    <div x-show="!hasMore && allCards.length > 7 && !loading" class="text-center py-3 text-xs text-gray-400">
                        Semua data ditampilkan
                    </div>
                </div>
            </div>

            <!-- Today's Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Recent Achievements -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Today's Achievements</h3>
                    @php
                        $todayAchievements = \App\Models\Achievement::with('manpower')
                            ->where('date', now()->toDateString())
                            ->orderByDesc('achievement')
                            ->limit(10)
                            ->get();
                    @endphp
                    @forelse ($todayAchievements as $achievement)
                        <div class="flex justify-between items-center py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                            <div>
                                <span class="font-semibold text-sm text-gray-800">{{ $achievement->manpower->full_name }}</span>
                                <span class="ml-2 font-mono text-xs text-gray-400">{{ $achievement->manpower->nip }}</span>
                            </div>
                            <span class="font-semibold {{ $achievement->achievement >= $achievement->manpower->getActiveDailyTarget($achievement->date) ? 'text-green-600' : 'text-red-600' }}">
                                {{ $achievement->achievement }}
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No achievements recorded today.</p>
                    @endforelse
                </div>

                <!-- Manpower Status -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Manpower by Contract</h3>
                    @php
                        $dedicatedCount = \App\Models\Manpower::active()->byContractType('dedicated')->count();
                        $mitraCount = \App\Models\Manpower::active()->byContractType('mitra')->count();
                        $twoWhCount = \App\Models\Manpower::active()->byVehicleType('2wh')->count();
                        $fourWhCount = \App\Models\Manpower::active()->byVehicleType('4wh')->count();
                    @endphp
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Dedicated</span>
                                <span class="font-semibold">{{ $dedicatedCount }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $dedicatedCount + $mitraCount > 0 ? ($dedicatedCount / ($dedicatedCount + $mitraCount)) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Mitra</span>
                                <span class="font-semibold">{{ $mitraCount }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ $dedicatedCount + $mitraCount > 0 ? ($mitraCount / ($dedicatedCount + $mitraCount)) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between text-sm">
                                <span>2 Wheels</span>
                                <span class="font-semibold">{{ $twoWhCount }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm">
                                <span>4 Wheels</span>
                                <span class="font-semibold">{{ $fourWhCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formatName(name) {
            if (!name) return '';
            const words = name.trim().split(/\s+/);
            if (words.length >= 2) {
                return words[0] + ' ' + words[1];
            }
            if (name.length > 14) {
                return name.substring(0, 14) + '...';
            }
            return name;
        }

        function mobileCards() {
            return {
                allCards: [],
                visibleCards: [],
                weekDays: [],
                page: 0,
                perPage: 7,
                hasMore: true,
                loading: false,

                init() {
                    this.allCards = {!! $productivityJson !!};
                    this.weekDays = {!! $weekDaysJson !!};
                    this.loadMore();

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && this.hasMore && !this.loading) {
                                this.loadMore();
                            }
                        });
                    }, { rootMargin: '200px' });

                    this.$nextTick(() => {
                        const sentinel = this.$refs.loadMoreSentinel;
                        if (sentinel) observer.observe(sentinel);
                    });
                },

                loadMore() {
                    this.loading = true;
                    setTimeout(() => {
                        const start = this.page * this.perPage;
                        const end = start + this.perPage;
                        const next = this.allCards.slice(start, end);
                        this.visibleCards = [...this.visibleCards, ...next];
                        this.page++;
                        this.hasMore = end < this.allCards.length;
                        this.loading = false;
                    }, 300);
                },
            };
        }

        function exportToExcel() {
            const table = document.querySelector('table');
            const weekLabel = 'Minggu {{ $weekNumber }} ({{ $weekStart->format("d M") }} - {{ $weekEnd->format("d M Y") }})';

            let html = '<html><head><meta charset="utf-8"></head><body>';
            html += '<table border="1">';

            html += '<tr><td colspan="' + table.querySelectorAll('thead th').length + '" style="font-size:14pt;font-weight:bold;background-color:#EE4D2D;color:white;text-align:center;padding:10px;">Productivity by Delivered - ' + weekLabel + '</td></tr>';
            html += '<tr><td colspan="' + table.querySelectorAll('thead th').length + '"></td></tr>';

            html += '<tr>';
            table.querySelectorAll('thead th').forEach(th => {
                const text = th.innerText.replace(/\n/g, ' ');
                html += '<td style="background-color:#374151;color:white;font-weight:bold;text-align:center;padding:6px;font-size:10pt;">' + text + '</td>';
            });
            html += '</tr>';

            table.querySelectorAll('tbody tr').forEach(tr => {
                if (tr.querySelector('td[colspan]')) return;
                html += '<tr>';
                tr.querySelectorAll('td').forEach((td, idx) => {
                    let text = td.innerText.replace(/\n/g, ' ').trim();
                    let style = 'padding:5px;text-align:center;font-size:10pt;';

                    if (idx === tr.querySelectorAll('td').length - 1) {
                        const rank = parseInt(text);
                        if (rank === 1) style += 'background-color:#16a34a;color:white;font-weight:bold;text-align:center;';
                        else if (rank === 2) style += 'background-color:#22c55e;color:white;font-weight:bold;text-align:center;';
                        else if (rank === 3) style += 'background-color:#4ade80;color:white;font-weight:bold;text-align:center;';
                        else style += 'background-color:#D1D5DB;text-align:center;';
                    }
                    else if (text === 'Achieved') {
                        style += 'background-color:#DCFCE7;color:#166534;font-weight:bold;';
                    }
                    else if (text === 'Not Achieved') {
                        style += 'background-color:#FEE2E2;color:#991B1B;font-weight:bold;';
                    }
                    else if (idx === 0) {
                        style += 'text-align:left;font-weight:bold;';
                    }
                    else if (text.startsWith('-')) {
                        style += 'color:#DC2626;font-weight:bold;';
                    }
                    else if (text.startsWith('+')) {
                        style += 'color:#16A34A;font-weight:bold;';
                    }
                    else {
                        style += 'font-weight:bold;';
                    }

                    html += '<td style="' + style + '">' + text + '</td>';
                });
                html += '</tr>';
            });

            html += '</table></body></html>';

            const blob = new Blob(['\uFEFF' + html], { type: 'application/vnd.ms-excel;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'productivity_by_delivered_{{ now()->format("Y-m-d") }}.xls';
            a.click();
            URL.revokeObjectURL(url);
        }
    </script>
</x-app-layout>
