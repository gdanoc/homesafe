<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

$email = $_SESSION['email'];
$id_chat = $_POST['id_chat'] ?? null;

if (!isset($_SESSION['email'])) {
    echo '<p><script>swal({
        title: "Error",
        text: "Debes iniciar sesión",
        icon: "warning",
        button: "Cerrar",
    
        }).then(function() {
        window.location = "index.php";
    });</script></p>';
}

if (!$id_chat) {
    die("Error");
}

$host = "localhost";
$port = "5432";
$user = "postgres";
$password = "TU_PASSWORD_DE_BASE_DE_DATOS";
$dbname = "homesafe";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Conexión fallida: " . pg_last_error());
}

$query = "SELECT * FROM chats_manager WHERE id_chat = $1";
$result = pg_query_params($conn, $query, array($id_chat));
$row = pg_fetch_assoc($result);
$id_casa = $row['id_casa'] ?? null;
$customer_gmail = $row['user_gmail_customer'] ?? null;
$pedido = $row['id_pedido'] ?? null;

$query2 = "SELECT * FROM propiedades WHERE id = $1";
$result2 = pg_query_params($conn, $query2, array($id_casa));
$row2 = pg_fetch_assoc($result2);
$nombre_casa = $row2['nombre'] ?? null;

if ($email == $row['user_gmail_customer'] || $email == $row['user_gmail_other']) {
    if ($email == $row['user_gmail_customer']) {
        $quien_es = "Customer";
        $rol = 0;
    } elseif ($email == $row['user_gmail_other']) {
        $quien_es = "Other";
        $rol = 3;
    }
} else {
    header("Location: regresar.php");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>HomeSafe Chat</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Variables CSS */
        :root {
            --color-black: #000000;
            --color-white: #ffffff;
            --color-blue: #2563eb;
            --color-blue-hover: #1d4ed8;
            --color-blue-light: #3b82f6;
            --color-gray-50: #f9fafb;
            --color-gray-100: #f3f4f6;
            --color-gray-200: #e5e7eb;
            --color-gray-300: #d1d5db;
            --color-gray-800: #1f2937;
            --color-gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--color-black) 0%, var(--color-gray-900) 100%);
            color: var(--color-white);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .chat-header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .chat-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--color-blue), transparent);
        }

        .chat-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 1.125rem;
        }

        .chat-icon {
            width: 2rem;
            height: 2rem;
            background: var(--color-blue);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-white);
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .status-dot {
            width: 0.5rem;
            height: 0.5rem;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Chat Container */
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
            padding: 0 1rem;
            height: calc(100vh - 70px);
            /* Altura fija restando el header */
            overflow: hidden;
        }

        /* Messages Area */
        #chat {
            flex: 1;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 1rem;
            margin: 1rem 0;
            padding: 1.5rem;
            overflow-y: auto;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            max-height: calc(100% - 130px);
            /* Altura máxima restando el espacio para el formulario */
            min-height: 200px;
            /* Altura mínima */
        }

        #chat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 80%, rgba(37, 99, 235, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.05) 0%, transparent 50%);
            border-radius: 1rem;
            pointer-events: none;
        }

        /* Custom Scrollbar */
        #chat::-webkit-scrollbar {
            width: 6px;
        }

        #chat::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }

        #chat::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        #chat::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Message Styles - Ajustados al tamaño del contenido */
        #chat .mensaje {
            margin: 1rem 0;
            padding: 0.875rem 1.25rem;
            border-radius: 1.25rem;
            max-width: 75%;
            word-wrap: break-word;
            line-height: 1.5;
            font-size: 0.95rem;
            position: relative;
            animation: messageSlideIn 0.3s ease-out;
            display: inline-block;
            width: fit-content;
        }

        /* Mensajes propios */
        #chat .mensaje.own {
            background: linear-gradient(135deg, var(--color-blue) 0%, var(--color-blue-light) 100%);
            color: var(--color-white);
            margin-left: auto;
            margin-right: 0;
            text-align: right;
            border-bottom-right-radius: 0.375rem;
            box-shadow: var(--shadow-md);
            display: block;
            width: fit-content;
            max-width: 75%;
            margin-left: auto;
        }

        /* Mensajes de otros */
        #chat .mensaje.other {
            background: rgba(255, 255, 255, 0.08);
            color: var(--color-white);
            margin-right: auto;
            margin-left: 0;
            text-align: left;
            border-bottom-left-radius: 0.375rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            display: block;
            width: fit-content;
            max-width: 75%;
            margin-right: auto;
        }

        #chat p strong {
            font-weight: 600;
            opacity: 0.9;
        }

        #chat p.own strong {
            color: rgba(255, 255, 255, 0.9);
        }

        #chat p.other strong {
            color: var(--color-blue-light);
        }

        /* Input Form */
        .chat-input-container {
            padding: 1rem 0 1.5rem;
            background: rgba(255, 255, 255, 0.02);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            position: sticky;
            bottom: 0;
            width: 100%;
            z-index: 10;
        }

        #formulario {
            display: flex;
            gap: 0.75rem;
            align-items: flex-end;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1.5rem;
            padding: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.2s ease;
        }

        #formulario:focus-within {
            border-color: var(--color-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        #formulario input[type="text"] {
            flex: 1;
            background: transparent;
            border: none;
            padding: 0.875rem 1rem;
            color: var(--color-white);
            font-size: 0.95rem;
            outline: none;
            font-family: inherit;
        }

        #formulario input[type="text"]::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        #formulario button {
            background: var(--color-blue);
            border: none;
            color: var(--color-white);
            padding: 0.875rem 1.5rem;
            border-radius: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            min-width: fit-content;
        }

        #formulario button:hover {
            background: var(--color-blue-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        #formulario button:active {
            transform: translateY(0);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--color-white);
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
            width: 100%;
            justify-content: center;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .chat-container {
                padding: 0 0.75rem;
            }

            .chat-header {
                padding: 0.75rem 1rem;
            }

            #chat {
                margin: 0.75rem 0;
                padding: 1rem;
            }

            #chat p {
                max-width: 85%;
                font-size: 0.9rem;
            }

            #formulario {
                gap: 0.5rem;
                padding: 0.375rem;
            }

            #formulario input[type="text"] {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            #formulario button {
                padding: 0.75rem 1rem;
                font-size: 0.85rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        .btn-comprar {
            background: var(--color-blue);
            border: none;
            color: var(--color-white);
            padding: 0.6rem 1.2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            box-shadow: var(--shadow-sm);
        }

        .btn-comprar:hover {
            background: var(--color-blue-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-comprar:active {
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="chat-header">
        <div class="chat-title">
            <div class="chat-icon">
                <i class="fas fa-comments"></i>
            </div>
            <span>HomeSafe Chat</span>
        </div>

        <!-- Botón Comprar -->
        <span><?php echo htmlspecialchars($nombre_casa); ?></span>


        <div class="status-indicator">
            <div class="status-dot"></div>
            <span><?php echo htmlspecialchars($email); ?></span>
        </div>
    </div>

    <!-- Chat Container -->
    <div class="chat-container">
        <!-- Messages Area -->
        <div id="chat"></div>

        <!-- Input Area -->
        <div class="chat-input-container">
            <form id="formulario" autocomplete="off" onsubmit="return false;">
                <input type="hidden" name="id_chat" value="<?php echo htmlspecialchars($id_chat); ?>">
                <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($email); ?>">
                <input type="text" name="mensaje" placeholder="Escribe tu mensaje..." required autocomplete="off" maxlength="200" /> <button type="submit">
                    <i class="fas fa-paper-plane"></i>
                    <span>Enviar</span>
                </button>
            </form>
            <div class="action-buttons">
                <form method="post" action="regresar.php" style="width: 100%;">
                    <input type="text" name="quien_es" value="<?php echo htmlspecialchars($quien_es); ?>" hidden>
                    <button type="submit" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        <span>Regresar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const id_chat_php = "<?php echo htmlspecialchars($id_chat); ?>";
        const usuario_php = "<?php echo htmlspecialchars($email); ?>";
    </script>

    <script type="module">
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-app.js";
        import {
            getDatabase,
            ref,
            push,
            set,
            onChildAdded
        } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-database.js";
        import {
            getAuth,
            signInAnonymously
        } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-auth.js";
//*********************** */
        const firebaseConfig = {
            apiKey: "AIzaSyCw1u2FUspp7rB5aji-8e1dljiBVwjEQtI",
            authDomain: "chats-ceb80.firebaseapp.com",
            projectId: "chats-ceb80",
            storageBucket: "chats-ceb80.appspot.com",
            messagingSenderId: "97614928234",
            appId: "1:97614928234:web:74c058ad2e570768fead13",
            measurementId: "G-GDBZ9NJVWB",
            databaseURL: "https://chats-ceb80-default-rtdb.firebaseio.com"
        };

        const app = initializeApp(firebaseConfig);
        const db = getDatabase(app);
        const auth = getAuth(app);

        
        signInAnonymously(auth)
            .then(() => {
                console.log("Sesión anónima iniciada");

                
                const mensajesRef = ref(db, 'mensajes/' + id_chat_php);

                
                onChildAdded(mensajesRef, (snapshot) => {
                    const msg = snapshot.val();
                    const chatDiv = document.getElementById("chat");
                    const div = document.createElement('div');
                    div.className = "mensaje";
                    if (msg.usuario === usuario_php) {
                        div.className += " own";
                    } else {
                        div.className += " other";
                    }

                    const usuarioSpan = document.createElement('strong');
                    usuarioSpan.textContent = msg.usuario + ': ';

                    const mensajeSpan = document.createElement('span');
                    mensajeSpan.textContent = msg.texto;

                    div.appendChild(usuarioSpan);
                    div.appendChild(mensajeSpan);

                    chatDiv.appendChild(div);
                    chatDiv.scrollTop = chatDiv.scrollHeight;
                });

                
                document.getElementById("formulario").addEventListener("submit", function(e) {
                    e.preventDefault();
                    const mensajeInput = document.getElementsByName('mensaje')[0];
                    const texto = mensajeInput.value.trim();
                    if (!texto) return;
                    if (texto.length > 200) {
                        alert("El mensaje no puede tener más de 200 caracteres.");
                        return;
                    }
                    const nuevo = push(mensajesRef);
                    set(nuevo, {
                        usuario: usuario_php,
                        texto: texto,
                        timestamp: Date.now()
                    });
                    mensajeInput.value = '';
                });
            })
            .catch((error) => {
                console.error("Error al iniciar sesión anónima:", error);
                alert("No se pudo conectar con la base de datos.");
            });
    </script>

</body>

</html>