<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Assinatura Eletrônica - {{ mb_strtoupper($empresaNome ?? 'MESA POSTA') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <style>
        body { background-color: #f3f4f6; touch-action: pan-y; }
        .canvas-container { border: 2px dashed #cbd5e1; border-radius: 8px; background: #fff; overflow: hidden; touch-action: none; position: relative; }
        canvas { width: 100%; height: 250px; cursor: crosshair; }
        /* Scroll customizado para a caixa de contrato */
        .contrato-scroll::-webkit-scrollbar { width: 6px; }
        .contrato-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="text-gray-800 antialiased font-sans pb-10">

    @php
        // MATEMÁTICA DA PROMISSÓRIA: Calcula o Risco Total do Acervo Locado
        $valorPromissoria = 0;
        if($pedido->tipo !== 'cobranca') {
            $valorPromissoria = $pedido->itens->sum(function($item) {
                return $item->quantidade_pedida * ($item->produto->valor_reposicao ?? 0);
            });
        }
    @endphp

    <div class="max-w-2xl mx-auto md:mt-6 bg-white shadow-xl md:rounded-xl overflow-hidden border-t-8 {{ $pedido->tipo === 'cobranca' ? 'border-red-600' : 'border-[#ffc20c]' }}">
        
        <div class="bg-gray-900 {{ $pedido->tipo === 'cobranca' ? 'text-red-500' : 'text-[#ffc20c]' }} p-6 text-center border-b border-gray-800">
            @if(!empty($empresaLogo))
                <img src="{{ asset($empresaLogo) }}" class="mx-auto max-h-12 mb-3 object-contain">
            @else
                <h2 class="font-black text-2xl tracking-widest uppercase mb-2 text-[#ffc20c]">{{ mb_strtoupper($empresaNome ?? 'MESA POSTA') }}</h2>
            @endif
            <h1 class="font-black text-xl uppercase tracking-widest">{{ $pedido->tipo === 'cobranca' ? 'Confissão de Dívida (Avarias)' : 'Contrato de Locação e Promissória' }}</h1>
            <p class="text-[10px] font-bold mt-1 text-gray-400 uppercase tracking-widest">OS #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }} • {{ mb_strtoupper($empresaNome ?? 'MESA POSTA') }}</p>
        </div>

        <div class="p-6 md:p-8">
            
            <div class="mb-8 text-center">
                <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-3">Antes de assinar, leia atentamente o documento oficial:</p>
                <a href="{{ URL::signedRoute('publico.pedido.imprimir', ['pedido' => $pedido->id]) }}" target="_blank" class="inline-flex items-center justify-center w-full md:w-auto px-8 py-4 bg-[#ffc20c] text-gray-900 font-black rounded-lg shadow-lg hover:bg-yellow-500 hover:scale-105 transition-all uppercase tracking-widest text-sm border-b-4 border-yellow-600">
                    <span class="text-2xl mr-3">📄</span> VISUALIZAR CONTRATO EM PDF
                </a>
            </div>

            @if(session('success') || $pedido->assinatura_data)
                <div class="bg-gray-50 border border-gray-200 p-8 rounded-xl text-center shadow-inner relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-green-500"></div>
                    <div class="text-6xl mb-4">🔒</div>
                    <h2 class="text-gray-800 font-black text-2xl uppercase tracking-widest mb-2">Link Expirado</h2>
                    <p class="text-green-700 text-xs font-black uppercase tracking-widest bg-green-100 py-1 px-3 inline-block rounded mb-4">✅ Contrato Assinado e Selado</p>
                    <p class="text-gray-600 text-sm font-medium leading-relaxed mb-6">Este formulário foi desativado por segurança. O documento já possui validade jurídica.</p>

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

                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-sm mb-6 flex flex-col md:flex-row justify-between">
                    <div>
                        <p class="mb-2 text-xs text-gray-500 uppercase tracking-widest font-bold">Locatário Responsável</p>
                        <p class="text-lg font-black text-gray-900">{{ $pedido->cliente->nome }}</p>
                    </div>
                    <div class="mt-4 md:mt-0 text-left md:text-right">
                        <p class="mb-1"><strong>Data do Evento:</strong> <span class="text-red-600 font-bold">{{ $pedido->data_evento ? \Carbon\Carbon::parse($pedido->data_evento)->format('d/m/Y') : '-' }}</span></p>
                        <p><strong>Locação:</strong> <span class="text-green-600 font-black">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</span></p>
                    </div>
                </div>

                {{-- ========================================================================= --}}
                {{-- 📜 BLOCO 1: TERMOS DO CONTRATO --}}
                {{-- ========================================================================= --}}
                <div class="mb-6">
                    <h3 class="font-black text-sm uppercase tracking-widest text-gray-800 mb-2">Cláusulas e Termos de Locação</h3>
                    <div class="bg-white border border-gray-300 rounded-lg p-4 h-48 overflow-y-auto contrato-scroll text-xs text-gray-600 leading-relaxed text-justify shadow-inner">
                        <strong class="text-red-600 uppercase mb-2 block">LEIA COM ATENÇÃO:</strong>
                        <ol class="list-decimal pl-4 space-y-2">
                            <li>Em casos de a entrega ser de responsabilidade da {{ mb_strtoupper($empresaNome ?? 'Empresa') }}, e o LOCATÁRIO ou responsável por ele indicado não estiver presente para receber, ele deverá fazer a retirada do pedido sem a responsabilidade do LOCADOR reembolsar o valor de frete.</li>
                            <li>É imprescindível a presença do LOCATÁRIO ou responsável por ele indicado no momento da retirada/entrega para conferência dos materiais, devendo comunicar imediatamente sobre qualquer irregularidade observada. Não serão aceitas reclamações posteriores.</li>
                            <li>A responsabilidade será integralmente do LOCATÁRIO em casos de ausência e/ou recusa na conferência dos materiais no ato da retirada.</li>
                            <li>Os Materiais alugados deverão ser devolvidos na data acordada em contrato, sob pena de cobrança de um novo aluguel por dia de atraso.</li>
                            <li>Os materiais deverão ser devolvidos limpos, embalados e armazenados nas mesmas condições do ato da retirada.
                                <ul class="list-[circle] pl-4 mt-1 space-y-1 text-gray-500">
                                    <li><strong>5.1</strong> Itens como toalhas e guardanapos não precisam ser lavados, pois a limpeza é de responsabilidade do LOCADOR. Apenas se atente com o armazenamento até a data de devolução (deixar os itens arejados para não causar bolor).</li>
                                    <li><strong>5.2</strong> Materiais devolvidos com resíduos de alimentos terão cobrança adicional de 50% do valor de locação.</li>
                                </ul>
                            </li>
                            <li>A partir do ato de retirada, fica sob responsabilidade do LOCATÁRIO todos os materiais locados. Deverá o LOCATÁRIO reembolsar o LOCADOR pelos itens que vierem a ser perdidos e/ou danificados.
                                <ul class="list-[circle] pl-4 mt-1 space-y-1 text-gray-500">
                                    <li><strong>6.1</strong> As faltas e avarias deverão ser pagas pelo LOCATÁRIO no ato da devolução, de acordo com o preço de reposição constante no Contrato.</li>
                                    <li><strong>6.2</strong> Em caso de ausência do LOCATÁRIO no ato da devolução, este terá o prazo de até 7 (sete) dias corridos para efetuar o pagamento.</li>
                                </ul>
                            </li>
                            <li>Na eventualidade de não cumprimento dos itens acima, o LOCATÁRIO desde já autoriza a emissão de cobrança bancária.</li>
                            <li>O saldo de material alugado poderá variar até a data da entrega, pois estamos sujeitos a quebras em locações anteriores. Nesse caso, iremos sempre propor as melhores opções para substituir as peças.</li>
                        </ol>
                        <strong class="text-gray-800 uppercase mb-2 mt-4 block">PAGAMENTO E GARANTIAS:</strong>
                        <ol class="list-decimal pl-4 space-y-2" start="9">
                            <li>O LOCATÁRIO deverá quitar o total do valor da locação no ato da contratação. Os materiais não serão reservados/liberados para contratos com saldo em aberto.</li>
                            <li>Em garantia dos bens locados, o LOCATÁRIO assina eletronicamente uma NOTA PROMISSÓRIA no valor de reposição dos respectivos bens, a qual reconhece plenamente válida e eficaz.</li>
                            <li>O LOCATÁRIO aceita e reconhece que a LOCADORA poderá protestar a nota promissória em decorrência da não devolução, perda e/ou dano dos bens locados.</li>
                            <li>No momento da devolução dos bens, a LOCADORA realizará a vistoria, e, não sendo constatado avarias e desgastes por mau uso, a NOTA PROMISSÓRIA se tornará sem efeito.</li>
                        </ol>
                        <strong class="text-gray-800 uppercase mb-2 mt-4 block">CANCELAMENTO:</strong>
                        <ol class="list-decimal pl-4 space-y-2" start="13">
                            <li>Para pedidos confirmados e cancelados até 05 (cinco) dias antes da data de retirada/entrega, será cobrada multa de 30% do valor total do contrato.</li>
                            <li>Para pedidos confirmados e cancelados até 24 horas antes da data de retirada/entrega, será cobrado o valor integral do Contrato.</li>
                        </ol>
                        <div class="mt-4 p-3 bg-gray-100 rounded font-bold text-center">CHAVE PIX: 44.551.388/0001-94</div>
                    </div>
                </div>

                {{-- ========================================================================= --}}
                {{-- 💸 BLOCO 2: NOTA PROMISSÓRIA VISUAL --}}
                {{-- ========================================================================= --}}
                @if($pedido->tipo !== 'cobranca')
                    <div class="mb-8">
                        <h3 class="font-black text-sm uppercase tracking-widest text-gray-800 mb-2">Garantia do Acervo</h3>
                        <div class="bg-yellow-50 border-2 border-yellow-400 p-5 rounded-lg shadow-sm relative overflow-hidden">
                            <div class="absolute -right-10 -top-10 opacity-10 text-9xl">📄</div>
                            <div class="flex justify-between items-start border-b border-yellow-300 pb-3 mb-3">
                                <h4 class="font-black text-lg text-yellow-900 uppercase tracking-widest">Nota Promissória</h4>
                                <div class="bg-yellow-200 text-yellow-900 px-3 py-1 rounded font-black text-lg">R$ {{ number_format($valorPromissoria, 2, ',', '.') }}</div>
                            </div>
                            <p class="text-xs text-yellow-800 leading-relaxed text-justify font-medium z-10 relative">
                                Ao assinar este documento, <strong>{{ mb_strtoupper($pedido->cliente->nome) }}</strong> (CPF/CNPJ: {{ $pedido->cliente->cpf_cnpj ?? 'Não informado' }}), reconhece e confessa ser devedor(a) da quantia de <strong>R$ {{ number_format($valorPromissoria, 2, ',', '.') }}</strong> referente ao Risco de Reposição Total do acervo locado.<br><br>
                                Esta promissória está vinculada à OS #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }} e somente será exigível/protestada em caso de quebra, perda ou não devolução dos materiais por parte do Locatário (Cláusulas 10 e 11).
                            </p>
                        </div>
                    </div>
                @endif

                <form id="form-assinatura" method="POST" action="{{ route('site.assinatura.store', $token) }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="assinatura_base64" id="assinatura_base64">
                    
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase mb-2">Confirme o CPF/CNPJ do Assinante</label>
                        <input type="text" name="cpf_assinante" value="{{ $pedido->cliente->cpf_cnpj }}" required class="w-full bg-white border border-gray-300 p-4 rounded-md shadow-sm font-bold text-sm focus:border-[#ffc20c] focus:ring-1 focus:ring-[#ffc20c]">
                    </div>

                    <label class="flex items-start space-x-3 bg-white p-4 border border-gray-300 rounded-md cursor-pointer shadow-sm hover:bg-gray-50">
                        <input type="checkbox" name="termos" required class="mt-1 w-5 h-5 text-[#ffc20c] rounded border-gray-300 focus:ring-[#ffc20c] focus:border-[#ffc20c]">
                        <span class="text-xs font-bold text-gray-700">Li e concordo com os Termos de Locação e reconheço a validade da Nota Promissória descrita acima.</span>
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

                    <button type="submit" id="btn-salvar" class="w-full py-5 bg-gray-900 text-[#ffc20c] font-black rounded-lg shadow-xl uppercase tracking-widest text-lg hover:bg-black transition-colors border-b-4 border-gray-700">
                        ✍️ Lacrar Contrato e Promissória
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
            if (!checkbox.checked) { alert("ATENÇÃO: Você precisa marcar a caixinha aceitando os Termos e a Promissória."); return; }
            if (signaturePad.isEmpty()) { alert("ATENÇÃO: Por favor, desenhe sua assinatura no quadro em branco."); return; }
            
            document.getElementById('assinatura_base64').value = signaturePad.toDataURL('image/png');
            this.innerText = "Registrando Validade Jurídica...";
            this.classList.replace('bg-gray-900', 'bg-gray-500');
            this.classList.replace('text-[#ffc20c]', 'text-white');
            this.disabled = true;
            document.getElementById('form-assinatura').submit();
        });
    </script>
    @endif
</body>
</html>