<x-app-layout>
    <x-slot name="header">Central de Gestão de Contratos</x-slot>

    <div class="max-w-7xl mx-auto space-y-6 pb-12">
        @if(session('success')) <div class="p-4 bg-green-100 text-green-800 font-bold shadow-sm rounded-md">{{ session('success') }}</div> @endif

        {{-- ========================================================================= --}}
        {{-- 📊 ABAS GERENCIAIS DE FILTRO (LOCAÇÃO VS MULTA)                             --}}
        {{-- ========================================================================= --}}
        <div class="flex flex-wrap gap-2 border-b-2 border-gray-200">
            <a href="{{ route('admin.pedidos.index', ['filtro' => 'locacoes']) }}" 
               class="px-6 py-3 rounded-t-lg font-black text-xs uppercase tracking-widest transition-colors 
               {{ request('filtro') !== 'avarias' ? 'bg-brand-dark text-brand-gold border-brand-gold border-t-2 border-l-2 border-r-2' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                📁 Contratos e Locações
            </a>
            <a href="{{ route('admin.pedidos.index', ['filtro' => 'avarias']) }}" 
               class="px-6 py-3 rounded-t-lg font-black text-xs uppercase tracking-widest transition-colors 
               {{ request('filtro') === 'avarias' ? 'bg-red-600 text-white border-red-600 border-t-2 border-l-2 border-r-2 shadow-inner' : 'bg-gray-100 text-gray-500 hover:bg-red-50' }}">
                ⚠️ Laudos Logísticos (Multas)
            </a>
        </div>

        {{-- PAINEL DE BUSCA AVANÇADA (Acompanha a Aba Ativa) --}}
        <div class="bg-white p-6 rounded-b-xl rounded-tr-xl shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 items-end">
            <form action="{{ route('admin.pedidos.index') }}" method="GET" class="flex flex-col lg:flex-row gap-3 w-full items-end">
                
                {{-- Trava o filtro oculto para manter a aba correta ao pesquisar --}}
                <input type="hidden" name="filtro" value="{{ request('filtro', 'locacoes') }}">

                <div class="w-full lg:w-1/3">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Busca Global</label>
                    <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Nome do Cliente ou N° da O.S." class="w-full border-gray-300 rounded-md text-sm font-bold shadow-sm focus:border-brand-gold focus:ring-brand-gold">
                </div>
                
                @if(request('filtro') !== 'avarias')
                <div class="w-full lg:w-1/5">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Situação Logística</label>
                    <select name="status" class="w-full border-gray-300 rounded-md text-sm font-bold shadow-sm focus:border-brand-gold focus:ring-brand-gold">
                        <option value="">Todos os Contratos</option>
                        <option value="confirmado" {{ request('status') == 'confirmado' ? 'selected' : '' }}>Confirmado (Pendente)</option>
                        <option value="em_separacao" {{ request('status') == 'em_separacao' ? 'selected' : '' }}>Em Separação no Galpão</option>
                        <option value="entregue" {{ request('status') == 'entregue' ? 'selected' : '' }}>Entregue (Na Festa)</option>
                        <option value="devolvido" {{ request('status') == 'devolvido' ? 'selected' : '' }}>Finalizado e Devolvido</option>
                    </select>
                </div>
                @endif

                <div class="w-full {{ request('filtro') === 'avarias' ? 'lg:w-1/4' : 'lg:w-1/6' }}">
                    <label class="block text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">Saída (Início)</label>
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full border-blue-300 bg-blue-50 text-blue-800 rounded-md text-sm font-bold shadow-sm">
                </div>
                <div class="w-full {{ request('filtro') === 'avarias' ? 'lg:w-1/4' : 'lg:w-1/6' }}">
                    <label class="block text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">Saída (Fim)</label>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full border-blue-300 bg-blue-50 text-blue-800 rounded-md text-sm font-bold shadow-sm">
                </div>
                
                <div class="w-full lg:w-auto flex gap-2">
                    <button type="submit" class="w-full px-6 py-2.5 bg-brand-gold text-brand-dark font-black rounded-md uppercase text-[10px] shadow-sm tracking-widest hover:bg-yellow-500 transition-colors h-[42px]">Filtrar</button>
                    <a href="{{ route('admin.pedidos.index', ['filtro' => request('filtro')]) }}" class="px-4 py-2.5 bg-gray-200 text-gray-700 font-black rounded-md uppercase text-[10px] shadow-sm flex items-center h-[42px]">Limpar</a>
                </div>
            </form>
        </div>

        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-200 mt-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">
                                {{ request('filtro') === 'avarias' ? 'O.S. (Multa) #' : 'O.S. #' }}
                            </th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Cliente Devedor</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">
                                {{ request('filtro') === 'avarias' ? 'Data do Dano' : 'Logística (Saída - Volta)' }}
                            </th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Valor</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($pedidos as $p)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-black text-brand-dark">
                                    #{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}
                                    @if(request('filtro') === 'avarias')
                                        <div class="text-[9px] font-bold text-red-500 mt-1 uppercase tracking-widest">Ref. Mãe #{{ str_pad($p->pedido_original_id, 5, '0', STR_PAD_LEFT) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-black text-gray-900 uppercase">{{ $p->cliente->nome ?? 'N/A' }}</div>
                                    <div class="text-[9px] font-bold text-gray-500 mt-1 uppercase tracking-widest">
                                        @if(request('filtro') === 'avarias')
                                            <span class="bg-red-100 text-red-800 px-2 py-0.5 rounded border border-red-200">MULTA POR AVARIA</span>
                                        @else
                                            <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded border border-green-200">{{ $p->forma_pagamento ?? 'A DEFINIR' }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-xs font-bold text-gray-600">
                                    @if(request('filtro') === 'avarias')
                                        <span class="text-gray-800">{{ $p->created_at->format('d/m/Y \à\s H:i') }}</span>
                                    @else
                                        <span class="text-blue-600">{{ $p->data_entrega ? $p->data_entrega->format('d/m/Y') : 'S/ Data' }}</span>
                                        <span class="mx-1 font-black">»</span>
                                        <span class="text-red-600">{{ $p->data_devolucao ? $p->data_devolucao->format('d/m/Y') : 'S/ Data' }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 text-[11px] font-black rounded uppercase tracking-widest {{ request('filtro') === 'avarias' ? 'bg-red-50 text-red-700 border-red-200 border' : 'bg-gray-100 text-brand-dark' }}">
                                        R$ {{ number_format($p->valor_total, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.pedidos.show', $p->id) }}" class="text-[10px] font-black {{ request('filtro') === 'avarias' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-brand-dark text-brand-gold hover:bg-black' }} px-4 py-2 rounded-md shadow-sm uppercase tracking-widest transition-colors">
                                        {{ request('filtro') === 'avarias' ? 'Ver Laudo' : 'Gerenciar' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Nenhum registro encontrado nesta aba.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pedidos->hasPages()) <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">{{ $pedidos->links() }}</div> @endif
        </div>
    </div>
</x-app-layout>