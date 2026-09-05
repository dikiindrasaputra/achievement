<x-app-layout>
    <div x-data="{ showModal: false, modalName: '', modalDate: '', modalAction: '' }" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Whitelist Management</h2>
            </div>

            <!-- Week Navigation & Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex items-end gap-4 flex-wrap">
                    <!-- Week Navigation -->
                    <div class="flex items-center gap-2">
                        <a href="?year={{ $weekNumber > 1 ? $year : $year - 1 }}&week_number={{ $weekNumber > 1 ? $weekNumber - 1 : 52 }}&contract_type={{ request('contract_type') }}&search={{ request('search') }}"
                           class="px-3 py-2 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-sm">
                            &larr; Prev
                        </a>
                        <div class="text-center px-4">
                            <p class="text-lg font-bold text-indigo-600">Week {{ $weekNumber }}</p>
                            <p class="text-xs text-gray-500">{{ $weekStart->format('d M') }} - {{ $weekEnd->format('d M Y') }}</p>
                        </div>
                        <a href="?year={{ $weekNumber < 52 ? $year : $year + 1 }}&week_number={{ $weekNumber < 52 ? $weekNumber + 1 : 1 }}&contract_type={{ request('contract_type') }}&search={{ request('search') }}"
                           class="px-3 py-2 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-sm">
                            Next &rarr;
                        </a>
                    </div>

                    <!-- Filters -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract</label>
                        <select onchange="window.location.href='?year={{ $year }}&week_number={{ $weekNumber }}&contract_type='+this.value+'&search={{ request('search') }}'" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All</option>
                            <option value="dedicated" {{ request('contract_type') == 'dedicated' ? 'selected' : '' }}>Dedicated</option>
                            <option value="mitra" {{ request('contract_type') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                        <form method="GET" class="flex gap-1">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="week_number" value="{{ $weekNumber }}">
                            <input type="hidden" name="contract_type" value="{{ request('contract_type') }}">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="NIP or Name" class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <button type="submit" class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">Cari</button>
                        </form>
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-4 flex gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-500"></span> Whitelisted</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-200 dark:bg-gray-600"></span> Available</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-200 dark:bg-red-800"></span> No Quota</span>
                </div>
            </div>

            <!-- Week Grid -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase sticky left-0 bg-gray-50 dark:bg-gray-700 z-10" style="min-width:200px">NIP / Name</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase" style="min-width:60px">Quota</th>
                                @foreach ($weekDays as $day)
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase {{ $day['is_today'] ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700' : 'text-gray-500' }}" style="min-width:90px">
                                        <div>{{ $day['day_name'] }}</div>
                                        <div class="text-[10px] font-normal">{{ $day['date_display'] }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="manpower-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @include('whitelists._rows', ['manpower' => $manpower])
                        </tbody>
                    </table>
                </div>

                {{-- Load More --}}
                <div id="load-more-container" class="p-4 text-center" x-data="whitelistLoadMore({{ $manpower->count() < 20 ? 'false' : 'true' }})" x-init="init()">
                    <button x-show="hasMore && !loading" @click="loadMore()"
                            class="px-6 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90"
                            style="background-color: #EE4D2D;">
                        Load More
                    </button>
                    <div x-show="loading" class="text-sm text-gray-500">Memuat data...</div>
                    <div x-show="!hasMore && !loading" class="text-sm text-gray-400">Semua data telah dimuat</div>
                </div>
            </div>
        </div>

        <!-- Custom Confirmation Modal -->
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showModal = false"></div>

                <!-- Modal Panel -->
                <div x-show="showModal"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                     class="relative inline-block overflow-hidden text-left align-bottom transition-all bg-white rounded-2xl shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="p-6">
                        <!-- Icon -->
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>

                        <!-- Content -->
                        <h3 class="text-lg font-bold text-gray-900 text-center">Hapus Whitelist?</h3>
                        <p class="mt-2 text-sm text-gray-500 text-center">
                            Hapus whitelist <span class="font-semibold text-gray-700" x-text="modalName"></span> di hari <span class="font-semibold text-gray-700" x-text="modalDate"></span>?
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3 justify-center">
                        <button @click="showModal = false"
                                class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <form :action="modalAction" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 transition-colors">
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function whitelistLoadMore(initialHasMore) {
            return {
                page: 1,
                hasMore: initialHasMore,
                loading: false,

                init() {
                    this.page = 1;
                },

                loadMore() {
                    if (this.loading || !this.hasMore) return;
                    this.loading = true;
                    this.page++;

                    const params = new URLSearchParams(window.location.search);
                    params.set('page', this.page);

                    fetch('?' + params.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(result => {
                        const tbody = document.getElementById('manpower-body');
                        tbody.insertAdjacentHTML('beforeend', result.html);
                        this.hasMore = result.hasMore;
                        this.loading = false;
                    })
                    .catch(() => {
                        this.loading = false;
                        this.page--;
                    });
                },
            };
        }
    </script>
</x-app-layout>
