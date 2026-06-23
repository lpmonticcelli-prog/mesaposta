<x-app-layout>
    <x-slot name="header">Contas a Receber (Entradas)</x-slot>

    <div class="max-w-7xl mx-auto space-y-6 pb-12">
        @if(session('success')) <div class="p-4 bg-green-100 text-green-800 font-bold shadow-sm rounded-md">{{ session('success') }}</div> @endif

        {{-- ========================================================================= --}}
        {{-- 📊 ABAS GERENCIAIS DE FILTRO (LOCAÇÃO VS MULTA)                             --}}
        {{-- ========================================================================= --}}
        <div class="flex flex-wrap gap-2 border-b-2 border-gray-200">
            <a href="{{ route('admin.financeiro.receber', ['filtro' => 'locacoes']) }}" 
               class="px-6 py-3 rounded-t-lg font-black text-xs uppercase tracking-widest transition-colors 
               {{ request('filtro') !== 'avarias' ? 'bg-green-600 text-white border-green-600 border-t-2 border-l-2 border-r-2 shadow-inner' : 'bg-gray-100 text-gray-500 hover:bg-green-50' }}">
                💵 Faturamento (Locações)
            </a>
            <a href="{{ route('admin.financeiro.receber', ['filtro' => 'avarias']) }}" 
               class="px-6 py-3 rounded-t-lg font-black text-xs uppercase tracking-widest transition-colors 
               {{ request('filtro') === 'avarias' ? 'bg-red-600 text-white border-red-600 border-t-2 border-l-2 border-r-2 shadow-inner' : 'bg-gray-100 text-gray-500 hover:bg-red-50' }}">
                🚨 Inadimplência (Multas Pendentes)
            </a>
        </div>

        {{-- PAINEL DE BUSCA AVANÇADA --}}
        <div class="bg-white p-6 rounded-b-xl rounded-tr-xl shadow-sm border-t-0 border border-gray-200 flex flex-col md:flex-row gap-4 items-end {{ request('filtro') === 'avarias' ? 'border-t-4 border-red-600' : 'border-t-4 border-green-600' }}">
            <form action="{{ route('admin.financeiro.receber') }}" method="GET" class="flex flex-col lg:flex-row gap-3 w-full items-end">
                
                {{-- Trava o filtro para manter a aba correta ao pesquisar --}}
                <input type="hidden" name="filtro" value="{{ request('filtro', 'locacoes') }}">

                <div class="w-full lg:w-1/3">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Busca no Caixa</label>
                    <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Descrição, OS ou Cliente..." class="w-full border-gray-300 rounded-md text-sm font-bold shadow-sm focus:border-green-600">
                </div>
                
                @if(request('filtro') !== 'avarias')
                <div class="w-full lg:w-1/5">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-md text-sm font-bold shadow-sm focus:border-green-600">
                        <option value="">Todos</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendentes</option>
                        <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Liquidados (Pagos)</option>
                    </select>
                </div>
                @endif

                <div class="w-full {{ request('filtro') === 'avarias' ? 'lg:w-1/4' : 'lg:w-1/6' }}">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Vencimento (De)</label>
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full border-gray-300 bg-gray-50 text-gray-800 rounded-md text-sm font-bold shadow-sm">
                </div>
                <div class="w-full {{ request('filtro') === 'avarias' ? 'lg:w-1/4' : 'lg:w-1/6' }}">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Vencimento (Até)</label>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full border-gray-300 bg-gray-50 text-gray-800 rounded-md text-sm font-bold shadow-sm">
                </div>
                <div class="w-full lg:w-auto flex gap-2">
                    <button type="submit" class="w-full px-6 py-2.5 {{ request('filtro') === 'avarias' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white font-black rounded-md uppercase text-[10px] shadow-sm tracking-widest transition-colors h-[42px]">Filtrar</button>
                    <a href="{{ route('admin.financeiro.receber', ['filtro' => request('filtro')]) }}" class="px-4 py-2.5 bg-gray-200 text-gray-700 font-black rounded-md uppercase text-[10px] shadow-sm flex items-center justify-center h-[42px] hover:bg-gray-300">Limpar</a>
                </div>
            </form>
        </div>

        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-200 mt-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Descrição / Título</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Data Vencimento</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Valor (R$)</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($lancamentos as $lanc)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-black text-brand-dark uppercase">{{ $lanc->descricao }}</div>
                                    @if($lanc->pedido && $lanc->pedido->cliente) 
                                        <div class="text-[9px] font-bold text-gray-500 mt-1 uppercase tracking-widest">Cliente: <span class="text-gray-800">{{ $lanc->pedido->cliente->nome }}</span></div> 
                                    @endif
                                    
                                    {{-- Botão de Atalho para a OS se for uma Multa --}}
                                    @if(request('filtro') === 'avarias' && $lanc->pedido_id)
                                        <div class="mt-2">
                                            <a href="{{ route('admin.pedidos.show', $lanc->pedido_id) }}" class="text-[9px] font-black text-blue-600 hover:text-blue-800 underline uppercase">Ver Contrato de Multa</a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-xs font-bold {{ $lanc->status === 'pendente' && (!empty($lanc->data_vencimento) && \Carbon\Carbon::parse($lanc->data_vencimento)->isPast()) ? 'text-red-600' : 'text-gray-600' }}">
                                    {{ !empty($lanc->data_vencimento) ? \Carbon\Carbon::parse($lanc->data_vencimento)->format('d/m/Y') : 'S/ Data' }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-black text-green-700">R$ {{ number_format($lanc->valor, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($lanc->status === 'pago') <span class="px-3 py-1 text-[9px] font-black rounded uppercase tracking-widest bg-green-100 text-green-800 border border-green-200">Líquidado</span>
                                    @else <span class="px-3 py-1 text-[9px] font-black rounded uppercase tracking-widest bg-red-100 text-red-800 border border-red-200 shadow-sm">Pendente</span> @endif
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-3 items-center">
                                    @if($lanc->status === 'pendente')
                                        <form action="{{ route('admin.financeiro.baixar', $lanc->id) }}" method="POST" onsubmit="return confirm('Confirmar o recebimento no banco?');">
                                            @csrf @method('PATCH') <input type="hidden" name="tipo" value="receber">
                                            <button type="submit" class="px-4 py-2 bg-green-600 text-white text-[9px] font-black rounded uppercase shadow-sm hover:bg-green-700">Dar Baixa</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.financeiro.destroy', $lanc->id) }}" method="POST" onsubmit="return confirm('Deseja excluir esta conta?');">
                                        @csrf @method('DELETE') <input type="hidden" name="tipo" value="receber">
                                        <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 text-[9px] font-black rounded border border-red-200 uppercase hover:bg-red-600 hover:text-white shadow-sm">Apagar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Nenhum título a receber encontrado nesta aba.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($lancamentos->hasPages()) <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">{{ $lancamentos->links() }}</div> @endif
        </div>
    </div>
</x-app-layout>