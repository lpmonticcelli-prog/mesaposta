<x-app-layout>
    <x-slot name="header">Fluxo de Caixa (DRE Resumido)</x-slot>

    <div class="max-w-7xl mx-auto space-y-6 pb-12">
        <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-brand-dark flex flex-col md:flex-row gap-4 items-end">
            <form action="{{ route('admin.financeiro.fluxo') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full items-end">
                <div class="w-full sm:w-1/3">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Período (Início)</label>
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full border-gray-300 rounded-md text-sm font-bold shadow-sm focus:border-brand-gold">
                </div>
                <div class="w-full sm:w-1/3">
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Período (Fim)</label>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full border-gray-300 rounded-md text-sm font-bold shadow-sm focus:border-brand-gold">
                </div>
                <button type="submit" class="bg-brand-gold text-brand-dark px-6 py-2.5 font-black uppercase text-[10px] tracking-widest rounded-md hover:bg-yellow-500 shadow-sm h-[42px] transition-colors w-full sm:w-auto">Gerar Fluxo</button>
                <a href="{{ route('admin.financeiro.fluxo') }}" class="px-4 py-2.5 bg-gray-200 text-gray-700 font-black rounded-md uppercase text-[10px] shadow-sm flex items-center justify-center h-[42px] hover:bg-gray-300 transition-colors w-full sm:w-auto">Limpar</a>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- RECEITAS --}}
            <div class="bg-white rounded-xl shadow-md border-t-4 border-green-500 overflow-hidden">
                <div class="bg-green-50 px-6 py-4 border-b border-green-100 flex justify-between items-center">
                    <h3 class="text-green-800 font-black uppercase tracking-widest text-sm">Entradas (Receitas)</h3>
                    <span class="text-sm font-black text-green-700">R$ {{ number_format($receitas->sum('valor'), 2, ',', '.') }}</span>
                </div>
                <ul class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto p-2">
                    @forelse($receitas as $r)
                        <li class="p-4 flex justify-between items-center hover:bg-gray-50">
                            <div><span class="block text-xs font-black uppercase text-brand-dark">{{ $r->descricao }}</span><span class="text-[9px] text-gray-500 font-bold uppercase">{{ !empty($r->data_vencimento) ? \Carbon\Carbon::parse($r->data_vencimento)->format('d/m/Y') : 'S/ Data' }}</span></div>
                            <span class="text-sm font-black text-green-600">R$ {{ number_format($r->valor, 2, ',', '.') }}</span>
                        </li>
                    @empty
                        <li class="p-6 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Nenhuma receita listada.</li>
                    @endforelse
                </ul>
            </div>

            {{-- DESPESAS --}}
            <div class="bg-white rounded-xl shadow-md border-t-4 border-red-500 overflow-hidden">
                <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex justify-between items-center">
                    <h3 class="text-red-800 font-black uppercase tracking-widest text-sm">Saídas (Despesas)</h3>
                    <span class="text-sm font-black text-red-700">R$ {{ number_format($despesas->sum('valor'), 2, ',', '.') }}</span>
                </div>
                <ul class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto p-2">
                    @forelse($despesas as $d)
                        <li class="p-4 flex justify-between items-center hover:bg-gray-50">
                            <div><span class="block text-xs font-black uppercase text-brand-dark">{{ $d->descricao }}</span><span class="text-[9px] text-gray-500 font-bold uppercase">{{ !empty($d->data_vencimento) ? \Carbon\Carbon::parse($d->data_vencimento)->format('d/m/Y') : 'S/ Data' }}</span></div>
                            <span class="text-sm font-black text-red-600">R$ {{ number_format($d->valor, 2, ',', '.') }}</span>
                        </li>
                    @empty
                        <li class="p-6 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Nenhuma despesa listada.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>