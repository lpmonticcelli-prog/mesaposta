<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa Posta Locações – Materiais para Eventos Premium</title>

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        /* --- Reset Básico e Configurações Globais --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.7;
            background-color: #f8f9fa;
            color: #333;
            overflow-x: hidden;
        }
        body.noscroll { overflow: hidden; }

        /* --- Animação do Preloader --- */
        #preloader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            background: #111111; z-index: 10000; display: flex;
            justify-content: center; align-items: center;
            transition: opacity 0.75s ease, visibility 0.75s ease;
        }
        #preloader.loaded { opacity: 0; visibility: hidden; }
        .spinner {
            width: 50px; height: 50px; border: 5px solid rgba(255, 255, 255, 0.2);
            border-top-color: #1e3a8a; border-radius: 50%; animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* --- Barra de Progresso --- */
        #scroll-progress-bar {
            position: fixed; top: 0; left: 0; height: 5px;
            background: #1e3a8a; width: 0%; z-index: 9999;
            transition: width 0.1s linear;
        }

        /* --- Menu Hambúrguer (Botão) --- */
        #menu-toggle-btn {
            position: fixed; top: 20px; left: 20px;
            width: 40px; height: 40px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            border: 1px solid #eee; border-radius: 8px;
            z-index: 9998; cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .hamburger-line {
            width: 20px; height: 2px; background: #333;
            display: block; margin: 4px auto;
            transition: all 0.3s cubic-bezier(0.645, 0.045, 0.355, 1);
        }
        #menu-toggle-btn.active .line-1 { transform: translateY(6px) rotate(45deg); }
        #menu-toggle-btn.active .line-2 { opacity: 0; }
        #menu-toggle-btn.active .line-3 { transform: translateY(-6px) rotate(-45deg); }
        
        /* --- Menu Overlay (Tela Cheia) --- */
        #menu-overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100vh;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
            z-index: 9997;
            display: flex; justify-content: center; align-items: center;
            opacity: 0; visibility: hidden;
            transform: scale(1.1);
            transition: opacity 0.4s ease, visibility 0.4s ease, transform 0.4s ease;
        }
        #menu-overlay.active { opacity: 1; visibility: visible; transform: scale(1); }
        #menu-overlay ul { list-style: none; text-align: center; }
        #menu-overlay ul li { opacity: 0; transform: translateY(20px); transition: opacity 0.3s ease, transform 0.3s ease; }
        #menu-overlay.active ul li { opacity: 1; transform: translateY(0); }
        #menu-overlay ul li a {
            text-decoration: none; color: #fff; font-size: 2rem;
            font-weight: 500; line-height: 3; transition: color 0.3s ease;
        }
        #menu-overlay ul li a:hover { color: #1e3a8a; }

        /* --- Utilitários --- */
        .container { max-width: 1100px; margin: 0 auto; padding: 0 2rem; }
        section { padding: 6rem 0; overflow: hidden; }
        h1, h2, h3 { margin-bottom: 1.5rem; line-height: 1.2; }
        h2 { font-size: 2.5rem; text-align: center; color: #1a1a1a; }
        h3 { font-size: 1.8rem; color: #1e3a8a; }
        p { margin-bottom: 1rem; font-size: 1.05rem; color: #555; }
        .text-center { text-align: center; }
        .btn {
            display: inline-block; background: #1e3a8a; color: #fff;
            padding: 0.8rem 1.8rem; text-decoration: none; border-radius: 5px;
            font-weight: bold; font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3);
        }
        .btn:hover {
            background-color: #172554; color: #fff; transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(30, 58, 138, 0.5);
        }

        /* --- Seção Hero (Home) --- */
        #home {
            height: 100vh; display: flex; flex-direction: column;
            justify-content: center; align-items: center; text-align: center;
            color: #fff; padding: 0 2rem; position: relative;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1511795409834-ef04bbd61622?q=80&w=2069&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
        }
        #home h1 { font-size: 3.2rem; margin-bottom: 1rem; min-height: 100px; color: #fff; }
        .typed-cursor { opacity: 1; font-weight: 100; font-size: 3.5rem; animation: blink 0.7s infinite; }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0; } 100% { opacity: 1; } }
        #home p { font-size: 1.5rem; margin-bottom: 2rem; color: #eee; }

        /* --- Seção Contato --- */
        #contato { background-color: #f8f9fa; }
        #contato form {
            max-width: 700px; margin: 2rem auto 0 auto; background: #fff;
            padding: 2.5rem; border-radius: 8px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .form-group { position: relative; margin-bottom: 2rem; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 0.8rem; border: 1px solid #ddd;
            border-radius: 5px; font-size: 1rem; font-family: inherit;
        }
        .form-group textarea { resize: vertical; min-height: 150px; }
        .form-group label {
            position: absolute; top: 0.8rem; left: 0.8rem; color: #999;
            pointer-events: none; transition: all 0.2s ease-out;
        }
        .form-group input:focus + label, .form-group textarea:focus + label,
        .form-group input:not(:placeholder-shown) + label, .form-group textarea:not(:placeholder-shown) + label {
            top: -1.2rem; left: 0; font-size: 0.85rem; font-weight: 600; color: #1e3a8a;
        }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #1e3a8a; }
        .btn-submit { width: 100%; border: none; cursor: pointer; }

        /* --- Footer --- */
        #main-footer { background: #111111; color: #fff; text-align: center; padding: 2.5rem 0; }
        #main-footer a { color: #fff; text-decoration: none; transition: color 0.3s ease; }
        #main-footer a:hover { color: #1e3a8a; }
    </style>
