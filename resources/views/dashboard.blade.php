<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Title -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Dashboard</h2>
            </div>

            <!-- Productivity by Delivered -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Productivity by Delivered</h3>
                    <button onclick="exportToExcel()" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Excel
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
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
                                        <div class="font-medium text-gray-800">{{ $person['name'] }}</div>
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
        function exportToExcel() {
            const table = document.querySelector('table');
            const weekLabel = 'Minggu {{ $weekNumber }} ({{ $weekStart->format("d M") }} - {{ $weekEnd->format("d M Y") }})';

            let html = '<html><head><meta charset="utf-8"></head><body>';
            html += '<table border="1">';

            // Title row
            html += '<tr><td colspan="' + table.querySelectorAll('thead th').length + '" style="font-size:14pt;font-weight:bold;background-color:#EE4D2D;color:white;text-align:center;padding:10px;">Productivity by Delivered - ' + weekLabel + '</td></tr>';

            // Empty row
            html += '<tr><td colspan="' + table.querySelectorAll('thead th').length + '"></td></tr>';

            // Header row
            html += '<tr>';
            table.querySelectorAll('thead th').forEach(th => {
                const text = th.innerText.replace(/\n/g, ' ');
                html += '<td style="background-color:#374151;color:white;font-weight:bold;text-align:center;padding:6px;font-size:10pt;">' + text + '</td>';
            });
            html += '</tr>';

            // Data rows
            table.querySelectorAll('tbody tr').forEach(tr => {
                if (tr.querySelector('td[colspan]')) return; // skip empty row
                html += '<tr>';
                tr.querySelectorAll('td').forEach((td, idx) => {
                    let text = td.innerText.replace(/\n/g, ' ').trim();
                    let style = 'padding:5px;text-align:center;font-size:10pt;';

                    // Rank column (last) - colored badge
                    if (idx === tr.querySelectorAll('td').length - 1) {
                        const rank = parseInt(text);
                        if (rank === 1) style += 'background-color:#16a34a;color:white;font-weight:bold;text-align:center;';
                        else if (rank === 2) style += 'background-color:#22c55e;color:white;font-weight:bold;text-align:center;';
                        else if (rank === 3) style += 'background-color:#4ade80;color:white;font-weight:bold;text-align:center;';
                        else style += 'background-color:#D1D5DB;text-align:center;';
                    }
                    // Status column (second to last)
                    else if (text === 'Achieved') {
                        style += 'background-color:#DCFCE7;color:#166534;font-weight:bold;';
                    }
                    else if (text === 'Not Achieved') {
                        style += 'background-color:#FEE2E2;color:#991B1B;font-weight:bold;';
                    }
                    // Name column (first) - left align
                    else if (idx === 0) {
                        style += 'text-align:left;font-weight:bold;';
                    }
                    // GAP column - red if negative
                    else if (text.startsWith('-')) {
                        style += 'color:#DC2626;font-weight:bold;';
                    }
                    else if (text.startsWith('+')) {
                        style += 'color:#16A34A;font-weight:bold;';
                    }
                    // Numeric columns - center
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
