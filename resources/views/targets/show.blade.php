<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Title & Actions -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Target: {{ $target->name }}</h2>
                <div class="flex gap-2">
                    <a href="{{ route('targets.edit', $target) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Edit Target
                    </a>
                    <a href="{{ route('targets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Back to List
                    </a>
                </div>
            </div>
            <!-- Target Info -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-5 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Target Name</p>
                        <p class="text-lg font-semibold">{{ $target->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Week Number</p>
                        <p class="text-lg font-bold text-indigo-600">Week {{ $target->week_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Period</p>
                        <p class="text-lg font-semibold">{{ $target->start_date->format('d M') }} - {{ $target->end_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Monthly Target</p>
                        <p class="text-lg font-bold text-blue-600">{{ number_format($target->monthly_target) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        @if ($target->isActive())
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
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500">
                        Apply All Days: <span class="font-semibold {{ $target->apply_all_days ? 'text-green-600' : 'text-yellow-600' }}">{{ $target->apply_all_days ? 'Yes (same target for all days)' : 'No (individual day targets)' }}</span>
                    </p>
                </div>
            </div>

            <!-- Manpower List -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold">Manpower Targets ({{ $target->items->count() }})</h3>
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
                                @unless ($target->apply_all_days)
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
                            @forelse ($target->items as $item)
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
                                    @unless ($target->apply_all_days)
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
                                    <td colspan="{{ $target->apply_all_days ? 7 : 13 }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No manpower in this target.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
