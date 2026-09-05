<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SPX Achievement - Week <?php echo e($weekNumber); ?></title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;700&display=swap" rel="stylesheet" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        body { font-family: 'Figtree', sans-serif; }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type="number"] { -moz-appearance: textfield; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <div class="bg-white border-b sticky top-0 z-30" style="border-color: #EE4D2D;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold" style="color: #EE4D2D;">SPX</span>
                    <span class="text-gray-500">|</span>
                    <span class="text-sm text-gray-600">Achievement Tracker</span>
                </div>
                <div class="text-right flex items-center gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Minggu <?php echo e($weekNumber); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e($weekStart->format('d M')); ?> - <?php echo e($weekEnd->format('d M Y')); ?></p>
                    </div>
                    <a href="<?php echo e(route('login')); ?>" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors" style="background-color: #EE4D2D;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex gap-2 justify-center">
                <?php $__currentLoopData = $weekDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex-1 text-center px-2 py-2 rounded-lg <?php echo e($day['is_today'] ? 'border-2 font-bold' : 'border border-gray-200'); ?>"
                         style="<?php echo e($day['is_today'] ? 'border-color: #EE4D2D; background-color: #FFF5F2;' : ''); ?>">
                        <div class="text-xs text-gray-500"><?php echo e($day['day_name']); ?></div>
                        <div class="text-sm <?php echo e($day['is_today'] ? 'font-bold' : 'font-medium'); ?>" style="<?php echo e($day['is_today'] ? 'color: #EE4D2D;' : ''); ?>">
                            Day <?php echo e($day['day_number']); ?>

                        </div>
                        <div class="text-xs text-gray-400"><?php echo e($day['date_display']); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background-color: #FFF5F2;">
                        <svg class="w-5 h-5" style="color: #EE4D2D;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Minggu ke-<?php echo e($weekNumber); ?></p>
                        <p class="text-sm font-semibold text-gray-800"><?php echo e($weekStart->format('d M')); ?> - <?php echo e($weekEnd->format('d M Y')); ?></p>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-400 mb-2">Top Achievement</p>
                <div class="flex gap-3">
                    <?php $__empty_1 = true; $__currentLoopData = $topAchievers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                 style="background-color: <?php echo e($idx === 0 ? '#16a34a' : ($idx === 1 ? '#22c55e' : ($idx === 2 ? '#4ade80' : '#9ca3af'))); ?>;">
                                <?php echo e($idx + 1); ?>

                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-800 truncate"><?php echo e($person['name']); ?></p>
                                <p class="text-xs text-gray-400"><?php echo e($person['weekly_achievement']); ?>/<?php echo e($person['weekly_target']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-xs text-gray-400">Belum ada pencapaian</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <script type="application/json" id="initial-data"><?php echo json_encode($data); ?></script>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-20"
         x-data="manpowerList()"
         x-init="init()">
        
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="search" placeholder="Cari nama atau NIP..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                </div>
                <select x-model="contractFilter"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                    <option value="all">Semua Kontrak</option>
                    <option value="dedicated">Dedicated</option>
                    <option value="mitra">Mitra</option>
                </select>
                <select x-model="vehicleFilter"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                    <option value="all">Semua Kendaraan</option>
                    <option value="2wh">2 Wheel</option>
                    <option value="4wh">4 Wheel</option>
                </select>
            </div>
        </div>

        <?php if($totalManpower === 0): ?>
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <p class="text-gray-400 text-lg">Belum ada data pencapaian untuk minggu ini</p>
            </div>
        <?php endif; ?>

        <template x-for="(person, pIdx) in people" :key="person.id">
            <div class="bg-white rounded-lg shadow-sm mb-4 overflow-hidden"
                 x-data="personData(person)"
                 x-show="(search === '' || name.toLowerCase().includes(search.toLowerCase()) || nip.includes(search)) && (contractFilter === 'all' || contract_type === contractFilter) && (vehicleFilter === 'all' || vehicleType === vehicleFilter)">
                
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between cursor-pointer"
                     @click="expanded = !expanded">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0"
                             :style="isMet ? 'background-color: #16a34a' : 'background-color: #EE4D2D'">
                            <template x-if="isMet">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </template>
                            <template x-if="!isMet">
                                <span x-text="progressPercent + '%'"></span>
                            </template>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800" x-text="name"></p>
                            <p class="text-xs text-gray-400" x-text="'[' + nip + '] ' + name + ' | ' + contract_type.charAt(0).toUpperCase() + contract_type.slice(1) + ' | ' + (vehicleType || '').toUpperCase()"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs text-gray-400">Target</p>
                            <p class="font-bold text-sm" :class="isMet ? 'text-green-600' : 'text-gray-800'">
                                <span x-text="totalAchieved"></span> / <span x-text="weeklyTarget"></span>
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="expanded ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>

                
                <div x-show="expanded" x-transition class="p-4">
                    
                    <div class="sm:hidden mb-3 text-center">
                        <p class="text-xs text-gray-400">Pencapaian Mingguan</p>
                        <p class="font-bold" :class="isMet ? 'text-green-600' : 'text-gray-800'">
                            <span x-text="totalAchieved"></span> / <span x-text="weeklyTarget"></span>
                        </p>
                    </div>

                    
                    <div class="flex items-center justify-between mb-3 p-2 bg-gray-50 rounded-lg text-sm">
                        <div class="flex items-center gap-4">
                            <span class="text-gray-500">Sisa: <span class="font-bold text-gray-800" x-text="remaining"></span></span>
                            <span class="text-gray-500">Kosong: <span class="font-bold text-gray-800" x-text="emptyDays"></span> hari</span>
                            <span class="text-gray-500" x-show="emptyDays > 0">Isi/hari: <span class="font-bold" style="color: #EE4D2D;" x-text="autoFillPerDay"></span></span>
                        </div>
                        <button @click="autoFill()" x-show="emptyDays > 0 && remaining > 0"
                                class="px-3 py-1 text-xs font-semibold text-white rounded-md hover:opacity-90"
                                style="background-color: #EE4D2D;">
                            Auto Fill
                        </button>
                    </div>

                    
                    <div class="flex gap-1.5 mb-3 flex-wrap">
                        <template x-for="(d, idx) in days" :key="idx">
                            <button @click="toggleDayOff(idx)"
                                    class="px-2 py-1 rounded-md text-xs font-medium border transition-all"
                                    :class="{
                                        'bg-purple-50 border-purple-200 text-purple-400 cursor-not-allowed': d.is_whitelisted,
                                        'bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed opacity-50': d.is_past && !d.is_today && !dayOff[idx],
                                        'bg-orange-50 border-orange-300 text-orange-700 font-bold': dayOff[idx],
                                        'bg-white border-gray-200 text-gray-700 hover:border-orange-300 hover:bg-orange-50': !d.is_whitelisted && !d.is_past && !dayOff[idx],
                                        'bg-blue-50 border-blue-200 text-blue-700': d.is_today && !d.is_whitelisted && !dayOff[idx],
                                    }"
                                    :disabled="d.is_whitelisted || (d.is_past && !d.is_today) || d.has_achievement"
                                    x-text="d.day_name + ' ' + d.date_display + (d.is_whitelisted ? ' (Libur)' : (dayOff[idx] ? ' (OFF)' : (d.is_past ? ' · ' + (d.has_achievement ? d.achievement : 0) : '')))">
                            </button>
                        </template>
                    </div>

                    
                    <template x-if="!hasWhitelist">
                        <div class="mb-3 flex items-center gap-2 text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 shrink-0 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/>
                            </svg>
                            <span>Pilih hari off dengan klik pada hari, untuk perhitungan target yg lebih tepat. Tanpa set hari libur perhitungan akan salah.</span>
                        </div>
                    </template>

                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-xs text-gray-400 border-b">
                                    <th class="text-left py-2 px-2">Hari</th>
                                    <th class="text-center py-2 px-2">Target</th>
                                    <th class="text-center py-2 px-2">Carry</th>
                                    <th class="text-center py-2 px-2">Eff. Target</th>
                                    <th class="text-center py-2 px-2">Achievement</th>
                                    <th class="text-center py-2 px-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(s, idx) in sim" :key="idx">
                                    <tr class="border-b border-gray-50"
                                        :class="{
                                            'bg-purple-50': s.is_whitelisted,
                                            'bg-orange-50': s.status === 'day_off',
                                            'bg-blue-50': days[idx].is_today && !s.is_whitelisted && s.status !== 'day_off',
                                            'opacity-50': s.status === 'not_joined'
                                        }">
                                        <td class="py-2 px-2">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium" x-text="days[idx].day_name"></span>
                                                <span class="text-xs text-gray-400" x-text="days[idx].date_display"></span>
                                                <template x-if="days[idx].is_today">
                                                    <span class="text-xs px-1.5 py-0.5 rounded font-bold" style="background-color: #EE4D2D; color: white;">NOW</span>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="text-center py-2 px-2 text-gray-600" x-text="s.is_whitelisted ? '-' : dailyTarget"></td>
                                        <td class="text-center py-2 px-2 font-medium"
                                            :class="s.carryover > 0 ? 'text-red-500' : (s.carryover < 0 ? 'text-green-500' : 'text-gray-400')"
                                            x-text="s.is_whitelisted || s.status === 'not_joined' ? '-' : s.carryover"></td>
                                        <td class="text-center py-2 px-2 font-bold"
                                            :class="s.is_whitelisted || s.status === 'not_joined' ? 'text-gray-400' : 'text-blue-600'"
                                            x-text="s.is_whitelisted || s.status === 'not_joined' ? '-' : s.effective_target"></td>
                                        <td class="text-center py-2 px-2">
                                            <template x-if="s.is_whitelisted">
                                                <span class="text-purple-500 text-xs font-medium">Whitelist</span>
                                            </template>
                                            <template x-if="s.status === 'not_joined'">
                                                <span class="text-gray-400 text-xs">Belum join</span>
                                            </template>
                                            <template x-if="!s.is_whitelisted && s.status !== 'not_joined' && days[idx].has_achievement">
                                                <span class="font-bold text-green-600" x-text="s.achievement"></span>
                                            </template>
                                            <template x-if="!s.is_whitelisted && s.status !== 'not_joined' && s.status !== 'day_off' && !days[idx].has_achievement && !days[idx].is_past">
                                                <input type="number" min="0"
                                                       :value="predictionValues[idx] || ''"
                                                       @input="updatePrediction(idx, $event.target.value)"
                                                       class="w-20 text-center border rounded px-2 py-1 text-sm font-bold focus:ring-2 focus:outline-none"
                                                       style="border-color: #EE4D2D;"
                                                       placeholder="0">
                                            </template>
                                            <template x-if="!s.is_whitelisted && s.status !== 'not_joined' && !days[idx].has_achievement && days[idx].is_past">
                                                <span class="font-bold text-green-600" x-text="s.achievement"></span>
                                            </template>
                                            <template x-if="s.status === 'day_off'">
                                                <span class="text-xs font-medium px-2 py-1 rounded-full bg-orange-100 text-orange-700">OFF</span>
                                            </template>
                                        </td>
                                        <td class="text-center py-2 px-2">
                                            <template x-if="s.is_whitelisted || s.status === 'not_joined'">
                                                <span class="text-xs text-gray-400">-</span>
                                            </template>
                                            <template x-if="!s.is_whitelisted && s.status !== 'not_joined' && s.status === 'achieved'">
                                                <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-700">✓</span>
                                            </template>
                                            <template x-if="!s.is_whitelisted && s.status !== 'not_joined' && s.status === 'partial'">
                                                <span class="text-xs font-medium px-2 py-1 rounded-full bg-yellow-100 text-yellow-700" x-text="s.pct + '%'"></span>
                                            </template>
                                            <template x-if="!s.is_whitelisted && s.status !== 'not_joined' && s.status === 'low'">
                                                <span class="text-xs font-medium px-2 py-1 rounded-full bg-red-100 text-red-700" x-text="s.pct + '%'"></span>
                                            </template>
                                            <template x-if="!s.is_whitelisted && s.status !== 'not_joined' && s.status === 'pending'">
                                                <span class="text-xs text-gray-400" x-text="days[idx].is_past ? '✗' : '...'"></span>
                                            </template>
                                            <template x-if="s.status === 'day_off'">
                                                <span class="text-xs font-medium px-2 py-1 rounded-full bg-orange-100 text-orange-700">OFF</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    
                    <div class="mt-4 p-3 rounded-lg" :class="isMet ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium" x-text="isMet ? '✓ Target Tercapai' : '✗ Target Tidak Tercapai'"></p>
                                <p class="text-xs mt-1" x-show="excess > 0" x-text="'Lebih ' + excess + ' dari target'"></p>
                                <p class="text-xs mt-1" x-show="excess === 0 && !isMet" x-text="'Kurang ' + remaining + ' dari target'"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold" x-text="totalAchieved + ' / ' + weeklyTarget"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        
        <div x-ref="loadMoreSentinel" x-show="hasMore" class="h-1"></div>

        
        <div x-show="hasMore && !loading" class="text-center py-4">
            <button @click="loadMore()"
                    class="px-6 py-2.5 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors"
                    style="background-color: #EE4D2D;">
                Load More
            </button>
        </div>

        
        <div x-show="loading" class="text-center py-4">
            <div class="inline-flex items-center gap-2 text-gray-500">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm">Memuat data...</span>
            </div>
        </div>

        <div x-show="!hasMore && people.length > 0 && !loading" class="text-center py-4 text-sm text-gray-400">
            Semua data telah dimuat
        </div>
    </div>

    <script>
        function manpowerList() {
            return {
                search: '',
                contractFilter: 'all',
                vehicleFilter: 'all',
                people: [],
                page: 1,
                hasMore: true,
                loading: false,

                init() {
                    const el = document.getElementById('initial-data');
                    if (el) {
                        this.people = JSON.parse(el.textContent);
                    }
                    this.hasMore = <?php echo e($hasMore ? 'true' : 'false'); ?>;

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                if (this.hasMore && !this.loading) this.loadMore();
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
                    this.page++;

                    fetch('<?php echo e(route("landing.load-more")); ?>?page=' + this.page)
                        .then(response => response.json())
                        .then(result => {
                            this.people = [...this.people, ...result.data];
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

        function personData(person) {
            return {
                id: person.id,
                nip: person.nip,
                name: person.name,
                contract_type: person.contract_type,
                vehicleType: person.vehicle_type,
                dailyTarget: person.daily_target,
                weeklyTarget: person.weekly_target,
                weeklyAchievement: person.weekly_achievement,
                lastCarryover: person.last_carryover,
                days: person.days,
                expanded: false,
                predictionValues: {},
                dayOff: {},

                toggleDayOff(idx) {
                    if (this.days[idx].is_whitelisted || this.days[idx].is_past || this.days[idx].has_achievement) return;
                    this.dayOff[idx] = !this.dayOff[idx];
                    if (this.dayOff[idx]) {
                        delete this.predictionValues[idx];
                    }
                },

                get hasWhitelist() {
                    return this.days.some(d => d.is_whitelisted);
                },

                get totalAchieved() {
                    let total = this.weeklyAchievement;
                    for (let i = 0; i < this.days.length; i++) {
                        const d = this.days[i];
                        if (d.has_achievement || d.is_whitelisted || d.status === 'not_joined') continue;
                        const v = parseInt(this.predictionValues[i]) || 0;
                        total += v;
                    }
                    return total;
                },

                get remaining() {
                    return Math.max(0, this.weeklyTarget - this.totalAchieved);
                },

                get emptyDays() {
                    let count = 0;
                    for (let i = 0; i < this.days.length; i++) {
                        const d = this.days[i];
                        if (!d.has_achievement && !d.is_whitelisted && d.status !== 'not_joined' && !this.dayOff[i] && !d.is_past) count++;
                    }
                    return count;
                },

                get autoFillPerDay() {
                    if (this.emptyDays <= 0) return 0;
                    return Math.ceil(this.remaining / this.emptyDays);
                },

                get excess() {
                    return Math.max(0, this.totalAchieved - this.weeklyTarget);
                },

                get isMet() {
                    return this.totalAchieved >= this.weeklyTarget;
                },

                get progressPercent() {
                    return this.weeklyTarget > 0 ? Math.round((this.totalAchieved / this.weeklyTarget) * 100) : 0;
                },

                get sim() {
                    const daily = this.dailyTarget;
                    const result = [];
                    let carry = 0;

                    for (let i = 0; i < this.days.length; i++) {
                        const d = this.days[i];

                        if (d.day_number === 1) carry = 0;

                        const dayCarryover = carry;

                        if (d.is_whitelisted) {
                            result.push({ carryover: 0, effective_target: 0, achievement: 0, pct: 0, status: 'whitelisted', is_whitelisted: true });
                            continue;
                        }
                        if (d.status === 'not_joined') {
                            result.push({ carryover: 0, effective_target: 0, achievement: 0, pct: 0, status: 'not_joined', is_whitelisted: false });
                            continue;
                        }

                        const eff = daily + carry;

                        if (this.dayOff[i]) {
                            carry = eff;
                            result.push({ carryover: dayCarryover, effective_target: eff, achievement: 0, pct: 0, status: 'day_off', is_whitelisted: false });
                            continue;
                        }

                        let ach;
                        let status;

                        if (d.has_achievement) {
                            ach = d.achievement;
                            const pct = eff > 0 ? Math.round((ach / eff) * 100) : 0;
                            status = pct >= 100 ? 'achieved' : (pct >= 50 ? 'partial' : 'low');
                            carry = eff - ach;
                        } else {
                            ach = parseInt(this.predictionValues[i]) || 0;
                            if (d.is_past && ach === 0) {
                                status = 'pending';
                                carry = eff;
                            } else {
                                const pct = eff > 0 ? Math.round((ach / eff) * 100) : 0;
                                status = pct >= 100 ? 'achieved' : (pct >= 50 ? 'partial' : 'low');
                                carry = eff - ach;
                            }
                        }

                        const pct = eff > 0 ? Math.round((ach / eff) * 100) : 0;
                        result.push({
                            carryover: dayCarryover,
                            effective_target: eff,
                            achievement: ach,
                            pct: pct,
                            status: status,
                            is_whitelisted: false,
                        });
                    }
                    return result;
                },

                autoFill() {
                    const val = this.autoFillPerDay;
                    for (let i = 0; i < this.days.length; i++) {
                        const d = this.days[i];
                        if (!d.has_achievement && !d.is_whitelisted && d.status !== 'not_joined' && !this.dayOff[i] && !d.is_past) {
                            this.predictionValues[i] = val;
                        }
                    }
                },

                updatePrediction(idx, value) {
                    this.predictionValues[idx] = parseInt(value) || 0;
                },
            };
        }
    </script>
</body>
</html>
<?php /**PATH /home/dikii/kerja/achievement/achievement/resources/views/landing.blade.php ENDPATH**/ ?>