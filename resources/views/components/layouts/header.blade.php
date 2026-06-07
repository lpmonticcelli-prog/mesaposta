@props(['title' => 'Painel'])
<header class="h-20 bg-white border-b-4 border-brand-gold flex items-center justify-between px-4 sm:px-6 shadow-sm z-30 shrink-0">
    <div class="flex items-center">
        <button @click="sidebarOpen = true" class="text-brand-dark hover:text-brand-gold focus:outline-none lg:hidden mr-4 p-2 rounded-md transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <div class="hidden sm:block text-xl font-black text-brand-dark uppercase tracking-wide border-l-4 border-brand-dark pl-3 py-1">
            <h2 class="font-extrabold text-2xl text-brand-dark leading-tight tracking-tight uppercase">{{ $title }}</h2>
        </div>
    </div>
    <div class="flex items-center space-x-4 relative">
        <div class="hidden sm:block text-right mt-1">
            <p class="text-xs font-black text-brand-dark uppercase tracking-widest leading-none">{{ auth()->user()->name ?? 'Diretoria Mesa Posta' }}</p>
            <p class="text-[10px] font-bold text-brand-hover uppercase mt-1">Administrador</p>
        </div>
        <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="w-11 h-11 rounded-full bg-brand-dark text-brand-gold font-black flex items-center justify-center border-2 border-brand-gold shadow-md hover:scale-105 transition-transform cursor-pointer">
            {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
        </button>
        <div x-cloak x-show="profileOpen" x-transition class="absolute right-0 top-14 w-48 mt-2 bg-white border border-gray-200 rounded-md shadow-xl py-1 z-50">
            <a href="{{ url('/profile') }}" class="block px-4 py-3 text-sm font-bold text-gray-700 hover:bg-brand-light hover:text-brand-gold transition-colors">Configurações da Conta</a>
        </div>
    </div>
</header>
