<x-app-layout>
    <x-slot name="header">{{ $titulo ?? 'Mesa de Operações' }}</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-200 border-t-4 border-brand-dark">
            <div class="p-6 bg-brand-black flex justify-between items-center">
                <h4 class="text-sm font-black text-brand-gold uppercase tracking-widest">{{ $titulo ?? 'OS' }}</h4>
                <a href="{{ route('admin.pedidos.create') }}" class="px-5 py-2.5 bg-brand-gold text-brand-black text-xs font-black rounded-md hover:bg-brand-hover uppercase tracking-widest shadow-md transition-colors">
                    + Nova Abertura
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Doc #</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Cliente</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Data Evento</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Total</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($pedidos as $os)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-black text-brand-dark flex items-center gap-2">
                                    #{{ str_pad($os->id, 5, '0', STR_PAD_LEFT) }}
                                    @if($os->tipo === 'cobranca')
                                        <span class="px-2 py-0.5 bg-red-600 text-white text-[9px] font-black uppercase tracking-widest rounded shadow-sm">Avaria</span>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 text-sm font-bold text-gray-800">{{ $os->cliente->nome ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-gray-600">{{ \Carbon\Carbon::parse($os->data_evento)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 text-[10px] font-black rounded uppercase tracking-widest {{ $os->status === 'orcamento' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-700' }}">
                                        {{ str_replace('_', ' ', $os->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-black text-brand-dark">R$ {{ number_format($os->valor_total, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <a href="{{ route('admin.pedidos.show', $os->id) }}" class="inline-flex items-center text-[10px] font-black bg-brand-dark text-brand-gold px-3 py-1.5 rounded uppercase hover:bg-brand-gold hover:text-brand-dark transition-colors shadow-sm">
                                        Editar / Abrir
                                    </a>
                                    <a href="{{ route('admin.pedidos.imprimir', $os->id) }}" target="_blank" class="inline-flex items-center text-[10px] font-black bg-gray-200 text-gray-700 px-3 py-1.5 rounded uppercase hover:bg-gray-300 hover:text-black transition-colors shadow-sm">
                                        🖨️ PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Nenhum registro encontrado nesta categoria.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($pedidos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $pedidos->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>