</head>
<body>

    <div id="preloader"><div class="spinner"></div></div>
    <div id="scroll-progress-bar"></div>

    <button id="menu-toggle-btn" aria-label="Abrir Menu">
        <span class="hamburger-line line-1"></span>
        <span class="hamburger-line line-2"></span>
        <span class="hamburger-line line-3"></span>
    </button>
    
    <div id="menu-overlay">
        <ul>
            <li><a href="#home">Início</a></li>
            <li><a href="#contato">Solicitar Orçamento</a></li>
            <li><a href="{{ route('login') }}">Acesso Restrito</a></li>
        </ul>
    </div>

    <main>
        <section id="home">
            <div class="hero-content" data-aos="zoom-in">
                <h1 class="typed-text-container"><span id="typed-text"></span></h1>
                <p data-aos="fade-up" data-aos-delay="100">Locação de móveis e louças para eventos premium. Transformando o seu ambiente.</p>
                <a href="#contato" class="btn" data-aos="fade-up" data-aos-delay="200">Solicite um Orçamento</a>
            </div>
        </section>

        <section id="contato" class="container">
            <h2 data-aos="fade-down">Solicite um Orçamento</h2>
            <p class="text-center" style="max-width: 600px; margin: -1rem auto 2rem auto;">
                Preencha os dados abaixo e entraremos em contato rapidamente para montar a estrutura do seu evento.
            </p>
            
            <form id="contact-form" action="/api/orcamento" method="POST" data-aos="fade-up">
                @csrf
                
                <div class="form-group">
                    <input type="text" id="name" name="name" placeholder=" " required>
                    <label for="name">Nome Completo</label>
                </div>
                <div class="form-group">
                    <input type="tel" id="telefone" name="telefone" placeholder=" " required>
                    <label for="telefone">WhatsApp (Ex: 11 98765-4321)</label>
                </div>
                <div class="form-group">
                    <input type="date" id="data_evento" name="data_evento" style="color: transparent;" onfocus="this.style.color='#333'" placeholder=" " required>
                    <label for="data_evento">Data do Evento</label>
                </div>
                <div class="form-group">
                    <textarea id="message" name="message" placeholder=" " required></textarea>
                    <label for="message">Quais materiais você precisa? (Louças, mesas, cadeiras...)</label>
                </div>
                
                <input type="text" name="url_website_fake" class="hidden" style="display:none" autocomplete="off" tabindex="-1">

                <button type="submit" id="btn-submit" class="btn btn-submit">Enviar Pedido</button>
            </form>
        </section>
    </main>
    
    <footer id="main-footer">
        <p>© {{ date('Y') }} Mesa Posta Locações. Todos os direitos reservados.</p>
        <p style="margin-top: 1rem;"><a href="{{ route('login') }}">Painel Administrativo</a></p>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://unpkg.com/typed.js@2.0.16/dist/typed.umd.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => { document.getElementById('preloader').classList.add('loaded'); });
        AOS.init({ duration: 800, once: true, offset: 50 });

        new Typed('#typed-text', {
            strings: ["MESA POSTA", "O requinte do seu evento.", "Locação Premium.", "Elegância e Sofisticação."],
            typeSpeed: 60, backSpeed: 30, backDelay: 1500, loop: true, smartBackspace: true
        });

        const menuToggleBtn = document.getElementById('menu-toggle-btn');
        const menuOverlay = document.getElementById('menu-overlay');
        const menuLinks = document.querySelectorAll('#menu-overlay ul li a');

        menuToggleBtn.addEventListener('click', () => {
            menuToggleBtn.classList.toggle('active');
            menuOverlay.classList.toggle('active');
            document.body.classList.toggle('noscroll');
        });

        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                menuToggleBtn.classList.remove('active');
                menuOverlay.classList.remove('active');
                document.body.classList.remove('noscroll');
            });
        });
    </script>
</body>
</html>