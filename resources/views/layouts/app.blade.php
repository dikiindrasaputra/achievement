<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white">
        <div x-data="{ open: false, collapsed: localStorage.getItem('sidebar-collapsed') === 'true' }" class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            @include('layouts.navigation')

            <!-- Main Content -->
            <div class="flex-1 overflow-y-auto">
                <main class="p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Notification Popup -->
        @if (session('success') || session('error'))
        <div x-data="notification()" x-init="show()" x-cloak
             class="fixed top-6 right-6 z-[9999] max-w-sm w-full pointer-events-none">
            <div x-show="visible" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-[-1rem] scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-[-1rem] scale-95"
                 class="pointer-events-auto rounded-xl shadow-2xl border {{ session('success') ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        @if (session('success'))
                            <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold {{ session('success') ? 'text-green-800' : 'text-red-800' }}">
                            {{ session('success') ? 'Berhasil!' : 'Gagal!' }}
                        </p>
                        <p class="text-sm {{ session('success') ? 'text-green-600' : 'text-red-600' }} mt-0.5">
                            {{ session('success') ?: session('error') }}
                        </p>
                    </div>
                    <button @click="hide()" class="flex-shrink-0 {{ session('success') ? 'text-green-400 hover:text-green-600' : 'text-red-400 hover:text-red-600' }} transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <!-- Progress bar -->
                <div class="mt-3 h-1 rounded-full {{ session('success') ? 'bg-green-200' : 'bg-red-200' }} overflow-hidden">
                    <div x-ref="progress" class="h-full rounded-full {{ session('success') ? 'bg-green-500' : 'bg-red-500' }} transition-all" :style="'width: 100%'"></div>
                </div>
            </div>
        </div>

        <script>
            function notification() {
                return {
                    visible: false,
                    show() {
                        this.visible = true;
                        // Animate progress bar
                        this.$nextTick(() => {
                            this.$refs.progress.style.width = '0%';
                            this.$refs.progress.style.transition = 'width 3s linear';
                        });
                        // Auto dismiss after 3s
                        setTimeout(() => this.hide(), 3000);
                    },
                    hide() {
                        this.visible = false;
                    }
                }
            }
        </script>
        @endif
    </body>
</html>
