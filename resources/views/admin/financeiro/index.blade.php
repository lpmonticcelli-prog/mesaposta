<x-app-layout>
    <x-slot name="header">{{ $titulo }}</x-slot>

    <div class="max-w-7xl mx-auto space-y-6" x-data="{ modalAberto: false }">
        
        @if(session('success'))
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg font-bold shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-200 border-t-4 border-{{ $cor }}-500">
            <div class="p-6 bg-brand-black flex justify-between items-center">
                <h4 class="text-sm font-black text-white uppercase tracking-widest">{{ $titulo }}</h4>
                <button @click="modalAberto = true" class="px-5 py-2.5 bg-brand-gold text-brand-black text-xs font-black rounded-md hover:bg-brand-hover uppercase tracking-widest shadow-md transition-colors">+ Novo Lançamento</button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Descrição da Conta</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Vencimento</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($lancamentos as $l)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $l->descricao }}</td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-gray-600">{{ \Carbon\Carbon::parse($l->data_vencimento)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 text-[10px] font-black rounded uppercase tracking-widest {{ $l->status === 'pago' ? 'bg-green-100 text-green-700' : ($l->status === 'atrasado' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                                        {{ $l->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-black text-{{ $cor }}-600">R$ {{ number_format($l->valor, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Nenhum lançamento encontrado nesta gaveta.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($lancamentos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $lancamentos->links() }}
                </div>
            @endif
        </div>

        <div x-cloak x-show="modalAberto" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 backdrop-blur-sm transition-opacity" x-transition>
            <div @click.away="modalAberto = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden mx-4 transform transition-all border-t-4 border-brand-gold">
                <div class="bg-brand-dark px-6 py-4 flex justify-between items-center">
                    <h3 class="text-brand-gold font-extrabold uppercase tracking-widest">Registrar Movimentação</h3>
                    <button @click="modalAberto = false" type="button" class="text-gray-400 hover:text-white text-2xl font-bold">&times;</button>
                </div>
                
                <form action="{{ route('admin.financeiro.store') }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="tipo" value="{{ request()->routeIs('admin.financeiro.receber') ? 'receita' : 'despesa' }}">
                    
                    <div>
                        <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider mb-1">Descrição (Ex: Conta de Luz, Sinal Cliente X)</label>
                        <input type="text" name="descricao" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-gold focus:ring focus:ring-brand-gold font-medium">
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider mb-1">Valor (R$)</label>
                            <input type="number" step="0.01" name="valor" required min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-gold font-bold text-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider mb-1">Data de Vencimento</label>
                            <input type="date" name="data_vencimento" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-gold font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider mb-1">Status Atual</label>
                        <select name="status" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-gold font-bold">
                            <option value="pendente">Pendente (Aguardando)</option>
                            <option value="pago">Pago / Baixado</option>
                        </select>
                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-5">
                        <button type="button" @click="modalAberto = false" class="px-6 py-3 bg-gray-100 text-gray-700 font-extrabold rounded-md hover:bg-gray-200 uppercase tracking-widest transition-colors text-sm">Cancelar</button>
                        <button type="submit" class="px-6 py-3 bg-brand-gold text-brand-dark font-extrabold rounded-md hover:bg-brand-hover shadow-md uppercase tracking-widest transition-colors text-sm">Salvar Lançamento</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>