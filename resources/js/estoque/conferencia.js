// resources/js/estoque/conferencia.js

// O Vite carrega módulos via 'defer', então o DOM já estará pronto e seguro para manipulação.
const readerElement = document.getElementById('reader');
const scanFeedback = document.getElementById('scanFeedback');
const formAvaria = document.getElementById('formAvaria');
const inputPedidoId = document.getElementById('pedido_id');
const fotoAvaria = document.getElementById('fotoAvaria');
const btnSincronizar = document.getElementById('btnSincronizar');
const compressFeedback = document.getElementById('compressFeedback');

// ========================================================================
// 1. MOTOR DE LEITURA ÓTICA (QR CODE)
// ========================================================================
if (readerElement) {
    const html5QrCode = new Html5Qrcode("reader");

    html5QrCode.start(
        { facingMode: "environment" }, // Força o uso da câmera traseira do dispositivo
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText, decodedResult) => {
            // Sucesso na Leitura
            html5QrCode.stop(); // Mata o processo de vídeo para poupar bateria e RAM do celular
            readerElement.classList.add('hidden');
            scanFeedback.classList.remove('hidden');
            scanFeedback.innerText = `Pedido Localizado: ${decodedText}`;
            
            inputPedidoId.value = decodedText;
            formAvaria.classList.remove('hidden');
        },
        (errorMessage) => { 
            // Ignora silenciosamente os frames de vídeo em que nenhum QR Code é encontrado
        }
    ).catch((err) => {
        alert("Erro ao acessar a câmera. Verifique as permissões do navegador e confirme se a rede está em HTTPS.");
    });
}

// ========================================================================
// 2. MOTOR DE COMPRESSÃO DE IMAGEM (Proteção Client-Side)
// ========================================================================
let imagemComprimidaBase64 = null;

if (fotoAvaria) {
    fotoAvaria.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (!file) return;

        compressFeedback.classList.remove('hidden');
        compressFeedback.innerText = `Esmagando a foto original de ${(file.size / 1024 / 1024).toFixed(2)} MB...`;

        new Compressor(file, {
            quality: 0.6, // Reduz a qualidade da imagem para 60%
            maxWidth: 1024, // Trava a largura máxima, impedindo arquivos gigantes
            success(result) {
                // Transforma o arquivo binário comprimido em texto (Base64) para tráfego via JSON
                const reader = new FileReader();
                reader.readAsDataURL(result);
                reader.onloadend = function() {
                    imagemComprimidaBase64 = reader.result;
                    compressFeedback.innerText = `Sucesso! Imagem reduzida para ${(result.size / 1024).toFixed(2)} KB.`;
                    compressFeedback.classList.add('text-green-600');
                    compressFeedback.classList.remove('text-gray-500');
                }
            },
            error(err) {
                console.error(err.message);
                compressFeedback.innerText = "Falha na compressão local da imagem.";
                compressFeedback.classList.add('text-red-600');
                compressFeedback.classList.remove('text-gray-500');
            },
        });
    });
}

// ========================================================================
// 3. GATILHO DE SINCRONIZAÇÃO PASSIVA (FETCH API)
// ========================================================================
if (btnSincronizar) {
    btnSincronizar.addEventListener('click', function() {
        if (!imagemComprimidaBase64) {
            alert('Atenção: Tire a foto da avaria antes de sincronizar com o servidor.');
            return;
        }

        const payload = {
            pedido_id: inputPedidoId.value,
            foto_base64: imagemComprimidaBase64
        };

        // Trava de UI (Evita o "Double Submit" de usuários impacientes)
        btnSincronizar.disabled = true;
        btnSincronizar.innerText = 'Enviando ao Servidor...';
        btnSincronizar.classList.add('opacity-50', 'cursor-not-allowed');

        fetch('/api/estoque/avaria', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Sincronizado! Avaria registrada e foto arquivada com segurança.");
                
                // Limpeza e reset da tela para a próxima conferência
                imagemComprimidaBase64 = null;
                document.getElementById('formAvaria').reset();
                compressFeedback.classList.add('hidden');
                formAvaria.classList.add('hidden');
                
                // Recarrega a tela para reiniciar a câmera perfeitamente
                window.location.reload();
            } else {
                alert("Aviso do Servidor: " + data.message);
            }
        })
        .catch(error => {
            console.error("Erro de requisição:", error);
            alert("Falha de rede. O galpão pode estar sem internet ou oscilando.");
        })
        .finally(() => {
            // Destrava a UI em caso de erro (o reload no success já limpa a tela de qualquer forma)
            btnSincronizar.disabled = false;
            btnSincronizar.innerText = 'Sincronizar com o Servidor';
            btnSincronizar.classList.remove('opacity-50', 'cursor-not-allowed');
        });
    });
}