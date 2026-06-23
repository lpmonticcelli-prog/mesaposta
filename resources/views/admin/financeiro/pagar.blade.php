<x-app-layout>
    <x-slot name="header">Contas a Pagar (Despesas e Fornecedores)</x-slot>

    <div class="max-w-7xl mx-auto space-y-6 pb-12">
        @if(session('success')) <div class="p-4 bg-green-100 text-green-800 font-bold shadow-sm rounded-md">{{ session('success') }}</div> @endif

        <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-red-600 flex flex-col md:flex-row gap-4 items-end">
            <form action="{{ route('admin.financeiro.pagar') }}" method="GET" class="flex flex-col lg:flex-row gap-3 w-full items-end">
                <div class="w-full lg:w-1/3">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Busca Rápida</label>
                    <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Nome do Fornecedor ou Despesa..." class="w-full border-gray-300 rounded-md text-sm font-bold shadow-sm focus:border-red-600">
                </div>
                <div class="w-full lg:w-1/5">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-md text-sm font-bold shadow-sm focus:border-red-600">
                        <option value="">Todos</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Atrasados / A Pagar</option>
                        <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Liquidados (Pagos)</option>
                    </select>
                </div>
                <div class="w-full lg:w-1/6">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Vencimento (De)</label>
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full border-gray-300 bg-gray-50 text-gray-800 rounded-md text-sm font-bold shadow-sm">
                </div>
                <div class="w-full lg:w-1/6">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Vencimento (Até)</label>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full border-gray-300 bg-gray-50 text-gray-800 rounded-md text-sm font-bold shadow-sm">
                </div>
                <div class="w-full lg:w-auto flex gap-2">
                    <button type="submit" class="w-full px-6 py-2.5 bg-red-600 text-white font-black rounded-md uppercase text-[10px] shadow-sm tracking-widest hover:bg-red-700 h-[42px]">Filtrar</button>
                    <a href="{{ route('admin.financeiro.pagar') }}" class="px-4 py-2.5 bg-gray-200 text-gray-700 font-black rounded-md uppercase text-[10px] shadow-sm flex items-center justify-center h-[42px] hover:bg-gray-300">Limpar</a>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h4 class="text-xs font-black text-brand-dark uppercase tracking-widest border-b-2 border-red-600 pb-2 mb-4">Adicionar Nova Despesa</h4>
            <form action="{{ route('admin.financeiro.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf <input type="hidden" name="tipo" value="pagar"> <input type="hidden" name="status" value="pendente">
                <div class="md:col-span-2"><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Descrição</label><input type="text" name="descricao" required class="w-full border-gray-300 rounded-md text-sm font-bold"></div>
                <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Valor (R$)</label><input type="number" step="0.01" name="valor" required class="w-full border-red-300 bg-red-50 rounded-md text-sm font-black text-red-700"></div>
                <div><label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Data Vencimento</label><input type="date" name="data_vencimento" required class="w-full border-gray-300 rounded-md text-sm font-bold"></div>
                <div class="md:col-span-4 flex justify-end"><button type="submit" class="px-8 py-3 bg-red-600 text-white font-black rounded uppercase text-xs tracking-widest shadow-md hover:bg-red-700">Gravar Despesa</button></div>
            </form>
        </div>

        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Descrição da Despesa</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Vencimento</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Valor (R$)</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($lancamentos as $lanc)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-black text-brand-dark uppercase">{{ $lanc->descricao }}</td>
                                <td class="px-6 py-4 text-center text-xs font-bold {{ $lanc->status === 'pendente' && (!empty($lanc->data_vencimento) && \Carbon\Carbon::parse($lanc->data_vencimento)->isPast()) ? 'text-red-600' : 'text-gray-600' }}">
                                    {{ !empty($lanc->data_vencimento) ? \Carbon\Carbon::parse($lanc->data_vencimento)->format('d/m/Y') : 'S/ Data' }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-black text-red-700">- R$ {{ number_format($lanc->valor, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($lanc->status === 'pago') <span class="px-3 py-1 text-[9px] font-black rounded uppercase tracking-widest bg-gray-200 text-gray-800">Líquidado</span>
                                    @else <span class="px-3 py-1 text-[9px] font-black rounded uppercase tracking-widest bg-red-100 text-red-800 shadow-sm border border-red-200">A Pagar</span> @endif
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    @if($lanc->status === 'pendente')
                                        <form action="{{ route('admin.financeiro.baixar', $lanc->id) }}" method="POST" onsubmit="return confirm('Confirmar pagamento?');">
                                            @csrf @method('PATCH') <input type="hidden" name="tipo" value="pagar">
                                            <button type="submit" class="px-3 py-1.5 bg-gray-900 text-white text-[9px] font-black rounded uppercase hover:bg-black shadow-sm">Baixar</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.financeiro.destroy', $lanc->id) }}" method="POST" onsubmit="return confirm('Deseja excluir esta despesa?');">
                                        @csrf @method('DELETE') <input type="hidden" name="tipo" value="pagar">
                                        <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 text-[9px] font-black rounded border border-red-200 uppercase hover:bg-red-600 hover:text-white shadow-sm">Apagar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Nenhuma despesa ou fornecedor no radar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($lancamentos->hasPages()) <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">{{ $lancamentos->links() }}</div> @endif
        </div>
    </div>
</x-app-layout>