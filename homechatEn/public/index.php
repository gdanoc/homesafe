<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeCHAT - Asistente Inteligente</title>
    <style>
        .char-counter {
            text-align: right;
            font-size: 0.9em;
            color: #888;
            margin: 2px 4px 0 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #000000;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .chat-container {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 800px;
            height: 600px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 20px 20px 0 0;
            border-bottom: 1px solid #333;
            position: relative;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 600;
            color: white;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
            color: #bfdbfe;
        }
        .back-button {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
            text-decoration: none;
        }
        .back-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-50%) translateX(-2px);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
        }
        .back-button:active {
            transform: translateY(-50%) translateX(0px);
        }
        #mensajes {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #000000;
            scroll-behavior: smooth;
        }
        
        /* Estilos mejorados para el scrollbar */
        #mensajes::-webkit-scrollbar {
            width: 8px;
        }
        #mensajes::-webkit-scrollbar-track {
            background: #0a0a0a;
            border-radius: 10px;
            margin: 5px;
        }
        #mensajes::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 10px;
            border: 1px solid #1a1a1a;
            transition: all 0.3s ease;
        }
        #mensajes::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.3);
        }
        #mensajes::-webkit-scrollbar-thumb:active {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        }
        
        /* Scrollbar para el textarea también */
        #mensaje::-webkit-scrollbar {
            width: 6px;
        }
        #mensaje::-webkit-scrollbar-track {
            background: #1a1a1a;
            border-radius: 6px;
        }
        #mensaje::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        #mensaje::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .mensaje {
            margin-bottom: 16px;
            animation: fadeIn 0.3s ease-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .mensaje-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            line-height: 1.4;
        }
        .usuario .mensaje-content {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }
        .ia .mensaje-content {
            background: #1a1a1a;
            color: white;
            border: 1px solid #333;
            border-bottom-left-radius: 5px;
        }
        .error .mensaje-content {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border-bottom-left-radius: 5px;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }
        .loading .mensaje-content {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border-bottom-left-radius: 5px;
            position: relative;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }
        .loading .mensaje-content::after {
            content: '';
            display: inline-block;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: white;
            margin-left: 8px;
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%,
            100% {
                opacity: 1;
            }
            50% {
                opacity: 0.3;
            }
        }
        .mensaje-label {
            font-size: 12px;
            color: #3b82f6;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .usuario .mensaje-label {
            text-align: right;
            color: #60a5fa;
        }
        .ia .mensaje-label {
            color: #3b82f6;
        }
        .error .mensaje-label {
            color: #ef4444;
        }
        .info {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 6px;
            font-style: italic;
        }
        .chat-form {
            padding: 20px;
            background: #1a1a1a;
            border-top: 1px solid #333;
            border-radius: 0 0 20px 20px;
        }
        .input-container {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }
        #mensaje {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #333;
            border-radius: 25px;
            font-size: 14px;
            resize: none;
            min-height: 44px;
            max-height: 120px;
            font-family: inherit;
            transition: border-color 0.3s ease;
            background: #000000;
            color: white;
        }
        #mensaje:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        #mensaje::placeholder {
            color: #6b7280;
        }
        #enviar {
            padding: 12px 24px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }
        #enviar:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        }
        #enviar:disabled {
            background: #374151;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .welcome-message {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .welcome-message h3 {
            color: #3b82f6;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 10px 0;
        }
        .feature {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: white;
        }
        .feature-icon {
            font-size: 18px;
        }
        .feature strong {
            color: #3b82f6;
        }
        .note {
            font-size: 12px;
            color: #9ca3af;
            font-style: italic;
            margin-top: 10px;
            text-align: center;
        }
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .chat-container {
                height: 100vh;
                border-radius: 0;
            }
            .header {
                border-radius: 0;
            }
            .chat-form {
                border-radius: 0;
            }
            .mensaje-content {
                max-width: 85%;
            }
            .features {
                grid-template-columns: 1fr;
            }
            .back-button {
                left: 15px;
                width: 35px;
                height: 35px;
                font-size: 16px;
            }
            .header h1 {
                font-size: 20px;
            }
        }
        /* Loading animation for button */
        .loading-spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="header">
            <a href="../../EN_dashboard.php" class="back-button" title="Regresar">
                ←
            </a>
            <h1>🏠 HomeCHAT</h1>
            <p>Asistente Impulsado por IA</p>
        </div>
        <div id="mensajes">
            <div class="welcome-message">
                <h3>¡Bienvenido a HomeSafe! 👋</h3>
                <div class="features">
                    <div class="feature">
                        <span class="feature-icon">🛋️</span>
                        <span><strong>Muebles:</strong> Productos en venta</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">🏠</span>
                        <span><strong>Propiedades:</strong> Casas publicadas</span>
                    </div>
                </div>
                <div class="note">
                    💡 Las respuestas pueden tomar 1-2 minutos
                </div>
            </div>
        </div>
        <form class="chat-form" id="formulario">
            <div class="input-container">
                <textarea
                    id="mensaje"
                    autocomplete="off"
                    placeholder="Pregunta sobre nuestros muebles o propiedades..."
                    required
                    rows="1"></textarea>
                <button type="submit" id="enviar">
                    <span id="enviar-texto">Enviar</span>
                </button>
            </div>
            <div class="char-counter" id="char-counter">0 / 400</div>
        </form>
    </div>
    <script>
        const form = document.getElementById('formulario');
        const mensajes = document.getElementById('mensajes');
        const input = document.getElementById('mensaje');
        const boton = document.getElementById('enviar');
        const enviarTexto = document.getElementById('enviar-texto');
        const charCounter = document.getElementById('char-counter');
        const MAX_CHARS = 400;
        
        const SESSION_KEY = 'homechat_convo';
        const SESSION_TIME_KEY = 'f';
        let abortController = null;
        
        function loadConversation() {
            const saved = sessionStorage.getItem(SESSION_KEY);
            const savedTime = sessionStorage.getItem(SESSION_TIME_KEY);
            if (saved && savedTime && (Date.now() - parseInt(savedTime)) < 60 * 60 * 1000) { //El primero es la cantidad de minutos osea 60
                mensajes.innerHTML = saved;
            } else {
                sessionStorage.removeItem(SESSION_KEY);
                sessionStorage.removeItem(SESSION_TIME_KEY);
            }
        }
        loadConversation();
        
        function saveConversation() {
            sessionStorage.setItem(SESSION_KEY, mensajes.innerHTML);
            sessionStorage.setItem(SESSION_TIME_KEY, Date.now().toString());
        }
        
        window.addEventListener('beforeunload', () => {
            sessionStorage.removeItem(SESSION_KEY);
            sessionStorage.removeItem(SESSION_TIME_KEY);
            if (abortController) abortController.abort();
        });
        
        function updateCharCounter() {
            let val = input.value;
            if (val.length > MAX_CHARS) {
                input.value = val.slice(0, MAX_CHARS);
            }
            charCounter.textContent = `${input.value.length} / ${MAX_CHARS}`;
        }
        input.addEventListener('input', function() {
            updateCharCounter();
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        updateCharCounter();
        form.addEventListener('submit', async e => {
            e.preventDefault();
            let texto = input.value.trim();
            if (!texto) return;
            if (texto.length > MAX_CHARS) {
                alert(`Máximo ${MAX_CHARS} caracteres.`);
                return;
            }
            
            mensajes.innerHTML += `
            <div class="mensaje usuario">
                <div class="mensaje-label">Tú</div>
                <div class="mensaje-content">${texto}</div>
            </div>
        `;
            saveConversation();
            input.value = '';
            updateCharCounter();
            input.style.height = 'auto';
            boton.disabled = true;
            enviarTexto.innerHTML = '<span class="loading-spinner"></span>Enviando...';
            
            const loadingId = 'loading-' + Date.now();
            mensajes.innerHTML += `
            <div class="mensaje loading" id="${loadingId}">
                <div class="mensaje-label">HomeCHAT</div>
                <div class="mensaje-content">
                    Procesando...
                </div>
            </div>
        `;
            mensajes.scrollTop = mensajes.scrollHeight;
            saveConversation();
            
            if (abortController) abortController.abort();
            abortController = new AbortController();
            try {
                
                const historial = mensajes.innerText; 
                const res = await fetch('../includes/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `mensaje=${encodeURIComponent(texto)}&historial=${encodeURIComponent(historial)}`,
                    signal: abortController.signal
                });
                const data = await res.text();
                document.getElementById(loadingId).remove();
                try {
                    const jsonData = JSON.parse(data);
                    if (jsonData.error) {
                        mensajes.innerHTML += `
                        <div class="mensaje error">
                            <div class="mensaje-label">Error</div>
                            <div class="mensaje-content">
                                ${jsonData.message}
                                <div class="info">Detalles: ${jsonData.details || 'N/A'}</div>
                            </div>
                        </div>
                    `;
                    } else {
                        mensajes.innerHTML += `
                        <div class="mensaje ia">
                            <div class="mensaje-label">HomeCHAT</div>
                            <div class="mensaje-content">
                                ${jsonData.response}
                            </div>
                        </div>
                    `;
                    }
                } catch {
                    mensajes.innerHTML += `
                    <div class="mensaje ia">
                        <div class="mensaje-label">HomeCHAT</div>
                        <div class="mensaje-content">${data}</div>
                    </div>
                `;
                }
                saveConversation();
            } catch (err) {
                const loadingElement = document.getElementById(loadingId);
                if (loadingElement) loadingElement.remove();
                mensajes.innerHTML += `
                <div class="mensaje error">
                    <div class="mensaje-label">Error de conexión</div>
                    <div class="mensaje-content">
                        ${err.message}
                    </div>
                </div>
            `;
                saveConversation();
            } finally {
                boton.disabled = false;
                enviarTexto.textContent = 'Enviar';
                mensajes.scrollTop = mensajes.scrollHeight;
            }
        });
        
        input.addEventListener('keypress', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                
                if (!boton.disabled) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        });
        
        input.focus();
    </script>
</body>
</html>
