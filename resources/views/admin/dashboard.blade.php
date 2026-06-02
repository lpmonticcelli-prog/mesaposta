<x-app-layout>
    <x-slot name="header">Painel Executivo</x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-7xl mx-auto space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-brand-dark rounded-xl shadow-xl p-6 border-b-4 border-brand-gold transform transition hover:-translate-y-1">
                <p class="text-brand-gold text-xs font-black uppercase tracking-widest mb-1">Saldo do Cofre</p>
                <h3 class="text-3xl font-black text-white mt-2">R$ {{ number_format($caixaAtual, 2, ',', '.') }}</h3>
                <p class="text-[10px] text-gray-400 mt-3 uppercase tracking-widest border-t border-gray-800 pt-2">Caixa Realizado</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-b-4 border-green-500 transform transition hover:-translate-y-1">
                <p class="text-gray-500 text-xs font-black uppercase tracking-widest mb-1">A Receber (Mês)</p>
                <h3 class="text-3xl font-black text-green-600 mt-2">R$ {{ number_format($aReceberMes, 2, ',', '.') }}</h3>
                <p class="text-[10px] text-gray-400 mt-3 uppercase tracking-widest border-t border-gray-100 pt-2">Sinais e Pagamentos</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-b-4 border-red-500 transform transition hover:-translate-y-1">
                <p class="text-gray-500 text-xs font-black uppercase tracking-widest mb-1">A Pagar (Mês)</p>
                <h3 class="text-3xl font-black text-red-600 mt-2">R$ {{ number_format($aPagarMes, 2, ',', '.') }}</h3>
                <p class="text-[10px] text-gray-400 mt-3 uppercase tracking-widest border-t border-gray-100 pt-2">Fornecedores</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-b-4 border-blue-500 transform transition hover:-translate-y-1">
                <p class="text-gray-500 text-xs font-black uppercase tracking-widest mb-1">Vendas (Mês)</p>
                <h3 class="text-3xl font-black text-blue-600 mt-2">R$ {{ number_format($vendasMes, 2, ',', '.') }}</h3>
                <p class="text-[10px] text-gray-400 mt-3 uppercase tracking-widest border-t border-gray-100 pt-2">OS Aprovadas</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md border border-gray-200 p-6 flex flex-col">
                <h3 class="text-sm font-black text-brand-dark uppercase tracking-widest mb-4">Evolução Financeira</h3>
                <div class="relative flex-1 w-full min-h-[300px]">
                    <canvas id="chartFluxoCaixa"></canvas>
                </div>
            </div>

            <div class="bg-brand-dark rounded-xl shadow-xl border border-gray-800 overflow-hidden flex flex-col">
                <div class="bg-brand-black px-5 py-4 border-b border-gray-800 flex justify-between items-center">
                    <h3 class="text-xs font-black text-brand-gold uppercase tracking-widest">Caixa de Entrada</h3>
                    <span class="bg-brand-gold text-brand-black text-[10px] font-black px-2 py-0.5 rounded-full">{{ $ultimosOrcamentos->count() }}</span>
                </div>
                <ul class="divide-y divide-gray-800 flex-1 overflow-y-auto max-h-[300px]">
                    @forelse($ultimosOrcamentos as $orc)
                        <li class="p-4 hover:bg-gray-900 transition flex justify-between items-center group">
                            <div>
                                <p class="text-sm font-bold text-white">{{ $orc->cliente->nome ?? 'Cliente Site' }}</p>
                                <p class="text-[10px] font-bold text-brand-gold uppercase mt-1">Há {{ $orc->created_at->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('admin.pedidos.show', $orc->id) }}" class="text-[10px] font-black border border-gray-600 text-gray-300 px-3 py-1.5 rounded uppercase hover:bg-brand-gold hover:text-brand-dark transition-colors">Analisar</a>
                        </li>
                    @empty
                        <li class="p-8 text-center text-xs text-gray-500 font-bold uppercase tracking-widest">Nenhum orçamento pendente.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="bg-red-50 border-b border-red-100 px-5 py-4">
                    <h3 class="text-xs font-black text-red-700 uppercase tracking-widest">Alerta: Contas a Pagar</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <tbody class="divide-y divide-gray-50">
                            @forelse($proximasContasPagar as $conta)
                                <tr class="hover:bg-red-50 transition-colors">
                                    <td class="px-5 py-4 text-sm font-bold text-gray-900">{{ $conta->descricao }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-[10px] font-black px-2 py-1 rounded {{ $conta->status === 'atrasado' || \Carbon\Carbon::parse($conta->data_vencimento)->isPast() ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' }} uppercase">
                                            {{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right text-sm font-black text-red-600">R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-8 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Sem despesas urgentes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="bg-green-50 border-b border-green-100 px-5 py-4">
                    <h3 class="text-xs font-black text-green-700 uppercase tracking-widest">Recebimentos Agendados</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <tbody class="divide-y divide-gray-50">
                            @forelse($proximosRecebimentos as $conta)
                                <tr class="hover:bg-green-50 transition-colors">
                                    <td class="px-5 py-4 text-sm font-bold text-gray-900">{{ $conta->descricao }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-[10px] font-black px-2 py-1 rounded {{ $conta->status === 'atrasado' || \Carbon\Carbon::parse($conta->data_vencimento)->isPast() ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' }} uppercase">
                                            {{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right text-sm font-black text-green-600">R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-8 text-center text-xs text-gray-400 font-bold uppercase tracking-widest">Sem recebimentos urgentes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(document.getElementById('chartFluxoCaixa')) {
                const ctx = document.getElementById('chartFluxoCaixa').getContext('2d');
                const meses = {!! json_encode($meses) !!};
                const receitas = {!! json_encode($receitasGrafico) !!};
                const despesas = {!! json_encode($despesasGrafico) !!};

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: meses,
                        datasets: [
                            { label: 'Entradas (R$)', data: receitas, backgroundColor: '#ffc20c', borderRadius: 4, barPercentage: 0.6 },
                            { label: 'Saídas (R$)', data: despesas, backgroundColor: '#111111', borderRadius: 4, barPercentage: 0.6 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', labels: { font: { family: 'sans-serif', weight: 'bold' } } } },
                        scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)', borderDash: [5, 5] }, ticks: { font: { weight: 'bold' } } }, x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } } }
                    }
                });
            }
        });
    </script>
</x-app-layout>