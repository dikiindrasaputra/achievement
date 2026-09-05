<x-app-layout>
    <div x-data="{ showModal: false, modalUrl: '', showDetail: false, selectedPerson: null }" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Header: Search + Actions --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-6">
                <form method="GET" class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIP atau Nama..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                </form>
                <div class="flex gap-2">
                    <a href="{{ route('manpower.create') }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add
                    </a>
                    <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import
                    </button>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 p-4">
                <form method="GET" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <div class="flex-1 sm:flex-none">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Contract</label>
                        <select name="contract_type" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                            <option value="">All</option>
                            <option value="dedicated" {{ request('contract_type') == 'dedicated' ? 'selected' : '' }}>Dedicated</option>
                            <option value="mitra" {{ request('contract_type') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                        </select>
                    </div>
                    <div class="flex-1 sm:flex-none">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Vehicle</label>
                        <select name="vehicle_type" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                            <option value="">All</option>
                            <option value="2wh" {{ request('vehicle_type') == '2wh' ? 'selected' : '' }}>2 Wheels</option>
                            <option value="4wh" {{ request('vehicle_type') == '4wh' ? 'selected' : '' }}>4 Wheels</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 py-2">
                        <input type="checkbox" name="show_inactive" id="show_inactive" {{ request('show_inactive') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                        <label for="show_inactive" class="text-xs text-gray-500 whitespace-nowrap">Inactive</label>
                    </div>
                </form>
            </div>

            {{-- Desktop: Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg hidden lg:block mb-6">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                            <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-3 py-2 text-center text-[10px] font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                            <th class="px-3 py-2 text-center text-[10px] font-medium text-gray-500 uppercase tracking-wider">Contract</th>
                            <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                            <th class="px-3 py-2 text-center text-[10px] font-medium text-gray-500 uppercase tracking-wider">Week</th>
                            <th class="px-3 py-2 text-center text-[10px] font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-3 py-2 text-center text-[10px] font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($manpower as $person)
                            <tr class="{{ !$person->is_active ? 'opacity-50' : '' }}">
                                <td class="px-3 py-2 whitespace-nowrap text-xs font-mono text-gray-900 dark:text-gray-100">{{ $person->nip }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900 dark:text-gray-100">{{ $person->full_name }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-center">
                                    <span class="px-1.5 inline-flex text-[10px] leading-5 font-semibold rounded-full {{ $person->vehicle_type == '2wh' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">{{ strtoupper($person->vehicle_type) }}</span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-center">
                                    <span class="px-1.5 inline-flex text-[10px] leading-5 font-semibold rounded-full {{ $person->contract_type == 'dedicated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($person->contract_type) }}</span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100">{{ $person->start_date->format('d M Y') }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-center text-gray-900 dark:text-gray-100">W{{ $person->getWeekNumber() }}/D{{ $person->getDayInWeek() }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-center">
                                    <span class="px-1.5 inline-flex text-[10px] leading-5 font-semibold rounded-full {{ $person->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $person->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-center space-x-1">
                                    <a href="{{ route('manpower.edit', $person) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <button type="button" @click="showModal = true; modalUrl = '{{ route('manpower.destroy', $person) }}'" class="text-red-600 hover:text-red-900">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-3 py-4 text-center text-xs text-gray-500">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 text-center">
                    Menampilkan {{ $manpower->count() }} manpower
                </div>
            </div>

            {{-- Mobile: Cards with lazy load --}}
            @php
                $manpowerJson = json_encode($manpower->map(fn($p) => [
                    'id' => $p->id,
                    'nip' => $p->nip,
                    'full_name' => $p->full_name,
                    'vehicle_type' => $p->vehicle_type,
                    'contract_type' => $p->contract_type,
                    'start_date' => $p->start_date->format('Y-m-d'),
                    'start_date_display' => $p->start_date->format('d M Y'),
                    'is_active' => $p->is_active,
                    'week' => $p->getWeekNumber(),
                    'day' => $p->getDayInWeek(),
                    'edit_url' => route('manpower.edit', $p),
                    'delete_url' => route('manpower.destroy', $p),
                ])->values());
            @endphp
            <div class="lg:hidden" x-data="mobileCards()" x-init="init()">
                <div class="space-y-3">
                    <template x-for="(person, idx) in visibleCards" :key="person.id">
                        <div class="bg-white rounded-lg p-4 border border-gray-100 shadow-sm"
                             @click="selectedPerson = person; showDetail = true"
                             :class="!person.is_active ? 'opacity-50' : ''">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0"
                                     :class="person.contract_type === 'dedicated' ? 'bg-green-500' : 'bg-yellow-500'">
                                    <span x-text="person.full_name.charAt(0)"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-sm text-gray-800 truncate" x-text="person.full_name.length > 18 ? person.full_name.substring(0, 18) + '...' : person.full_name"></div>
                                    <div class="text-[11px] text-gray-400 font-mono" x-text="person.nip"></div>
                                </div>
                                <div class="flex flex-col items-end gap-1 shrink-0">
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                                          :class="person.vehicle_type === '2wh' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                                          x-text="person.vehicle_type === '2wh' ? '2W' : '4W'"></span>
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                                          :class="person.contract_type === 'dedicated' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                                          x-text="person.contract_type === 'dedicated' ? 'DED' : 'MTR'"></span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-50">
                                <div class="text-[11px] text-gray-400">
                                    Start: <span class="text-gray-600" x-text="person.start_date_display"></span>
                                </div>
                                <div class="text-[11px] text-gray-400">
                                    W<span x-text="person.week"></span>/D<span x-text="person.day"></span>
                                </div>
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                                      :class="person.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                      x-text="person.is_active ? 'Active' : 'Inactive'"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-ref="loadMoreSentinel" x-show="hasMore" class="h-1"></div>
                <div x-show="loading" class="text-center py-4">
                    <div class="inline-flex items-center gap-2 text-gray-400 text-sm">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Memuat...
                    </div>
                </div>
                <div x-show="!hasMore && visibleCards.length >= 7 && !loading" class="text-center py-3 text-xs text-gray-400">Semua data ditampilkan</div>
                <div x-show="!hasMore && visibleCards.length < 7 && visibleCards.length > 0 && !loading" class="text-center py-3 text-xs text-gray-400">Semua data ditampilkan</div>
            </div>
        </div>

    {{-- Mobile: Detail/Edit/Delete Modal --}}
    <div x-show="showDetail" x-cloak class="fixed inset-0 z-50 overflow-y-auto lg:hidden" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showDetail = false"></div>
            <div class="relative inline-block overflow-hidden text-left align-bottom transition-all bg-white rounded-2xl shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <template x-if="selectedPerson">
                    <div>
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold shrink-0"
                                     :class="selectedPerson.contract_type === 'dedicated' ? 'bg-green-500' : 'bg-yellow-500'">
                                    <span x-text="selectedPerson.full_name.charAt(0)"></span>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-lg font-bold text-gray-900 truncate" x-text="selectedPerson.full_name"></h3>
                                    <p class="text-sm text-gray-400 font-mono" x-text="selectedPerson.nip"></p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-[10px] text-gray-400 uppercase">Vehicle</div>
                                    <div class="text-sm font-semibold" x-text="selectedPerson.vehicle_type === '2wh' ? '2 Wheels' : '4 Wheels'"></div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-[10px] text-gray-400 uppercase">Contract</div>
                                    <div class="text-sm font-semibold capitalize" x-text="selectedPerson.contract_type"></div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-[10px] text-gray-400 uppercase">Start Date</div>
                                    <div class="text-sm font-semibold" x-text="selectedPerson.start_date_display"></div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-[10px] text-gray-400 uppercase">Status</div>
                                    <div class="text-sm font-semibold" :class="selectedPerson.is_active ? 'text-green-600' : 'text-red-600'" x-text="selectedPerson.is_active ? 'Active' : 'Inactive'"></div>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3">
                            <button @click="showDetail = false" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Tutup</button>
                            <a :href="selectedPerson ? selectedPerson.edit_url : '#'" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors text-center">Edit</a>
                            <button @click="showDetail = false; showModal = true; modalUrl = selectedPerson.delete_url" class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
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
                    <h3 class="text-lg font-bold text-gray-900 text-center">Nonaktifkan Manpower?</h3>
                    <p class="mt-2 text-sm text-gray-500 text-center">Manpower ini akan dinonaktifkan dan tidak muncul di daftar aktif.</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3 justify-center">
                    <button @click="showModal = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <form :action="modalUrl" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 transition-colors">Ya, Nonaktifkan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- Import Modal --}}
    <div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Import Manpower from Excel</h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('manpower.import') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paste Excel Data</label>
                    <textarea name="import_data" rows="10" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" placeholder="[1298306]PERI YULIANTO&#10;[1298307]BUDI SETIAWAN&#10;[1298308]AHMAD RIDWAN"></textarea>
                    <p class="mt-1 text-sm text-gray-500">Format: [NIP]NAME per line</p>
                </div>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type</label>
                        <select name="vehicle_type" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="2wh">2 Wheels</option>
                            <option value="4wh">4 Wheels</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contract Type</label>
                        <select name="contract_type" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="dedicated">Dedicated</option>
                            <option value="mitra">Mitra</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Import</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function mobileCards() {
            return {
                allCards: [],
                visibleCards: [],
                page: 0,
                perPage: 7,
                hasMore: true,
                loading: false,

                init() {
                    this.allCards = {!! $manpowerJson !!};
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
                    if (this.loading || !this.hasMore) return;
                    this.loading = true;
                    setTimeout(() => {
                        const start = this.page * this.perPage;
                        const end = start + this.perPage;
                        const next = this.allCards.slice(start, end);
                        this.visibleCards = [...this.visibleCards, ...next];
                        this.page++;
                        this.hasMore = end < this.allCards.length;
                        this.loading = false;
                    }, 200);
                },
            };
        }
    </script>
</x-app-layout>
