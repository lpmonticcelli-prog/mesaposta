<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 bg-brand-dark text-white flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static border-r border-gray-900 shadow-2xl shrink-0">
    <div class="h-20 flex items-center justify-center border-b border-gray-900 bg-brand-black px-4 shrink-0">
        <img src="https://dipdrinks.com/wp-content/uploads/2022/06/cropped-Simbolo-Dourado.png" alt="Logo" class="h-10 w-auto mr-3">
        <span class="text-brand-gold font-black text-xl tracking-widest uppercase mt-1">DIP ERP</span>
    </div>
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
        <p class="px-3 text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 mt-2">Visão Macro</p>
        <a href="{{ url('/dashboard') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all text-gray-400 hover:bg-gray-900 hover:text-brand-gold font-bold">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            Painel ERP
        </a>
        <p class="px-3 text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 mt-8">Comercial & Operação</p>
        <a href="{{ url('/admin/clientes') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all text-gray-400 hover:bg-gray-900 hover:text-brand-gold font-bold">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Carteira de Clientes
        </a>
        <a href="{{ url('/admin/orcamentos') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all text-gray-400 hover:bg-gray-900 hover:text-brand-gold font-bold">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Orçamentos (Site)
        </a>
        <a href="{{ url('/admin/pedidos') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all text-gray-400 hover:bg-gray-900 hover:text-brand-gold font-bold">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Pedidos e OS
        </a>
        <a href="{{ url('/admin/produtos') }}" class="flex items-center px-4 py-3.5 rounded-lg transition-all bg-brand-gold text-brand-black font-black shadow-lg">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            Acervo e Estoque
        </a>
    </nav>
    <div class="p-4 bg-brand-black border-t border-gray-900 shrink-0">
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center px-4 py-3 text-sm font-black text-gray-500 border border-gray-800 rounded-lg hover:bg-brand-gold hover:text-brand-black transition-colors uppercase tracking-widest">
                Sair do Sistema
            </button>
        </form>
    </div>
</aside>
