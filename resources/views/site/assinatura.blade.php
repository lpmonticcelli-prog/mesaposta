<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Assinatura Eletrônica - {{ mb_strtoupper($empresaNome) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <style>
        body { background-color: #f3f4f6; touch-action: pan-y; }
        .canvas-container { border: 2px dashed #cbd5e1; border-radius: 8px; background: #fff; overflow: hidden; touch-action: none; position: relative; }
        canvas { width: 100%; height: 250px; cursor: crosshair; }
    </style>
</head>
<body class="text-gray-800 antialiased font-sans pb-10">

    <div class="max-w-2xl mx-auto md:mt-6 bg-white shadow-xl md:rounded-xl overflow-hidden border-t-8 {{ $pedido->tipo === 'cobranca' ? 'border-red-600' : 'border-brand-gold' }}">
        
        <div class="bg-gray-900 {{ $pedido->tipo === 'cobranca' ? 'text-red-500' : 'text-brand-gold' }} p-6 text-center border-b border-gray-800">
            @if(!empty($empresaLogo))
                <img src="{{ asset($empresaLogo) }}" class="mx-auto max-h-12 mb-3 object-contain">
            @else
                <h2 class="font-black text-2xl tracking-widest uppercase mb-2 text-brand-gold">{{ mb_strtoupper($empresaNome) }}</h2>
            @endif
            <h1 class="font-black text-xl uppercase tracking-widest">{{ $pedido->tipo === 'cobranca' ? 'Confissão de Dívida (Avarias)' : 'Contrato de Locação' }}</h1>
            <p class="text-[10px] font-bold mt-1 text-gray-400 uppercase tracking-widest">OS #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }} • {{ mb_strtoupper($empresaNome) }}</p>
        </div>

        <div class="p-6 md:p-8">
            @if(session('success') || $pedido->assinatura_data)
                <div class="bg-gray-50 border border-gray-200 p-8 rounded-xl text-center shadow-inner relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-green-500"></div>
                    <div class="text-6xl mb-4">🔒</div>
                    <h2 class="text-gray-800 font-black text-2xl uppercase tracking-widest mb-2">Link Expirado</h2>
                    <p class="text-green-700 text-xs font-black uppercase tracking-widest bg-green-100 py-1 px-3 inline-block rounded mb-4">✅ Contrato Assinado e Selado</p>
                    
                    <p class="text-gray-600 text-sm font-medium leading-relaxed mb-6">
                        Por questões de segurança da informação e LGPD, este formulário foi desativado para novas edições e o documento possui validade jurídica.
                    </p>

                    <div class="bg-white border border-gray-300 p-4 rounded-lg inline-block text-left mx-auto w-full shadow-sm">
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-3 text-center border-b pb-2">Auditoria Jurídica Oficial</p>
                        <img src="{{ $pedido->assinatura_img }}" class="mx-auto max-h-24 mb-4 border-b border-gray-100 pb-2">
                        <div class="space-y-1">
                            <p class="text-[10px] text-gray-600 font-bold flex justify-between"><span>Autenticação:</span> <span>{{ \Carbon\Carbon::parse($pedido->assinatura_data)->format('d/m/Y \à\s H:i:s') }}</span></p>
                            <p class="text-[10px] text-gray-600 font-bold flex justify-between"><span>IP Rastreado:</span> <span>{{ $pedido->assinatura_ip }}</span></p>
                            <p class="text-[10px] text-gray-600 font-bold flex justify-between"><span>CPF Declarado:</span> <span>{{ $pedido->assinatura_cpf }}</span></p>
                        </div>
                    </div>
                </div>
            @else
                
                @if(session('error')) <div class="bg-red-100 text-red-700 p-4 rounded mb-6 font-bold text-sm text-center border border-red-300">{{ session('error') }}</div> @endif

                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-sm mb-6">
                    <p class="mb-2"><strong>Locatário Responsável:</strong><br> <span class="text-lg font-black text-gray-900">{{ $pedido->cliente->nome }}</span></p>
                    <p class="mb-2"><strong>Data do Evento:</strong> <span class="text-red-600 font-bold">{{ \Carbon\Carbon::parse($pedido->data_evento)->format('d/m/Y') }}</span></p>
                    <p><strong>Valor Total Documentado:</strong> <span class="text-green-600 font-black">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</span></p>
                </div>

                <div class="mb-6">
                    <h3 class="font-black text-sm uppercase tracking-widest border-b-2 border-black pb-2 mb-3">Relação de Materiais</h3>
                    <ul class="text-xs space-y-2">
                        @foreach($pedido->itens as $item)
                            <li class="flex justify-between items-center bg-gray-50 p-3 border border-gray-100 rounded">
                                <div>
                                    <span class="font-bold uppercase">{{ $item->produto->nome }}</span><br>
                                    @if($pedido->tipo === 'cobranca')
                                        <span class="text-[9px] text-red-600 font-bold uppercase tracking-widest">Cobrança de Reposição</span>
                                    @else
                                        <span class="text-[9px] text-gray-500 font-bold uppercase tracking-widest">Multa de Reposição: R$ {{ number_format($item->produto->valor_reposicao ?? 0, 2, ',', '.') }}/un</span>
                                    @endif
                                </div>
                                <span class="font-black bg-gray-200 text-gray-800 px-3 py-1 rounded">{{ $item->quantidade_pedida }}x</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-yellow-50 p-5 rounded-lg border border-yellow-200 text-xs text-yellow-900 font-medium leading-relaxed text-justify mb-8 shadow-inner">
                    <strong class="uppercase text-yellow-800 block mb-2 font-black border-b border-yellow-200 pb-2">Cláusula de Responsabilidade e Aceite</strong>
                    @if($pedido->tipo === 'cobranca')
                        Ao assinar eletronicamente este termo, declaro estar ciente e assumo a responsabilidade pelas avarias/quebras listadas acima. Comprometo-me a realizar o pagamento do valor de reposição estipulado de forma imediata à <strong>{{ mb_strtoupper($empresaNome) }}</strong>.
                    @else
                        Ao assinar eletronicamente este termo, assumo total responsabilidade pela guarda e conservação dos materiais locados. Comprometo-me a <strong>RESSARCIR FINANCEIRAMENTE a {{ mb_strtoupper($empresaNome) }}</strong>, pagando o valor integral de reposição estipulado acima, em caso de avaria, quebra, trinca ou perda.
                    @endif
                    <br><br>
                    Estou ciente de que esta assinatura possui <strong>Validade Jurídica Integral</strong> amparada na MP nº 2.200-2/2001 e Lei 14.063/2020.
                </div>

                <form id="form-assinatura" method="POST" action="{{ route('site.assinatura.store', $token) }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="assinatura_base64" id="assinatura_base64">
                    
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase mb-2">Confirme o CPF/CNPJ do Assinante</label>
                        <input type="text" name="cpf_assinante" value="{{ $pedido->cliente->cpf_cnpj }}" required class="w-full bg-white border border-gray-300 p-4 rounded-md shadow-sm font-bold text-sm focus:border-brand-gold focus:ring-1 focus:ring-brand-gold">
                    </div>

                    <label class="flex items-start space-x-3 bg-white p-4 border border-gray-300 rounded-md cursor-pointer shadow-sm hover:bg-gray-50">
                        <input type="checkbox" name="termos" required class="mt-1 w-5 h-5 text-brand-gold rounded border-gray-300 focus:ring-brand-gold focus:border-brand-gold">
                        <span class="text-xs font-bold text-gray-700">Li e concordo com os Termos de Responsabilidade descritos em favor da {{ mb_strtoupper($empresaNome) }}.</span>
                    </label>

                    <div class="pt-4">
                        <div class="flex justify-between items-end mb-2">
                            <label class="block text-[11px] font-black text-gray-800 uppercase tracking-widest">Assine no quadro abaixo (Use o Dedo)</label>
                            <button type="button" id="limpar" class="text-[10px] text-red-500 font-bold uppercase underline tracking-widest">Apagar e Refazer</button>
                        </div>
                        <div class="canvas-container shadow-inner mb-4">
                            <canvas id="signature-pad"></canvas>
                        </div>
                    </div>

                    <button type="submit" id="btn-salvar" class="w-full py-5 bg-gray-900 text-brand-gold font-black rounded-lg shadow-xl uppercase tracking-widest text-lg hover:bg-black transition-colors border-b-4 border-gray-700">
                        ✍️ Lacrar Documento
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(!$pedido->assinatura_data)
    <script>
        const canvas = document.getElementById('signature-pad');
        function resizeCanvas() {
            const ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = 250;
            canvas.getContext("2d").scale(ratio, ratio);
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        const signaturePad = new SignaturePad(canvas, { penColor: "rgb(17, 17, 17)", minWidth: 2, maxWidth: 4 });

        document.getElementById('limpar').addEventListener('click', function () { signaturePad.clear(); });

        document.getElementById('btn-salvar').addEventListener('click', function (e) {
            const checkbox = document.querySelector('input[name="termos"]');
            if (!checkbox.checked) { alert("ATENÇÃO: Você precisa marcar a caixinha aceitando os Termos."); return; }
            if (signaturePad.isEmpty()) { alert("ATENÇÃO: Por favor, desenhe sua assinatura no quadro em branco."); return; }
            
            document.getElementById('assinatura_base64').value = signaturePad.toDataURL('image/png');
            this.innerText = "Registrando Validade Jurídica...";
            this.classList.replace('bg-gray-900', 'bg-gray-500');
            this.classList.replace('text-brand-gold', 'text-white');
            this.disabled = true;
            document.getElementById('form-assinatura').submit();
        });
    </script>
    @endif
</body>
</html>