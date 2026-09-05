<x-app-layout>
    <div x-data="{ showModal: false }" class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Title & Action -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Weekly Productivity: {{ $manpower->full_name }}</h2>
                <a href="{{ route('achievements.index', ['date' => request('date', now()->format('Y-m-d'))]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Back to Achievements
                </a>
            </div>
            <!-- Manpower Info -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">NIP</p>
                        <p class="text-lg font-mono font-semibold">{{ $manpower->nip }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="text-lg font-semibold">{{ $manpower->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contract</p>
                        <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full {{ $manpower->contract_type == 'dedicated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($manpower->contract_type) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Start Date</p>
                        <p class="text-lg font-semibold">{{ $manpower->start_date->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Weekly Summary -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Weekly Summary</h3>
                <div class="grid grid-cols-4 gap-6">
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Week Period</p>
                        <p class="text-lg font-semibold">{{ $data['week_start']->format('d M') }} - {{ $data['week_end']->format('d M') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Daily Target</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $data['daily_target'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Weekly Target</p>
                        <p class="text-3xl font-bold text-indigo-600">{{ $data['weekly_target'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Current Achievement</p>
                        <p class="text-3xl font-bold {{ $data['total_achievement'] >= $data['weekly_target'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $data['total_achievement'] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Accumulation Calculation -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Accumulation Calculation</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <p class="text-sm text-gray-500 mb-2">Remaining Days</p>
                        <p class="text-2xl font-bold">{{ $data['remaining_days'] }} days</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <p class="text-sm text-gray-500 mb-2">Remaining Target</p>
                        <p class="text-2xl font-bold {{ $data['remaining_target'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $data['remaining_target'] }}
                        </p>
                    </div>
                    <div class="bg-indigo-50 dark:bg-indigo-900 rounded-lg p-4 col-span-2">
                        <p class="text-sm text-indigo-600 mb-2">Suggested Daily Achievement</p>
                        <p class="text-4xl font-bold text-indigo-600">{{ $data['suggested_daily'] }}</p>
                        <p class="text-sm text-gray-500 mt-2">
                            To reach weekly target, you need to achieve {{ $data['suggested_daily'] }} per day for the remaining {{ $data['remaining_days'] }} days.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Apply Accumulation -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Apply Weekly Accumulation</h3>
                <p class="text-gray-600 mb-4">
                    This will apply the suggested daily achievement as a carryover for the remaining days in this week.
                </p>
                <form x-ref="accumForm" action="{{ route('achievements.store-weekly-accumulation', $manpower) }}" method="POST">
                    @csrf
                    <input type="hidden" name="date" value="{{ request('date', now()->format('Y-m-d')) }}">
                    <button type="button" @click="showModal = true" class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-semibold">
                        Apply Accumulation ({{ $data['suggested_daily'] }}/day for {{ $data['remaining_days'] }} days)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Apply Accumulation Confirmation Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showModal = false"></div>
            <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-95" class="relative inline-block overflow-hidden text-left align-bottom transition-all bg-white rounded-2xl shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <div class="p-6">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-indigo-100">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 text-center">Apply Accumulation?</h3>
                    <p class="mt-2 text-sm text-gray-500 text-center">Terapkan <span class="font-semibold">{{ $data['suggested_daily'] }}/hari</span> untuk <span class="font-semibold">{{ $data['remaining_days'] }} hari</span> ke depan?</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3 justify-center">
                    <button @click="showModal = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button @click="$refs.accumForm.submit()" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 transition-colors">Ya, Apply</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
