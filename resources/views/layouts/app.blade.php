<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DIP Drinks ERP</title>

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 
                        brand: { 
                            dark: '#111111',
                            black: '#0a0a0a',
                            gold: '#ffc20c',
                            hover: '#e0a800',
                            light: '#f8f9fa'
                        } 
                    },
                    fontFamily: { sans: ['Figtree', 'sans-serif'] }
                }
            }
        }
    </script>

    <script>
        const consoleWarnOriginal = console.warn;
        console.warn = function() {
            if (arguments[0] && arguments[0].includes('cdn.tailwindcss.com should not be used in production')) return;
            consoleWarnOriginal.apply(console, arguments);
        };
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #ffc20c; }
    </style>
</head>
<body class="font-sans antialiased text-gray-800 bg-brand-light overflow-hidden" x-data="{ sidebarOpen: false, profileOpen: false }">

    <div class="flex h-screen w-full">
        
        <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" x-transition.opacity></div>

        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 bg-brand-dark text-white flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static border-r border-gray-900 shadow-2xl shrink-0">
            
            <div class="h-20 flex items-center justify-center border-b border-gray-900 bg-brand-black px-4 shrink-0">
                <img src="https://dipdrinks.com/wp-content/uploads/2022/06/cropped-Simbolo-Dourado.png" alt="Logo" class="h-10 w-auto mr-3">
                <span class="text-brand-gold font-black text-xl tracking-widest uppercase mt-1">DIP ERP</span>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
             
                <p class="px-3 text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 mt-2">Visão Macro</p>
                
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-gold text-brand-black font-black shadow-lg' : 'text-gray-400 hover:bg-gray-900 hover:text-brand-gold font-bold' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Painel ERP
                </a>

                <p class="px-3 text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 mt-8">Comercial & Operação</p>

                <a href="{{ route('admin.clientes.index') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all {{ request()->routeIs('admin.clientes.*') ? 'bg-brand-gold text-brand-black font-black shadow-lg' : 'text-gray-400 hover:bg-gray-900 hover:text-brand-gold font-bold' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Carteira de Clientes
                </a>
                
                <a href="{{ route('admin.orcamentos.index') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all {{ request()->routeIs('admin.orcamentos.*') ? 'bg-brand-gold text-brand-black font-black shadow-lg' : 'text-gray-400 hover:bg-gray-900 hover:text-brand-gold font-bold' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Orçamentos (Site)
                </a>
 
                <a href="{{ route('admin.pedidos.index') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all {{ request()->routeIs('admin.pedidos.*') && !request()->routeIs('admin.orcamentos.*') && !request()->routeIs('admin.pedidos.create') ? 'bg-brand-gold text-brand-black font-black shadow-lg' : 'text-gray-400 hover:bg-gray-900 hover:text-brand-gold font-bold' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Pedidos e OS
                </a>
  
                <a href="{{ route('admin.produtos.index') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all {{ request()->routeIs('admin.produtos.*') ? 'bg-brand-gold text-brand-black font-black shadow-lg' : 'text-gray-400 hover:bg-gray-900 hover:text-brand-gold font-bold' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Acervo e Estoque
                </a>

                <p class="px-3 text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 mt-8">Cofre (Financeiro)</p>
                
                <a href="{{ route('admin.financeiro.receber') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all {{ request()->routeIs('admin.financeiro.receber') ? 'bg-green-600 text-white font-black shadow-lg' : 'text-gray-400 hover:bg-gray-900 hover:text-green-500 font-bold group' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.financeiro.receber') ? 'text-white' : 'group-hover:text-green-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Contas a Receber
                </a>
                
                <a href="{{ route('admin.financeiro.pagar') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all {{ request()->routeIs('admin.financeiro.pagar') ? 'bg-red-600 text-white font-black shadow-lg' : 'text-gray-400 hover:bg-gray-900 hover:text-red-500 font-bold group' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.financeiro.pagar') ? 'text-white' : 'group-hover:text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                    Contas a Pagar
                </a>
                
                <a href="{{ route('admin.financeiro.fluxo') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all {{ request()->routeIs('admin.financeiro.fluxo') ? 'bg-blue-600 text-white font-black shadow-lg' : 'text-gray-400 hover:bg-gray-900 hover:text-blue-500 font-bold group' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.financeiro.fluxo') ? 'text-white' : 'group-hover:text-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    Fluxo de Caixa
                </a>

            </nav>

            <div class="p-4 bg-brand-black border-t border-gray-900 shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-3 text-sm font-black text-gray-500 border border-gray-800 rounded-lg hover:bg-brand-gold hover:text-brand-black transition-colors uppercase tracking-widest">
                        Sair do Sistema
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 bg-brand-light relative">
            
            <header class="h-20 bg-white border-b-4 border-brand-gold flex items-center justify-between px-4 sm:px-6 shadow-sm z-30 shrink-0">
                <div class="flex items-center">
                    <button @click="sidebarOpen = true" class="text-brand-dark hover:text-brand-gold focus:outline-none lg:hidden mr-4 p-2 rounded-md transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    @if (isset($header))
                        <div class="hidden sm:block text-xl font-black text-brand-dark uppercase tracking-wide border-l-4 border-brand-dark pl-3 py-1">
                            {{ $header }}
                        </div>
                    @endif
                </div>

                <div class="flex items-center space-x-4 relative">
                    <div class="hidden sm:block text-right mt-1">
                        <p class="text-xs font-black text-brand-dark uppercase tracking-widest leading-none">{{ Auth::user()?->name ?? 'Diretoria' }}</p>
                        <p class="text-[10px] font-bold text-brand-hover uppercase mt-1">Administrador</p>
                    </div>
                    <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="w-11 h-11 rounded-full bg-brand-dark text-brand-gold font-black flex items-center justify-center border-2 border-brand-gold shadow-md hover:scale-105 transition-transform cursor-pointer">
                        {{ substr(Auth::user()?->name ?? 'D', 0, 1) }}
                    </button>
                    <div x-cloak x-show="profileOpen" x-transition class="absolute right-0 top-14 w-48 mt-2 bg-white border border-gray-200 rounded-md shadow-xl py-1 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-3 text-sm font-bold text-gray-700 hover:bg-brand-light hover:text-brand-gold transition-colors">Configurações da Conta</a>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>

        </div>
    </div>
</body>
</html>