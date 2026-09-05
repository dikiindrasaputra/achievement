<x-app-layout>
    <div x-data="{ showModal: false }" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Title & Action -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Target Management</h2>
                <a href="{{ route('targets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    Create New Target
                </a>
            </div>

            <!-- Target Picker -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun</label>
                        <select id="yearPicker" onchange="changeYear()" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    {{ $year }}{{ $year == now()->format('Y') ? ' (Sekarang)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Periode Target</label>
                        <select id="targetPicker" onchange="changeTarget()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @forelse ($targets as $target)
                                <option value="{{ $target->id }}" {{ $selectedTarget && $selectedTarget->id == $target->id ? 'selected' : '' }}>
                                    {{ $target->name }} - Week {{ $target->week_number }} ({{ $target->start_date->format('d M') }} - {{ $target->end_date->format('d M Y') }})
                                    @if ($target->isActive()) [AKTIF] @endif
                                </option>
                            @empty
                                <option value="">Belum ada target untuk tahun ini</option>
                            @endforelse
                        </select>
                    </div>
                    <a href="{{ route('targets.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">Reset</a>
                </div>
            </div>

            @if ($selectedTarget)
                <!-- Target Info -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-5 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Target Name</p>
                            <p class="text-lg font-semibold">{{ $selectedTarget->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Week Number</p>
                            <p class="text-lg font-bold text-indigo-600">Week {{ $selectedTarget->week_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Period</p>
                            <p class="text-lg font-semibold">{{ $selectedTarget->start_date->format('d M') }} - {{ $selectedTarget->end_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Monthly Target</p>
                            <p class="text-lg font-bold text-blue-600">{{ number_format($selectedTarget->monthly_target) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            @if ($selectedTarget->isActive())
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <p class="text-sm text-gray-500">
                            Apply All Days: <span class="font-semibold {{ $selectedTarget->apply_all_days ? 'text-green-600' : 'text-yellow-600' }}">{{ $selectedTarget->apply_all_days ? 'Yes (same target for all days)' : 'No (individual day targets)' }}</span>
                        </p>
                        <div class="flex gap-2">
                            <a href="{{ route('targets.edit', $selectedTarget) }}" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">Edit</a>
                            <form action="{{ route('targets.destroy', $selectedTarget) }}" method="POST" class="inline" x-ref="deleteForm">
                                @csrf
                                @method('DELETE')
                                <button type="button" @click="showModal = true" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Manpower List -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold">Manpower Targets ({{ $selectedTarget->items->count() }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contract</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Daily</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Weekly</th>
                                    @unless ($selectedTarget->apply_all_days)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-blue-50 dark:bg-blue-900">Day 1</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-blue-50 dark:bg-blue-900">Day 2</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-blue-50 dark:bg-blue-900">Day 3</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-blue-50 dark:bg-blue-900">Day 4</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-blue-50 dark:bg-blue-900">Day 5</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-blue-50 dark:bg-blue-900">Day 6</th>
                                    @endunless
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Achievement</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($selectedTarget->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-mono">{{ $item->manpower->nip }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $item->manpower->full_name }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->manpower->contract_type == 'dedicated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($item->manpower->contract_type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center font-bold">{{ $item->daily_target }}</td>
                                        <td class="px-4 py-3 text-sm text-center font-bold">{{ $item->weekly_target }}</td>
                                        @unless ($selectedTarget->apply_all_days)
                                            <td class="px-4 py-3 text-sm text-center bg-blue-50 dark:bg-blue-900">{{ $item->day_1 }}</td>
                                            <td class="px-4 py-3 text-sm text-center bg-blue-50 dark:bg-blue-900">{{ $item->day_2 }}</td>
                                            <td class="px-4 py-3 text-sm text-center bg-blue-50 dark:bg-blue-900">{{ $item->day_3 }}</td>
                                            <td class="px-4 py-3 text-sm text-center bg-blue-50 dark:bg-blue-900">{{ $item->day_4 }}</td>
                                            <td class="px-4 py-3 text-sm text-center bg-blue-50 dark:bg-blue-900">{{ $item->day_5 }}</td>
                                            <td class="px-4 py-3 text-sm text-center bg-blue-50 dark:bg-blue-900">{{ $item->day_6 }}</td>
                                        @endunless
                                        <td class="px-4 py-3 text-sm text-center">
                                            @php
                                                $weeklyAchievement = $item->manpower->getWeeklyAchievement();
                                            @endphp
                                            <span class="font-semibold {{ $weeklyAchievement >= $item->weekly_target ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $weeklyAchievement }}
                                            </span>
                                            <span class="text-gray-400">/ {{ $item->weekly_target }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $selectedTarget->apply_all_days ? 7 : 13 }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No manpower in this target.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-12 text-center">
                    <p class="text-gray-500 text-lg">Pilih periode target untuk melihat data</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showModal = false"></div>
            <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-95" class="relative inline-block overflow-hidden text-left align-bottom transition-all bg-white rounded-2xl shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <div class="p-6">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 text-center">Hapus Target?</h3>
                    <p class="mt-2 text-sm text-gray-500 text-center">Target ini akan dihapus permanen beserta semua data manpower-nya.</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3 justify-center">
                    <button @click="showModal = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button @click="$refs.deleteForm.submit()" class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 transition-colors">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function changeTarget() {
            const targetId = document.getElementById('targetPicker').value;
            const year = document.getElementById('yearPicker').value;
            if (targetId) {
                window.location.href = '{{ route("targets.index") }}?year=' + year + '&target_id=' + targetId;
            }
        }

        function changeYear() {
            const year = document.getElementById('yearPicker').value;
            window.location.href = '{{ route("targets.index") }}?year=' + year;
        }
    </script>
</x-app-layout>
