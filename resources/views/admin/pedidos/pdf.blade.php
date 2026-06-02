<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OS #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; border-bottom: 3px solid #ffc20c; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 24px; color: #111; text-transform: uppercase; letter-spacing: 2px;}
        .header h3 { margin: 5px 0 0 0; font-size: 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; font-size: 10px; text-transform: uppercase; color: #333; }
        .address-box { border: 2px solid #111; padding: 12px; margin-bottom: 20px; border-radius: 4px; background-color: #fafafa; }
        .address-title { font-weight: bold; text-transform: uppercase; font-size: 11px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; color: #111;}
        .total { text-align: right; font-size: 16px; font-weight: bold; background-color: #111; color: #ffc20c; padding: 10px; }
        .qr-container { text-align: center; margin-top: 30px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>DIP DRINKS ERP</h2>
        <h3>ORDEM DE SERVIÇO E LOGÍSTICA #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</h3>
    </div>

    <table>
        <tr>
            <th style="width: 15%;">Cliente:</th>
            <td style="width: 35%;"><strong>{{ $pedido->cliente->nome }}</strong></td>
            <th style="width: 20%;">Data do Evento:</th>
            <td style="width: 30%; font-weight: bold; color: #d93025;">{{ \Carbon\Carbon::parse($pedido->data_evento)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>WhatsApp:</th>
            <td>{{ $pedido->cliente->telefone }}</td>
            <th>Status OS:</th>
            <td><strong>{{ strtoupper($pedido->status) }}</strong></td>
        </tr>
    </table>

    <div class="address-box">
        <div class="address-title">📍 Endereço de Entrega / Local do Evento</div>
        @if($pedido->endereco_entrega)
            <p style="margin:0; line-height: 1.5; font-size: 13px;">
                <strong>Rua:</strong> {{ $pedido->endereco_entrega }}, Nº {{ $pedido->numero_entrega ?? 'S/N' }}<br>
                @if($pedido->complemento_entrega) <strong>Complemento:</strong> {{ $pedido->complemento_entrega }}<br> @endif
                <strong>Bairro:</strong> {{ $pedido->bairro_entrega }} | <strong>Cidade:</strong> {{ $pedido->cidade_entrega }} / {{ $pedido->estado_entrega }}<br>
                <strong>CEP:</strong> {{ $pedido->cep_entrega }}
            </p>
        @else
            <p style="margin:0; color:#777;"><em>A retirada será feita pelo cliente ou endereço não informado.</em></p>
        @endif
        
        @if($pedido->observacoes)
            <div class="address-title" style="margin-top: 15px;">⚠️ Anotações da Logística:</div>
            <p style="margin:0; font-size: 13px; color: #d93025;"><strong>{{ $pedido->observacoes }}</strong></p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">QTD</th>
                <th style="width: 50%;">MATERIAL (ACERVO)</th>
                <th style="width: 20%; text-align: right;">V. UNITÁRIO</th>
                <th style="width: 20%; text-align: right;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->itens as $item)
            <tr>
                <td style="text-align: center; font-weight: bold; font-size: 14px;">{{ $item->quantidade_pedida }}</td>
                <td style="text-transform: uppercase;">{{ $item->produto->nome ?? 'Item Removido do Acervo' }}</td>
                <td style="text-align: right;">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">R$ {{ number_format($item->quantidade_pedida * $item->valor_unitario, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        TOTAL DO CONTRATO: R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
    </div>

    <div class="qr-container">
        <img src="data:image/svg+xml;base64,{!! $qrCode !!}" alt="QR Code" width="130">
        <p style="font-size: 10px; font-weight: bold; margin-top: 5px;">ESCANEIE ESTE CÓDIGO NO GALPÃO PARA ABRIR A TELA DE SEPARAÇÃO (CHECKLIST)</p>
    </div>

    <div style="text-align: center; font-size: 9px; color: #777; margin-top: 40px; border-top: 1px solid #eee; padding-top: 10px;">
        Documento oficial emitido pelo Sistema DIP Drinks ERP em {{ now()->format('d/m/Y \à\s H:i') }}.
    </div>

</body>
</html>