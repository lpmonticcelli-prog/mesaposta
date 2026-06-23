<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>DRE - {{ $clienteNome }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #111; font-size: 13px; margin: 0; padding: 20px; background: #fff; }
        .header { border-bottom: 3px solid #111; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; text-transform: uppercase; margin: 0 0 10px 0; letter-spacing: 1px; }
        .header-info { display: flex; justify-content: space-between; font-size: 12px; color: #555; text-transform: uppercase; }
        .grid { display: flex; gap: 15px; margin-bottom: 20px; }
        .box { flex: 1; border: 1px solid #ccc; border-radius: 4px; padding: 15px; background: #fafafa; }
        .box-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #555; margin-bottom: 8px; }
        .value { font-size: 22px; font-weight: bold; color: #111; }
        .value.green { color: #059669; }
        .value.red { color: #dc2626; }
        .result-box { background: #111; color: #fff; padding: 15px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .result-box .title { font-size: 14px; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; }
        .result-box .value { color: #ffc20c; font-size: 28px; }
        .section-title { font-size: 14px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #111; padding-bottom: 5px; margin: 30px 0 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        th, td { border: 1px solid #eee; padding: 8px 10px; text-align: left; }
        th { background: #f4f4f4; text-transform: uppercase; color: #333; border-bottom: 2px solid #ddd; }
        td.money { text-align: right; font-weight: bold; }
        tr:nth-child(even) { background-color: #fafafa; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
        .badge.green { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge.red { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .footer { text-align: center; font-size: 10px; color: #999; margin-top: 40px; border-top: 1px solid #eee; padding-top: 10px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
        .btn-print { display: block; width: 100%; padding: 15px; background: #111; color: #ffc20c; text-align: center; text-decoration: none; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; border-radius: 5px; cursor: pointer; border: none; }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn-print no-print">🖨️ Imprimir ou Salvar PDF</button>

    <div class="header">
        <h1>DRE - Demonstração de Resultado</h1>
        <div class="header-info"><div><strong>Período:</strong> {{ $periodoFormatado }}</div><div><strong>Filtro:</strong> {{ $clienteNome }}</div></div>
    </div>

    <div class="grid">
        <div class="box"><div class="box-title">Total de Entradas (Receitas Pagas)</div><div class="value green">+ R$ {{ number_format($receitasPagas, 2, ',', '.') }}</div></div>
        <div class="box"><div class="box-title">Total de Saídas (Despesas Pagas)</div><div class="value red">- R$ {{ number_format($despesasPagas, 2, ',', '.') }}</div></div>
    </div>

    <div class="result-box"><div class="title">Resultado Líquido do Período (Caixa Realizado)</div><div class="value">R$ {{ number_format($lucroLiquido, 2, ',', '.') }}</div></div>

    <div class="section-title">Análise por Centro de Custos</div>
    <div class="grid" style="align-items: flex-start; gap: 20px;">
        <div style="flex: 1;">
            <table>
                <thead><tr><th colspan="2" style="background: #ecfdf5; color: #065f46;">ENTRADAS POR CATEGORIA</th></tr></thead>
                <tbody>
                    @php $temReceita = false; @endphp
                    @foreach($receitasPorCategoria as $chave => $cat)
                        @if($cat['total'] > 0)
                            @php $temReceita = true; @endphp
                            <tr>
                                <td style="font-weight: bold;">{{ $cat['nome'] }}</td>
                                <td class="money" style="color: #059669; width: 35%;">R$ {{ number_format($cat['total'], 2, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                    @if(!$temReceita)
                        <tr><td colspan="2" style="text-align: center; color: #999;">Sem receitas categorizadas no período.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div style="flex: 1;">
            <table>
                <thead><tr><th colspan="2" style="background: #fef2f2; color: #991b1b;">SAÍDAS POR CATEGORIA</th></tr></thead>
                <tbody>
                    @php $temDespesa = false; @endphp
                    @foreach($despesasPorCategoria as $chave => $cat)
                        @if($cat['total'] > 0)
                            @php $temDespesa = true; @endphp
                            <tr>
                                <td style="font-weight: bold;">{{ $cat['nome'] }}</td>
                                <td class="money" style="color: #dc2626; width: 35%;">R$ {{ number_format($cat['total'], 2, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                    @if(!$temDespesa)
                        <tr><td colspan="2" style="text-align: center; color: #999;">Sem despesas categorizadas no período.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-title">Resumo Operacional & Risco</div>
    <table>
        <tr><th style="width: 25%">OS Confirmadas</th><td style="width: 25%">{{ $totalOsConfirmadas }} contratos</td><th style="width: 25%">Volume Financeiro Operado</th><td style="width: 25%" class="money">R$ {{ number_format($valorOsConfirmadas, 2, ',', '.') }}</td></tr>
        <tr><th>Pendente a Receber</th><td style="color: #b45309; font-weight:bold;">R$ {{ number_format($receitasPendentes, 2, ',', '.') }}</td><th>Pendente a Pagar</th><td style="color: #dc2626; font-weight:bold;">R$ {{ number_format($despesasPendentes, 2, ',', '.') }}</td></tr>
    </table>

    <div class="section-title">Detalhamento Analítico: Entradas (Receitas)</div>
    <table>
        <thead><tr><th style="width: 12%">Data Pgto.</th><th style="width: 48%">Descrição / Categoria</th><th style="width: 25%">Cliente / Origem</th><th style="width: 15%" class="money">Valor (R$)</th></tr></thead>
        <tbody>
            @forelse($listaReceitas as $receita)
                <tr>
                    <td>{{ $receita->data_pagamento ? \Carbon\Carbon::parse($receita->data_pagamento)->format('d/m/Y') : '-' }}</td>
                    <td>
                        <span style="font-weight: bold;">{{ $receita->descricao }}</span><br>
                        <span class="badge green">{{ $categoriasReceita[$receita->categoria_chave] ?? 'Outros' }}</span>
                    </td>
                    <td>{{ ($receita->pedido && $receita->pedido->cliente) ? $receita->pedido->cliente->nome : 'Avulso/Caixa' }}</td>
                    <td class="money" style="color: #059669;">{{ number_format($receita->valor, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; color: #777;">Nenhuma entrada registrada neste período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Detalhamento Analítico: Saídas (Despesas)</div>
    <table>
        <thead><tr><th style="width: 12%">Data Pgto.</th><th style="width: 48%">Descrição / Categoria</th><th style="width: 25%">Vinculado a</th><th style="width: 15%" class="money">Valor (R$)</th></tr></thead>
        <tbody>
            @forelse($listaDespesas as $despesa)
                <tr>
                    <td>{{ $despesa->data_pagamento ? \Carbon\Carbon::parse($despesa->data_pagamento)->format('d/m/Y') : '-' }}</td>
                    <td>
                        <span style="font-weight: bold;">{{ $despesa->descricao }}</span><br>
                        <span class="badge red">{{ $categoriasDespesa[$despesa->categoria_chave] ?? 'Outros' }}</span>
                    </td>
                    <td>Custo Fixo / Operacional</td>
                    <td class="money" style="color: #dc2626;">{{ number_format($despesa->valor, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; color: #777;">Nenhuma saída registrada neste período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Documento gerado em {{ now()->format('d/m/Y \à\s H:i:s') }} pelo sistema de inteligência Mesa Posta ERP.</div>
</body>
</html>