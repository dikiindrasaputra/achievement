{{-- Desktop Sidebar (hidden on mobile) --}}
<nav :class="collapsed ? 'w-[70px] min-w-[70px] max-w-[70px]' : 'w-[250px] min-w-[250px] max-w-[250px]'"
     class="hidden lg:flex bg-white border-r h-screen flex-col transition-all duration-300 shrink-0" style="border-color: #EE4D2D;">
    <!-- Logo -->
    <div class="flex items-center justify-between px-4 py-5 border-b shrink-0" style="border-color: #EE4D2D;">
        <a href="{{ route('dashboard') }}" class="flex items-center overflow-hidden">
            <span class="text-xl font-bold shrink-0" style="color: #EE4D2D;">SPX</span>
            <span x-show="!collapsed" x-transition class="ml-2 text-sm text-gray-600 whitespace-nowrap">Achievement</span>
        </a>
        <button @click="collapsed = !collapsed; localStorage.setItem('sidebar-collapsed', collapsed)" 
                class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 shrink-0">
            <svg x-show="!collapsed" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
            <svg x-show="collapsed" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 py-4 overflow-y-auto">
        <div class="px-3 space-y-1">
            <a href="{{ route('dashboard') }}" 
               :title="collapsed ? 'Dashboard' : ''"
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-700 hover:bg-gray-100' }}"
               style="{{ request()->routeIs('dashboard') ? 'background-color: #EE4D2D;' : '' }}">
                <svg class="w-5 h-5 shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="whitespace-nowrap">Dashboard</span>
            </a>

            <a href="{{ route('manpower.index') }}" 
               :title="collapsed ? 'Manpower' : ''"
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('manpower.*') ? 'text-white' : 'text-gray-700 hover:bg-gray-100' }}"
               style="{{ request()->routeIs('manpower.*') ? 'background-color: #EE4D2D;' : '' }}">
                <svg class="w-5 h-5 shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="whitespace-nowrap">Manpower</span>
            </a>

            <a href="{{ route('targets.index') }}" 
               :title="collapsed ? 'Targets' : ''"
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('targets.*') ? 'text-white' : 'text-gray-700 hover:bg-gray-100' }}"
               style="{{ request()->routeIs('targets.*') ? 'background-color: #EE4D2D;' : '' }}">
                <svg class="w-5 h-5 shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="whitespace-nowrap">Targets</span>
            </a>

            <a href="{{ route('achievements.index') }}" 
               :title="collapsed ? 'Achievements' : ''"
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('achievements.*') ? 'text-white' : 'text-gray-700 hover:bg-gray-100' }}"
               style="{{ request()->routeIs('achievements.*') ? 'background-color: #EE4D2D;' : '' }}">
                <svg class="w-5 h-5 shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="whitespace-nowrap">Achievements</span>
            </a>

            <a href="{{ route('whitelists.index') }}" 
               :title="collapsed ? 'Whitelists' : ''"
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('whitelists.*') ? 'text-white' : 'text-gray-700 hover:bg-gray-100' }}"
               style="{{ request()->routeIs('whitelists.*') ? 'background-color: #EE4D2D;' : '' }}">
                <svg class="w-5 h-5 shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="whitespace-nowrap">Whitelists</span>
            </a>
        </div>
    </div>

    <!-- User Profile (sticky bottom) -->
    <div class="border-t px-3 py-4 shrink-0" style="border-color: #EE4D2D;">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-semibold" style="background-color: #EE4D2D;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            </div>
            <div x-show="!collapsed" x-transition class="ml-3 flex-1 overflow-hidden">
                <p class="text-sm font-medium text-gray-700 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-gray-100 shrink-0" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- Mobile sidebar overlay --}}
<div x-show="open" x-transition:enter="transition-opacity ease-linear duration-300" 
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300" 
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden" @click="open = false">
</div>

{{-- Mobile sidebar --}}
<div x-show="open" x-transition:enter="transition ease-in-out duration-300" 
     x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in-out duration-300" 
     x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
     class="fixed inset-y-0 left-0 z-50 w-64 bg-white flex flex-col transform lg:hidden border-r" style="border-color: #EE4D2D;">
    
    {{-- Logo --}}
    <div class="flex items-center justify-between px-6 py-5 border-b shrink-0" style="border-color: #EE4D2D;">
        <a href="{{ route('dashboard') }}" class="flex items-center">
            <span class="text-xl font-bold" style="color: #EE4D2D;">SPX</span>
            <span class="ml-2 text-sm text-gray-600">Achievement</span>
        </a>
        <button @click="open = false" class="p-1 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Navigation Links --}}
    <div class="flex-1 py-4 overflow-y-auto">
        <div class="px-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-700 hover:bg-gray-100' }}" style="{{ request()->routeIs('dashboard') ? 'background-color: #EE4D2D;' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <a href="{{ route('manpower.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('manpower.*') ? 'text-white' : 'text-gray-700 hover:bg-gray-100' }}" style="{{ request()->routeIs('manpower.*') ? 'background-color: #EE4D2D;' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Manpower
            </a>
            <a href="{{ route('targets.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('targets.*') ? 'text-white' : 'text-gray-700 hover:bg-gray-100' }}" style="{{ request()->routeIs('targets.*') ? 'background-color: #EE4D2D;' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Targets
            </a>
            <a href="{{ route('achievements.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('achievements.*') ? 'text-white' : 'text-gray-700 hover:bg-gray-100' }}" style="{{ request()->routeIs('achievements.*') ? 'background-color: #EE4D2D;' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Achievements
            </a>
            <a href="{{ route('whitelists.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('whitelists.*') ? 'text-white' : 'text-gray-700 hover:bg-gray-100' }}" style="{{ request()->routeIs('whitelists.*') ? 'background-color: #EE4D2D;' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Whitelists
            </a>
        </div>
    </div>

    {{-- User Profile (sticky bottom) --}}
    <div class="border-t px-4 py-4 shrink-0" style="border-color: #EE4D2D;">
        <div class="flex items-center">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-semibold shrink-0" style="background-color: #EE4D2D;">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="ml-3 flex-1 overflow-hidden">
                <p class="text-sm font-medium text-gray-700 truncate">{{ Auth::user()->name }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-gray-100 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </div>
</div>
