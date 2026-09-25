<style>
    /* Contenedor principal para posicionamiento */
    .chat-button-container {
        position: fixed;
        bottom: 24px;
        /* 6 * 4px = 24px */
        right: 24px;
        /* 6 * 4px = 24px */
        z-index: 50;
    }

    /* Estilos del botón de chat */
    .chat-button {
        position: relative;
        width: 56px;
        /* 14 * 4px = 56px */
        height: 56px;
        /* 14 * 4px = 56px */
        background-color: #2563eb;
        /* blue-600 */
        color: white;
        border-radius: 9999px;
        /* full rounded */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        /* shadow-lg */
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        /* Para que el pulso no se salga */
    }

    .chat-button:hover {
        background-color: #1d4ed8;
        /* blue-700 */
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        /* shadow-xl */
    }

    /* Estilos del icono dentro del botón */
    .chat-button svg {
        transition: transform 0.2s ease;
    }

    .chat-button:hover svg {
        transform: scale(1.1);
    }

    /* Efecto de pulso */
    .chat-button-pulse {
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        background-color: #2563eb;
        /* blue-600 */
        opacity: 0.2;
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.5);
            /* Ajustado para un pulso más visible */
        }
    }

    /* Estilos del tooltip/viñeta */
    .chat-tooltip {
        position: absolute;
        bottom: 100%;
        /* Arriba del botón */
        right: 0;
        margin-bottom: 8px;
        /* 2 * 4px = 8px */
        padding: 8px 12px;
        /* px-3 py-2 */
        background-color: #1f2937;
        /* gray-900 */
        color: white;
        font-size: 14px;
        /* text-sm */
        border-radius: 8px;
        /* rounded-lg */
        white-space: nowrap;
        transition: opacity 0.3s ease, transform 0.3s ease;
        opacity: 0;
        transform: translateY(8px);
        /* translate-y-2 */
        pointer-events: none;
        /* Para que no bloquee eventos del botón */
    }

    /* Mostrar tooltip al hacer hover en el contenedor */
    .chat-button-container:hover .chat-tooltip {
        opacity: 1;
        transform: translateY(0);
    }

    /* Flecha del tooltip */
    .chat-tooltip-arrow {
        position: absolute;
        top: 100%;
        /* Abajo del tooltip */
        right: 16px;
        /* Ajustar para que esté centrado con el botón */
        width: 0;
        height: 0;
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 4px solid #1f2937;
        /* gray-900 */
    }
</style>
<div class="chat-button-container">
    <!-- Tooltip/Viñeta -->
    <div class="chat-tooltip">
        Habla con la IA de HomeSafe
        <!-- Flecha del tooltip -->
        <div class="chat-tooltip-arrow"></div>
    </div>

    <!-- Botón de Chat -->
    <button class="chat-button" aria-label="Habla con la IA de HomeSafe" href="../homechat/public/index.php">
        <!-- Icono de chat (SVG de Lucide React, convertido a SVG puro) -->
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-message-circle">
            <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
        </svg>
        <!-- Efecto de pulso -->
        <div class="chat-button-pulse"></div>

    </button>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const chatButton = document.querySelector(".chat-button")

  if (chatButton) {
    chatButton.addEventListener("click", () => {
      
      window.location.href = "homechat/public/index.php"
    })
  }
})
</script>