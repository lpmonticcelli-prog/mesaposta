<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>OS #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }} - {{ mb_strtoupper($empresaNome ?? 'MESA POSTA') }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; border-bottom: 3px solid #ffc20c; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 20px; color: #111; text-transform: uppercase; letter-spacing: 1px;}
        .header h3 { margin: 5px 0 0 0; font-size: 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: middle; }
        th { background-color: #f4f4f4; font-size: 9px; text-transform: uppercase; color: #333; }
        .address-box { border: 2px solid #111; padding: 12px; margin-bottom: 20px; border-radius: 4px; background-color: #fafafa; }
        .address-title { font-weight: bold; text-transform: uppercase; font-size: 11px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; color: #111;}
        .total-box { text-align: right; background-color: #111; color: #ffc20c; padding: 10px; margin-bottom: 20px;}
        .total-line { font-size: 12px; margin-bottom: 5px; color: #fff;}
        .total-final { font-size: 16px; font-weight: bold; margin-top: 5px; border-top: 1px solid #555; padding-top: 5px;}
        .checkbox { width: 20px; text-align: center; font-family: monospace; font-size: 14px; }
        
        .foto-avaria-container { margin-top: 10px; padding: 8px; border: 1px dashed #d93025; background-color: #fdf2f2; border-radius: 4px; display: inline-block; }
        .foto-avaria-title { font-size: 9px; font-weight: bold; color: #d93025; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
        
        .qr-logistica { text-align: center; margin-top: 30px; page-break-inside: avoid; border: 2px dashed #ccc; padding: 15px; border-radius: 8px; background: #fff;}
        .pix-box { border: 2px dashed #059669; padding: 15px; background-color: #f0fdf4; text-align: center; page-break-inside: avoid; margin-top: 20px; border-radius: 8px;}
        .pix-title { font-size: 16px; font-weight: bold; color: #059669; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 1px;}
        .pix-payload { font-size: 9px; font-family: monospace; background: #fff; padding: 10px; border: 1px solid #ccc; word-break: break-all; margin-top: 10px; color: #333; text-align: left; border-radius: 4px;}
        
        .header-multa { background-color: #d93025; color: #fff; padding: 10px; text-align: center; font-weight: bold; margin-bottom: 20px; font-size: 14px; text-transform: uppercase;}
    </style>
</head>
<body>

    <div class="header">
        @php
            $logoPdf = null;
            if(!empty($empresaLogo)) {
                $caminhoFisico = storage_path('app/public/' . str_replace('storage/', '', $empresaLogo));
                if(file_exists($caminhoFisico)) {
                    $logoPdf = 'data:image/' . pathinfo($caminhoFisico, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($caminhoFisico));
                }
            }
        @endphp
        @if($logoPdf) <img src="{{ $logoPdf }}" style="max-height: 50px; margin-bottom: 10px;"> @endif
        
        @if($via === 'galpao')
            <h2 style="color: #d93025;">📦 DOCUMENTO DE SEPARAÇÃO E LOGÍSTICA (CEGO)</h2>
        @else
            <h2>{{ mb_strtoupper($empresaNome ?? 'MESA POSTA') }}</h2>
            <h3>CONTRATO DE LOCAÇÃO E PRESTAÇÃO DE SERVIÇOS #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</h3>
        @endif
    </div>

    @if($pedido->tipo === 'cobranca')
        <div class="header-multa">
            ⚠️ LAUDO DE COBRANÇA E AVARIAS LOGÍSTICAS ⚠️<br>
            Referente ao Contrato Original: #{{ str_pad($pedido->pedido_original_id, 5, '0', STR_PAD_LEFT) }}
        </div>
    @endif

    <table>
        <tr>
            <th style="width: 15%;">Cliente:</th>
            <td style="width: 35%;"><strong>{{ $pedido->cliente->nome }}</strong></td>
            <th style="width: 20%;">Data Saída (Entrega):</th>
            <td style="width: 30%; font-weight: bold; color: #1d4ed8;">{{ $pedido->data_entrega ? $pedido->data_entrega->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <th>WhatsApp:</th>
            <td>{{ $pedido->cliente->telefone }}</td>
            <th>Data Retorno (Devolução):</th>
            <td style="font-weight: bold; color: #d93025;">{{ $pedido->data_devolucao ? $pedido->data_devolucao->format('d/m/Y') : '-' }}</td>
        </tr>
        @if($via === 'cliente')
        <tr>
            <th>Pagamento:</th>
            <td><strong>{{ $pedido->forma_pagamento }}</strong></td>
            <th>Data da Festa (Evento):</th>
            <td style="font-weight: bold;">{{ $pedido->data_evento ? $pedido->data_evento->format('d/m/Y') : '-' }}</td>
        </tr>
        @endif
    </table>

    <div class="address-box">
        <div class="address-title">📍 Local do Evento / Entrega</div>
        @if($pedido->endereco_entrega)
            <p style="margin:0; line-height: 1.5; font-size: 12px;">
                {{ $pedido->endereco_entrega }}, Nº {{ $pedido->numero_entrega ?? 'S/N' }} 
                @if($pedido->complemento_entrega) - {{ $pedido->complemento_entrega }} @endif <br>
                Bairro: {{ $pedido->bairro_entrega }} | {{ $pedido->cidade_entrega }}/{{ $pedido->estado_entrega }}
            </p>
        @else
            <p style="margin:0; color:#777;"><em>Retirada pelo cliente no galpão.</em></p>
        @endif
        
        @if($pedido->observacoes)
            <div class="address-title" style="margin-top: 10px;">⚠️ Anotações Logísticas e Laudos:</div>
            <p style="margin:0; font-size: 10px; color: #d93025; font-weight: bold; white-space: pre-wrap;">{{ $pedido->observacoes }}</p>
        @endif
    </div>

    @if($via === 'galpao')
        <table>
            <thead>
                <tr>
                    <th class="checkbox">CK</th>
                    <th style="width: 15%; text-align: center;">QTD</th>
                    <th style="width: 80%;">MATERIAL FÍSICO PARA SEPARAÇÃO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->itens as $item)
                    @if($item->produto && $item->produto->is_kit && $item->produto->componentesKit->count() > 0)
                        <tr style="background-color: #f8f9fa;">
                            <td class="checkbox">[  ]</td>
                            <td style="text-align: center; font-weight: bold; font-size: 14px;">{{ $item->quantidade_pedida }}</td>
                            <td><strong>📦 CONJUNTO: {{ $item->produto->nome }}</strong></td>
                        </tr>
                        @foreach($item->produto->componentesKit as $comp)
                            <tr>
                                <td class="checkbox" style="color: #d93025;">↳ [  ]</td>
                                <td style="text-align: center; font-weight: bold; font-size: 14px; color: #d93025;">{{ $item->quantidade_pedida * $comp->quantidade }}</td>
                                <td style="color: #555; text-transform: uppercase;">(Separar Físico) - {{ $comp->produtoAvulso->nome ?? 'Excluído' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="checkbox">[  ]</td>
                            <td style="text-align: center; font-weight: bold; font-size: 14px;">{{ $item->quantidade_pedida }}</td>
                            <td style="text-transform: uppercase;"><strong>{{ $item->produto->nome ?? 'Excluído' }}</strong></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        
        <div style="text-align: center; padding: 20px; border: 2px dashed #ccc; margin-top: 20px; page-break-inside: avoid;">
            <h3>ASSINATURA DO CONFERENTE DE GALPÃO</h3>
            <div style="border-bottom: 1px solid #111; width: 60%; margin: 40px auto 5px auto;"></div>
        </div>

        @if(!empty($qrCodeLogistica) && $pedido->tipo !== 'cobranca')
            <div class="qr-logistica">
                <img src="data:image/svg+xml;base64,{!! $qrCodeLogistica !!}" alt="QR Code Logística" width="120">
                <p style="font-size: 11px; font-weight: bold; margin-top: 8px; color: #d93025;">📸 OPERADOR: ESCANEIE PARA ABRIR A TELA DE CONFERÊNCIA</p>
            </div>
        @endif

    @else
        @php $somaDescontos = 0; $somaBruta = 0; @endphp
        <table>
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">QTD</th>
                    <th style="width: 45%;">MATERIAL LOCADO / LAUDO</th>
                    <th style="width: 15%; text-align: right;">MULTA (UN)</th>
                    <th style="width: 15%; text-align: right;">DIÁRIA (R$)</th>
                    <th style="width: 20%; text-align: right;">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->itens as $item)
                @php 
                    $brutoItem = $item->quantidade_pedida * $item->valor_unitario;
                    $somaBruta += $brutoItem; 
                    $somaDescontos += $item->desconto;
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $item->quantidade_pedida }}</td>
                    <td style="text-transform: uppercase;">
                        <strong>{{ $item->produto->nome ?? 'Excluído' }}</strong>
                        
                        @php
                            $fotoAvariaPath = $item->foto_avaria;
                            if (!$fotoAvariaPath) {
                                $pedidoCobrancaFilho = \App\Models\Pedido::where('pedido_original_id', $pedido->id)->where('tipo', 'cobranca')->first();
                                if ($pedidoCobrancaFilho) {
                                    $itemCobranca = \App\Models\PedidoItem::where('pedido_id', $pedidoCobrancaFilho->id)->where('produto_id', $item->produto_id)->whereNotNull('foto_avaria')->first();
                                    if ($itemCobranca) $fotoAvariaPath = $itemCobranca->foto_avaria;
                                }
                            }

                            $fotoBase64 = null;
                            if ($fotoAvariaPath) {
                                $pathStorage = storage_path('app/public/' . $fotoAvariaPath);
                                $pathPublic = public_path('storage/' . $fotoAvariaPath);
                                $caminhoFinal = file_exists($pathStorage) ? $pathStorage : (file_exists($pathPublic) ? $pathPublic : null);
                                if ($caminhoFinal) $fotoBase64 = 'data:image/' . pathinfo($caminhoFinal, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($caminhoFinal));
                            }
                        @endphp

                        @if($fotoBase64)
                            <br>
                            <div class="foto-avaria-container">
                                <div class="foto-avaria-title">📷 Evidência de Quebra / Avaria:</div>
                                <img src="{{ $fotoBase64 }}" style="max-height: 150px; max-width: 150px; border: 1px solid #ddd; display: block; margin-top: 4px;">
                            </div>
                        @endif
                    </td>
                    <td style="text-align: right; font-size: 10px; color: #d93025; font-weight: bold;">R$ {{ number_format($item->valor_reposicao, 2, ',', '.') }}</td>
                    <td style="text-align: right;">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">
                        @if($item->desconto > 0) <span style="font-size: 9px; color: #d93025; display: block;">(- R$ {{ number_format($item->desconto, 2, ',', '.') }})</span> @endif
                        R$ {{ number_format($item->subtotal, 2, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-box" style="page-break-inside: avoid;">
            <div class="total-line">Soma dos Itens: R$ {{ number_format($somaBruta, 2, ',', '.') }}</div>
            @if($somaDescontos > 0) <div class="total-line" style="color: #ef4444;">Desconto Concedido: - R$ {{ number_format($somaDescontos, 2, ',', '.') }}</div> @endif
            <div class="total-final">TOTAL LÍQUIDO A PAGAR: R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</div>
        </div>

        @if(!empty($qrCodePix))
            <div class="pix-box">
                <div class="pix-title">Pague com PIX (Sem Taxas)</div>
                <img src="data:image/svg+xml;base64,{!! $qrCodePix !!}" alt="QR Code PIX" width="130">
                <div style="font-size: 11px; font-weight: bold; margin-top: 10px; color: #059669;">Abra o app do seu banco e escaneie o código acima.</div>
                
                <div class="pix-payload">
                    <strong style="color:#059669;">PIX Copia e Cola:</strong><br>
                    {{ $pixPayload }}
                </div>
            </div>
        @endif

        {{-- 🛡️ MOTOR DE HERANÇA DE ASSINATURA --}}
        @php
            $assinaturaRef = $pedido;
            $avisoHeranca = false;
            
            // Se for um laudo de multa, o sistema procura a assinatura na OS original
            if ($pedido->tipo === 'cobranca' && $pedido->pedido_original_id) {
                $original = \App\Models\Pedido::find($pedido->pedido_original_id);
                if ($original && $original->assinatura_data) {
                    $assinaturaRef = $original;
                    $avisoHeranca = true;
                }
            }
        @endphp

        @if($assinaturaRef->assinatura_data)
            <div style="margin-top: 20px; border: 2px solid #111; padding: 15px; background-color: #fafafa; page-break-inside: avoid;">
                <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px;">
                    Termo de Responsabilidade e Reparação Civil
                    @if($avisoHeranca) <br><span style="color: #d93025; font-size: 9px;">(Assinatura validada e herdada do Contrato Original)</span> @endif
                </div>
                <p style="font-size: 9px; line-height: 1.4; color: #333; margin: 0 0 10px 0; text-align: justify;">
                    O LOCATÁRIO assume total responsabilidade pela guarda dos materiais. Em caso de avaria ou quebra, compromete-se a pagar imediatamente o valor integral estipulado na coluna <strong>"MULTA (UN)"</strong> acima. Documento com validade jurídica (MP nº 2.200-2/2001 e Lei 14.063/2020).
                </p>
                <table style="width: 100%; border: none; margin-bottom: 0;">
                    <tr>
                        <td style="width: 50%; border: none; text-align: center; vertical-align: bottom;">
                            <img src="{{ $assinaturaRef->assinatura_img }}" style="max-height: 80px; max-width: 250px; border-bottom: 1px solid #111;"><br>
                            <strong style="font-size: 11px;">{{ $assinaturaRef->cliente->nome }}</strong><br>
                            <span style="font-size: 9px;">CPF Assinante: {{ $assinaturaRef->assinatura_cpf }}</span>
                        </td>
                        <td style="width: 50%; border: none; vertical-align: bottom; text-align: right;">
                            <div style="border: 1px dashed #777; padding: 10px; text-align: left; background: #fff; display: inline-block;">
                                <div style="font-size: 9px; font-weight: bold; margin-bottom: 5px;">🛡️ Selo de Autenticidade Digital</div>
                                <div style="font-size: 8px; color: #555; line-height: 1.5;">
                                    <strong>Data/Hora:</strong> {{ \Carbon\Carbon::parse($assinaturaRef->assinatura_data)->format('d/m/Y \à\s H:i:s') }}<br>
                                    <strong>IP Origem:</strong> {{ $assinaturaRef->assinatura_ip }}<br>
                                    <strong>Token Único:</strong> <span style="font-family: monospace;">{{ $assinaturaRef->token_assinatura }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        @else
            @if($pedido->tipo !== 'cobranca')
            <div style="margin-top: 30px; border: 2px solid #111; padding: 15px; page-break-inside: avoid;">
                <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 15px;">Assinatura Física</div>
                <table style="width: 100%; border: none; margin-bottom: 0;">
                    <tr>
                        <td style="width: 50%; border: none; text-align: center; vertical-align: bottom;">
                            <div style="border-bottom: 1px solid #111; width: 85%; margin: 0 auto 5px auto; height: 40px;"></div>
                            <strong style="font-size: 11px; text-transform: uppercase;">{{ $pedido->cliente->nome }}</strong><br>
                            <span style="font-size: 9px; color: #555;">Locatário (Ciente da Tabela de Multas)</span>
                        </td>
                        <td style="width: 50%; border: none; text-align: center; vertical-align: bottom;">
                            <div style="border-bottom: 1px solid #111; width: 85%; margin: 0 auto 5px auto; height: 40px;"></div>
                            <strong style="font-size: 11px; text-transform: uppercase;">{{ mb_strtoupper($empresaNome ?? 'MESA POSTA') }}</strong><br>
                            <span style="font-size: 9px; color: #555;">Locador</span>
                        </td>
                    </tr>
                </table>
            </div>
            @endif
        @endif
    @endif

</body>
</html>