<x-app-layout>
    <x-slot name="header">Gestão de Orçamentos (Pendentes)</x-slot>

    <div class="max-w-7xl mx-auto space-y-6 pb-12">
        <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-brand-gold flex flex-col md:flex-row gap-4 items-end justify-between">
            <form action="{{ route('admin.orcamentos.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-end">
                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Busca Inteligente</label>
                    <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Nome do Cliente ou N°..." class="bg-gray-50 border border-gray-300 px-4 py-2.5 rounded-md font-bold text-sm focus:border-brand-gold w-full sm:w-80 shadow-sm">
                </div>
                <button type="submit" class="bg-brand-gold text-brand-dark px-6 py-2.5 font-black uppercase text-[10px] tracking-widest rounded-md hover:bg-yellow-500 shadow-sm h-[42px] transition-colors">🔍 Filtrar</button>
                <a href="{{ route('admin.orcamentos.index') }}" class="px-4 py-2.5 bg-gray-200 text-gray-700 font-black rounded-md uppercase text-[10px] shadow-sm flex items-center h-[42px] hover:bg-gray-300 transition-colors">Limpar</a>
            </form>
            <a href="{{ route('admin.pedidos.create') }}" class="px-6 py-2.5 bg-brand-dark text-brand-gold font-black uppercase text-[10px] tracking-widest rounded-md shadow-md hover:bg-black whitespace-nowrap h-[42px] flex items-center transition-colors">+ Novo Orçamento</a>
        </div>

        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Orçamento #</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Cliente Contratante</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Datas (Saída - Festa)</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($pedidos as $p)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-black text-brand-dark">#{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-6 py-4 text-sm font-black text-gray-900 uppercase">{{ $p->cliente->nome ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-center text-xs font-bold text-gray-600">
                                    {{-- BLINDAGEM MÁXIMA DE DATAS --}}
                                    <span class="text-blue-600">{{ !empty($p->data_entrega) ? \Carbon\Carbon::parse($p->data_entrega)->format('d/m/Y') : 'S/ Data' }}</span>
                                    <span class="mx-1 font-black">»</span>
                                    <span class="text-brand-gold">{{ !empty($p->data_evento) ? \Carbon\Carbon::parse($p->data_evento)->format('d/m/Y') : 'S/ Data' }}</span>
                                </td>
                                <td class="px-6 py-4 text-right"><a href="{{ route('admin.pedidos.show', $p->id) }}" class="text-[10px] font-black bg-gray-800 text-brand-gold px-4 py-2 rounded-md shadow-sm uppercase tracking-widest hover:bg-black transition-colors">Acessar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Nenhum orçamento encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pedidos->hasPages()) <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">{{ $pedidos->links() }}</div> @endif
        </div>
    </div>
</x-app-layout>