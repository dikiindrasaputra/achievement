<x-guest-layout>
    {{-- Logo & Brand --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background-color: #EE4D2D;">
            <span class="text-2xl font-bold text-white">SPX</span>
        </div>
<h1 class="text-2xl font-bold text-gray-800">Achievement Tracker <br> Singkut Hub</h1>
        <p class="text-sm text-gray-500 mt-1"> <span class="font-bold">Muhammad Rizky Pratama | Facility Lead </span> <br> Login dengan akun Google kantor Anda</p>
    </div>

    {{-- Error Message --}}
    @if ($errors->any())
        <div class="mb-6 flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm text-red-600">{{ $errors->first('email') }}</span>
        </div>
    @endif

    {{-- Google Login Button --}}
    <a href="{{ route('google.redirect') }}"
       class="flex items-center justify-center w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:border-orange-300 hover:bg-orange-50 transition-all duration-200 shadow-sm">
        <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Sign in with Google
    </a>

    {{-- Footer --}}
    <div class="mt-8 text-center">
        <p class="text-xs text-gray-400">Hanya akun email kantor yang diizinkan</p>
    </div>
</x-guest-layout>
