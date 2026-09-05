<x-app-layout>
    <div x-data="{ showModal: false, expandedTarget: {{ $selectedTarget ? $selectedTarget->id : 'null' }} }" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Header: Year picker + Create --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-6">
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-400 whitespace-nowrap">Tahun</label>
                    <select id="yearPicker" onchange="changeYear()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}{{ $year == now()->format('Y') ? ' (Sekarang)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ route('targets.create') }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create Target
                </a>
            </div>

            {{-- Target Week Cards --}}
            <div class="space-y-3">
                @forelse ($targets as $target)
                    @php
                        $isActive = $target->isActive();
                    @endphp
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden {{ $isActive ? 'ring-2 ring-green-400' : '' }}">
                        {{-- Card Header: click to expand --}}
                        <div class="p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                             @click="expandedTarget = expandedTarget === {{ $target->id }} ? null : {{ $target->id }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center shrink-0 {{ $isActive ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                                        <span class="text-[10px] leading-none font-medium">Wk</span>
                                        <span class="text-lg font-bold leading-none">{{ $target->week_number }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-sm text-gray-800 truncate">{{ $target->name }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $target->start_date->format('d M') }} - {{ $target->end_date->format('d M Y') }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="text-right hidden sm:block">
                                        <div class="text-[10px] text-gray-400">Monthly</div>
                                        <div class="text-sm font-bold text-blue-600">{{ number_format($target->monthly_target) }}</div>
                                    </div>
                                    <div class="text-right hidden sm:block">
                                        <div class="text-[10px] text-gray-400">Manpower</div>
                                        <div class="text-sm font-bold text-gray-800">{{ $target->items->count() }}</div>
                                    </div>
                                    @if ($isActive)
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-green-100 text-green-700">AKTIF</span>
                                    @endif
                                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="expandedTarget === {{ $target->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            {{-- Mobile: show monthly + manpower --}}
                            <div class="flex gap-4 mt-2 sm:hidden">
                                <div class="text-[11px] text-gray-400">Monthly: <span class="font-bold text-blue-600">{{ number_format($target->monthly_target) }}</span></div>
                                <div class="text-[11px] text-gray-400">Manpower: <span class="font-bold text-gray-800">{{ $target->items->count() }}</span></div>
                            </div>
                        </div>

                        {{-- Expanded Detail --}}
                        <div x-show="expandedTarget === {{ $target->id }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" x-cloak>
                            <div class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                                {{-- Target Info --}}
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 mb-4">
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                        <div class="text-[10px] text-gray-400 uppercase">Name</div>
                                        <div class="text-sm font-semibold text-gray-800">{{ $target->name }}</div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                        <div class="text-[10px] text-gray-400 uppercase">Monthly Target</div>
                                        <div class="text-sm font-bold text-blue-600">{{ number_format($target->monthly_target) }}</div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                        <div class="text-[10px] text-gray-400 uppercase">Apply All Days</div>
                                        <div class="text-sm font-semibold {{ $target->apply_all_days ? 'text-green-600' : 'text-yellow-600' }}">{{ $target->apply_all_days ? 'Yes' : 'No' }}</div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 flex items-end gap-2">
                                        <a href="{{ route('targets.edit', $target) }}" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition">Edit</a>
                                        <button type="button" @click="showModal = true; $event.stopPropagation()" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition" data-delete-url="{{ route('targets.destroy', $target) }}">Delete</button>
                                    </div>
                                </div>

                                {{-- Manpower Table --}}
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-xs text-gray-500 border-b">
                                                <th class="text-left py-2 px-2">NIP</th>
                                                <th class="text-left py-2 px-2">Name</th>
                                                <th class="text-left py-2 px-2 hidden sm:table-cell">Contract</th>
                                                <th class="text-center py-2 px-2">Daily</th>
                                                <th class="text-center py-2 px-2">Weekly</th>
                                                @unless ($target->apply_all_days)
                                                    <th class="text-center py-2 px-2 bg-blue-50 dark:bg-blue-900">D1</th>
                                                    <th class="text-center py-2 px-2 bg-blue-50 dark:bg-blue-900">D2</th>
                                                    <th class="text-center py-2 px-2 bg-blue-50 dark:bg-blue-900">D3</th>
                                                    <th class="text-center py-2 px-2 bg-blue-50 dark:bg-blue-900">D4</th>
                                                    <th class="text-center py-2 px-2 bg-blue-50 dark:bg-blue-900">D5</th>
                                                    <th class="text-center py-2 px-2 bg-blue-50 dark:bg-blue-900">D6</th>
                                                @endunless
                                                <th class="text-center py-2 px-2">Achievement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($target->items as $item)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50">
                                                    <td class="py-2 px-2 font-mono text-xs">{{ $item->manpower->nip }}</td>
                                                    <td class="py-2 px-2">
                                                        <div class="font-medium truncate max-w-[120px] sm:max-w-none">{{ $item->manpower->full_name }}</div>
                                                        <div class="text-[10px] text-gray-400 sm:hidden">{{ ucfirst($item->manpower->contract_type) }}</div>
                                                    </td>
                                                    <td class="py-2 px-2 hidden sm:table-cell">
                                                        <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full {{ $item->manpower->contract_type == 'dedicated' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                            {{ ucfirst($item->manpower->contract_type) }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-2 text-center font-bold">{{ $item->daily_target }}</td>
                                                    <td class="py-2 px-2 text-center font-bold">{{ $item->weekly_target }}</td>
                                                    @unless ($target->apply_all_days)
                                                        <td class="py-2 px-2 text-center bg-blue-50 dark:bg-blue-900 text-xs">{{ $item->day_1 }}</td>
                                                        <td class="py-2 px-2 text-center bg-blue-50 dark:bg-blue-900 text-xs">{{ $item->day_2 }}</td>
                                                        <td class="py-2 px-2 text-center bg-blue-50 dark:bg-blue-900 text-xs">{{ $item->day_3 }}</td>
                                                        <td class="py-2 px-2 text-center bg-blue-50 dark:bg-blue-900 text-xs">{{ $item->day_4 }}</td>
                                                        <td class="py-2 px-2 text-center bg-blue-50 dark:bg-blue-900 text-xs">{{ $item->day_5 }}</td>
                                                        <td class="py-2 px-2 text-center bg-blue-50 dark:bg-blue-900 text-xs">{{ $item->day_6 }}</td>
                                                    @endunless
                                                    <td class="py-2 px-2 text-center">
                                                        @php $wa = $item->manpower->getWeeklyAchievement(); @endphp
                                                        <span class="font-semibold {{ $wa >= $item->weekly_target ? 'text-green-600' : 'text-red-600' }}">{{ $wa }}</span>
                                                        <span class="text-gray-400 text-[10px]">/ {{ $item->weekly_target }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $target->apply_all_days ? 6 : 12 }}" class="py-4 text-center text-gray-400 text-sm">No manpower in this target.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-12 text-center">
                        <p class="text-gray-400 text-lg">Belum ada target untuk tahun {{ $selectedYear }}</p>
                        <a href="{{ route('targets.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Create Target
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showModal = false"></div>
            <div class="relative inline-block overflow-hidden text-left align-bottom transition-all bg-white rounded-2xl shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <div class="p-6">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 text-center">Hapus Target?</h3>
                    <p class="mt-2 text-sm text-gray-500 text-center">Target ini akan dihapus permanen beserta semua data manpower-nya.</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3 justify-center">
                    <button @click="showModal = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 transition-colors">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function changeYear() {
            const year = document.getElementById('yearPicker').value;
            window.location.href = '{{ route("targets.index") }}?year=' + year;
        }

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-delete-url]');
            if (btn) {
                e.stopPropagation();
                document.getElementById('deleteForm').action = btn.dataset.deleteUrl;
            }
        });
    </script>
</x-app-layout>
