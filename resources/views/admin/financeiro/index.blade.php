<x-app-layout>
    <x-slot name="header">{{ $titulo }}</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-200 border-t-4 border-{{ $cor }}-500">
            <div class="p-6 bg-brand-black flex justify-between items-center">
                <h4 class="text-sm font-black text-white uppercase tracking-widest">{{ $titulo }}</h4>
                <button class="px-5 py-2.5 bg-brand-gold text-brand-black text-xs font-black rounded-md hover:bg-brand-hover uppercase tracking-widest shadow-md transition-colors">+ Novo Lançamento</button>
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
            
        </div>
    </div>
</x-app-layout>