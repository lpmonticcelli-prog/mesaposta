<x-app-layout>
    <x-slot name="header">Terminal de Logística</x-slot>

    <div class="max-w-3xl mx-auto pb-20" x-data="{ modoAvaria: false }">
        
        {{-- PAINEL DE AVISOS DE ERRO E SUCESSO --}}
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-6 rounded-lg shadow-md flex items-start gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <h4 class="text-red-800 font-black uppercase tracking-widest text-[10px]">Falha na Operação</h4>
                    <p class="text-red-700 font-bold text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-600 p-4 mb-6 rounded-lg shadow-md flex items-start gap-3">
                <span class="text-2xl">✅</span>
                <p class="text-green-800 font-bold text-sm">{{ session('success') }}</p>
            </div>
        @endif
        
        <div class="bg-brand-black rounded-t-xl p-6 text-center border-b-4 border-brand-gold shadow-lg relative overflow-hidden">
            <h2 class="text-brand-gold font-black text-2xl uppercase tracking-widest relative z-10">OS #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</h2>
            <p class="text-white font-bold mt-2 text-lg relative z-10">{{ $pedido->cliente->nome }}</p>
            <p class="text-gray-400 text-xs mt-1 uppercase tracking-widest font-black relative z-10">
                @if($pedido->status === 'confirmado' || $pedido->status === 'em_separacao') 📤 CHECK-OUT DE SAÍDA @else 📥 CHECK-IN DE RETORNO @endif
            </p>
        </div>

        <div class="bg-white p-4 sm:p-6 shadow-md rounded-b-xl border border-gray-200 space-y-6">
            
            <div class="space-y-3">
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">Checklist Físico</h3>
                @foreach($pedido->itens as $item)
                    <div id="item-row-{{ $item->produto_id }}" class="flex justify-between items-center p-4 bg-gray-50 rounded-lg border border-gray-200 shadow-sm transition-colors duration-300">
                        <div class="w-3/4">
                            <span class="text-[9px] font-black text-gray-400 uppercase block mb-0.5">CÓD: #{{ $item->produto_id }}</span>
                            <span class="font-black text-brand-dark uppercase text-sm block leading-tight">{{ $item->produto->nome ?? 'Peça Removida do Acervo' }}</span>
                        </div>
                        <div class="bg-brand-dark text-brand-gold font-black px-4 py-2 rounded-md text-lg text-center shadow-inner">
                            {{ $item->quantidade_pedida }}x
                        </div>
                    </div>
                @endforeach
            </div>

            @if($pedido->status === 'confirmado' || $pedido->status === 'em_separacao')
                <div class="pt-6 border-t border-gray-100">
                    <form action="{{ route('estoque.conferencia.processar', $pedido->id) }}" method="POST" onsubmit="tocarBip(); return confirm('Confirma a saída dos materiais para a festa?');">
                        @csrf <input type="hidden" name="acao" value="saida">
                        <button type="submit" class="w-full py-5 bg-blue-600 text-white font-black rounded-lg shadow-lg hover:bg-blue-700 uppercase tracking-widest text-sm flex justify-center items-center gap-2 transition-colors">
                            Confirmar Saída (Despachar)
                        </button>
                    </form>
                </div>

            @elseif($pedido->status === 'entregue')
                <div x-show="!modoAvaria" class="space-y-4 pt-6 border-t border-gray-100 transition-all">
                    <form action="{{ route('estoque.conferencia.processar', $pedido->id) }}" method="POST" onsubmit="tocarBip(); return confirm('Confirma que tudo voltou intacto? O estoque será liberado.');">
                        @csrf <input type="hidden" name="acao" value="retorno_intacto">
                        <button type="submit" class="w-full py-5 bg-green-600 text-white font-black rounded-lg shadow-lg hover:bg-green-700 uppercase tracking-widest text-sm flex justify-center items-center gap-2 transition-colors">
                            Tudo OK - Retornar ao Acervo
                        </button>
                    </form>
                    <button @click="modoAvaria = true" type="button" class="w-full py-5 bg-red-50 text-red-600 border border-red-200 font-black rounded-lg shadow-sm hover:bg-red-100 uppercase tracking-widest text-sm flex justify-center items-center gap-2 transition-colors mt-4">
                        Registrar Avarias / Quebras
                    </button>
                </div>

                {{-- MODO AVARIA --}}
                <div x-cloak x-show="modoAvaria" class="pt-6 border-t-4 border-red-500 transition-all">
                    <div class="bg-red-50 p-4 rounded-lg mb-6 border border-red-200">
                        <h3 class="text-red-800 font-black uppercase text-sm mb-1">Modo de Avaria Ativado</h3>
                        <p class="text-xs text-red-600 font-bold">Informe a quantidade QUEBRADA. As fotos são <strong>obrigatórias</strong> para aplicar a multa.</p>
                    </div>
                    
                    <form id="form-avarias" action="{{ route('estoque.conferencia.processar', $pedido->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="return validarEEnviar();">
                        @csrf <input type="hidden" name="acao" value="retorno_avaria">
                        @foreach($pedido->itens as $item)
                            <div class="bg-white p-4 rounded-lg border border-gray-300 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 row-avaria">
                                <div class="w-full sm:w-1/2">
                                    <p class="font-black text-brand-dark uppercase text-xs mb-0.5">{{ $item->produto->nome ?? 'Peça Removida' }}</p>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase">Lote Total Alugado: {{ $item->quantidade_pedida }}</p>
                                </div>
                                <div class="flex gap-3 w-full sm:w-1/2">
                                    <div class="w-1/2">
                                        <label class="block text-[9px] font-black text-red-500 uppercase mb-1">Qtd Perdida</label>
                                        <input type="number" name="avarias[{{ $item->id }}]" min="0" max="{{ $item->quantidade_pedida }}" value="0" class="input-qtd w-full rounded border-gray-300 focus:border-red-500 focus:ring-red-500 font-black text-center text-lg text-red-600 bg-red-50">
                                    </div>
                                    <div class="w-1/2 flex flex-col relative overflow-hidden">
                                        <label class="block text-[9px] font-black text-gray-500 uppercase mb-1 text-center">Tirar Foto</label>
                                        
                                        <label class="btn-foto cursor-pointer bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 rounded font-black text-[10px] uppercase flex items-center justify-center flex-1 transition-colors text-center">
                                            <span>📸 FOTO</span>
                                            <input type="file" name="fotos[{{ $item->id }}]" accept="image/*" capture="environment" class="input-foto absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="comprimirFoto(event, this)">
                                        </label>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="pt-6 flex gap-4">
                            <button @click="modoAvaria = false" type="button" class="w-1/3 py-4 bg-gray-200 text-gray-700 font-black rounded-lg uppercase text-xs hover:bg-gray-300 transition-colors">Voltar</button>
                            <button type="submit" id="btn-executar" class="w-2/3 py-4 bg-red-600 text-white font-black rounded-lg shadow-lg uppercase text-xs hover:bg-red-700 transition-colors">Executar Multa e Baixa</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-6 bg-gray-50 text-center rounded-lg border border-gray-200 mt-4 shadow-inner">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <p class="text-brand-dark font-black uppercase tracking-widest text-sm">OS Finalizada e Arquivada.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function tocarBip() {
            try {
                let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                let osc = audioCtx.createOscillator();
                osc.type = "sine"; 
                osc.frequency.setValueAtTime(800, audioCtx.currentTime); 
                osc.connect(audioCtx.destination); 
                osc.start(); 
                osc.stop(audioCtx.currentTime + 0.15); 
            } catch (e) {}
        }

        // 🛡️ MOTOR DE COMPRESSÃO DE IMAGENS (Resolve o Bug de 5MB)
        function comprimirFoto(event, inputElement) {
            let file = event.target.files[0];
            if (!file) return;

            tocarBip(); // Bip de feedback ao tirar a foto
            
            let label = inputElement.parentElement;
            let spanTxt = label.querySelector('span');
            
            label.classList.remove('bg-gray-100', 'text-gray-700', 'border-gray-300', 'bg-red-100', 'text-red-700', 'border-red-500');
            label.classList.add('bg-yellow-100', 'text-yellow-700', 'border-yellow-400');
            spanTxt.innerText = '⏳ PROCESSANDO...';

            let reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(e) {
                let img = new Image();
                img.src = e.target.result;
                img.onload = function() {
                    let canvas = document.createElement('canvas');
                    let ctx = canvas.getContext('2d');
                    
                    // Trava o tamanho em 800px para o PDF ficar leve (Compressão Visual)
                    let MAX_WIDTH = 800; let MAX_HEIGHT = 800;
                    let width = img.width; let height = img.height;

                    if (width > height) {
                        if (width > MAX_WIDTH) { height *= MAX_WIDTH / width; width = MAX_WIDTH; }
                    } else {
                        if (height > MAX_HEIGHT) { width *= MAX_HEIGHT / height; height = MAX_HEIGHT; }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    // Cria um novo arquivo JPG com 60% de qualidade (Cai de 5MB para ~150KB)
                    canvas.toBlob(function(blob) {
                        let container = new DataTransfer();
                        let compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, ".jpg"), { type: "image/jpeg", lastModified: Date.now() });
                        container.items.add(compressedFile);
                        
                        inputElement.files = container.files; // Substitui no form invisivelmente

                        // Feedback de sucesso
                        label.classList.remove('bg-yellow-100', 'text-yellow-700', 'border-yellow-400');
                        label.classList.add('bg-green-100', 'text-green-700', 'border-green-400');
                        spanTxt.innerText = '✅ OK (' + Math.round(blob.size / 1024) + 'kb)';
                        
                        // Marca o input como "preenchido" para a validação final
                        inputElement.setAttribute('data-preenchido', 'sim');
                    }, 'image/jpeg', 0.6);
                };
            };
        }

        // 🛡️ MOTOR DE VALIDAÇÃO (Trava de Evidência Obrigatória)
        function validarEEnviar() {
            let linhas = document.querySelectorAll('.row-avaria');
            let falhou = false;

            linhas.forEach(function(linha) {
                let qtd = parseInt(linha.querySelector('.input-qtd').value) || 0;
                let inputFoto = linha.querySelector('.input-foto');
                let labelFoto = linha.querySelector('.btn-foto');
                
                // Se ele disse que quebrou (> 0) e NÃO tirou a foto...
                if (qtd > 0 && inputFoto.getAttribute('data-preenchido') !== 'sim') {
                    falhou = true;
                    // Fica vermelho piscando
                    labelFoto.classList.remove('bg-gray-100', 'border-gray-300');
                    labelFoto.classList.add('bg-red-100', 'text-red-700', 'border-red-500', 'animate-pulse');
                    labelFoto.querySelector('span').innerText = '🚨 FOTO OBRIGATÓRIA';
                }
            });

            if (falhou) {
                alert('BLOQUEIO: Você marcou peças quebradas sem anexar as evidências fotográficas. Tire as fotos exigidas nas linhas vermelhas para poder continuar.');
                return false; // Trava o botão de enviar
            }

            // Se tudo estiver certo, apita, trava o botão para não dar duplo-clique e envia!
            tocarBip();
            let btn = document.getElementById('btn-executar');
            btn.innerHTML = "Salvando Laudos...";
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            return true;
        }
    </script>
</x-app-layout>