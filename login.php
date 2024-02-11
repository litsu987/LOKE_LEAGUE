<?php
session_start();

if (isset($_SESSION['user_id'])) {
    // El usuario ya ha iniciado sesión, redirigirlo a otra página o mostrar un mensaje de error
    header("Location: dashboard.php"); // Puedes cambiar esto según tus necesidades
    exit;
}

include("config.php");

try {
    $hostname = "localhost";
    $dbname = "lokeleague";
    $username = $dbUser;
    $pw = $dbPass;
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
} catch (PDOException $e) {
    echo "Failed to get DB handle: " . $e->getMessage();
    escribirEnLog("[LOGIN] " . $e);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        // Este código se ejecutará solo cuando se registre un nuevo usuario
        $destinatario = $_POST['email'];
        $username = $_POST['username'];
        $password = hash('sha512', $_POST['password']);

        // Verificar si el correo ya está registrado
        $emailCheckQuery = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE Email = ?");
        $emailCheckQuery->execute([$destinatario]);
        $emailCount = $emailCheckQuery->fetchColumn();

        // Verificar si el nombre de usuario ya está en uso
        $usernameCheckQuery = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE Username = ?");
        $usernameCheckQuery->execute([$username]);
        $usernameCount = $usernameCheckQuery->fetchColumn();

        if ($emailCount > 0) {
            echo '<script>showNotification("error", "El correo electrónico ya está registrado");</script>';
            $errorMessage = "Error: El correo electrónico ya está registrado.";
        } elseif ($usernameCount > 0) {
            $errorMessage = "Error: El nombre de usuario ya está en uso.";
        }else {
            // Generar token de forma más sencilla
            $tokenLength = 40;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $token = '';
            for ($i = 0; $i < $tokenLength; $i++) {
                $randomIndex = rand(0, strlen($characters) - 1);
                $token .= $characters[$randomIndex];
            }

            // Validar que las contraseñas coincidan
            if ($_POST['password'] !== $_POST['confirmPassword']) {
                echo '<script>showNotification("error", "Las contraseñas no coinciden");</script>';
                exit; // No continuar si las contraseñas no coinciden
            }

            // Insertar en la base de datos
            $query = $pdo->prepare("INSERT INTO Users(`Username`, `Password`, `Email`, `ValidationToken`) VALUES (?, ?, ?, ?)");
            $query->bindParam(1, $username);
            $query->bindParam(2, $password);
            $query->bindParam(3, $destinatario);
            $query->bindParam(4, $token);

            // Ejecutar la consulta
            if ($query->execute()) {
                // Enviar correo electrónico
                $title = "Bienvenido, " . $username . "!";
                $content = "Bienvenido, <strong>" . $username . "</strong>. Valida tu cuenta accediendo a este enlace.<br><a class='btn' href='https://aws25.ieti.site/Proyecto_Vota/Verification.php?validToken=" . $token . "'>Validar cuenta</a>.<br><br>Atentamente, el equipo de Vota EJA.";
    
                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->Mailer = "smtp";
    
                $mail->SMTPDebug  = 0;
                $mail->SMTPAuth   = TRUE;
                $mail->SMTPSecure = "tls";
                $mail->Port       = 587;
                $mail->Host       = "smtp.gmail.com";
                $mail->Username   = "anaviogarcia.cf@iesesteveterradas.cat"; // Email de la cuenta de correo desde la que se enviarán los correos
                $mail->Password   = "Caqjuueeemke64"; // Contraseña de la cuenta de correo
    
                $mail->IsHTML(true);
                $mail->AddAddress($destinatario);
                $mail->SetFrom("anaviogarcia.cf@iesesteveterradas.cat", "LOKE LEAGUE");
    
                $mail->Subject = "Bienvenido a LOKE LEAGUE";
                $mail->Body = "Bienvenido, $username. Valida tu cuenta accediendo a este enlace: localhost/LokeLeague/Verification.php?validToken=$token'. Atentamente, el equipo de LOKE LEAGUE.";
    
                if ($mail->Send()) {
                    echo '<script>showNotification("success",aaaaaa'.$token.'");</script>';
                } else {
                    echo '<script>showNotification("error", "Error al enviar el correo de validación: ' . $mail->ErrorInfo . '");</script>';
                }
            } else {
                echo '<script>showNotification("error", "Error al registrar en la base de datos");</script>';
            }
        }
    } else {
        $destinatario = $_POST['email'];
        $password = hash('sha512', $_POST['password']);

        $query = $pdo->prepare("SELECT * FROM Users WHERE Email = ? AND Password = ?");
        $query->execute([$destinatario, $password]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['IsAuthenticated'] == 1) {
                // Iniciar sesión y pasar el nombre de usuario a través de la sesión
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['username'] = $user['Username'];
                header("Location: dashboard.php");
                exit;
            } else {
                // El usuario no está autenticado, mostrar un mensaje
                $_SESSION['login_error'] = "Debes validar tu cuenta para iniciar sesión. Revisa tu correo electrónico.";
                header("Location: login.php");
                exit;
            }
        } else {
            // Almacena el mensaje de error en una variable de sesión
            $_SESSION['login_error'] = "Credenciales incorrectas";
            // Redirige al usuario a la página de inicio sin perder el mensaje de error
            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles2.css"> 
    <link rel="icon" href="./img/vota-si.png" />
    <script src="functions.js"></script>
    <script src="register.js"></script>
    <title>LOKE LEAGUE - Iniciar Sesión</title>
    <style>
        #formulario-container {
            position: relative;
        }

        #deslizar {
            position: absolute;
            top: calc(-18px - 20px); /* Calcula la posición desde la parte superior del contenedor */
            left: 0;
            color: var(--blanco);
            border: none;
            cursor: pointer;
            padding: 10px 20px; /* ajuste del tamaño del botón */
            font-size: 16px;
            z-index: 1; /* asegura que esté por encima del formulario */
            width: 44%; /* Ancho fijo del botón */
            transition: transform 0.5s ease-in-out, background 0.5s ease-in-out; /* Animación de transformación y de fondo */
            /* Definir el fondo inicial del botón */
            background: linear-gradient(to right, #5d9cec, #4b89da);
        }

        #deslizar.mostrar {
            transform: translateX(110%); /* Desplazar hacia la derecha */
            background: linear-gradient(to right, #ff9500, #ff5e3a); /* Cambia el color de fondo a otro tono de naranja cuando está en el estado "Iniciar Sesión" */
        }

        .formulario {
            display: none; /* Inicialmente ocultar el formulario de registro */
        }

        .formulario.mostrar {
            display: block; /* Mostrar formulario cuando la clase 'mostrar' está presente */
        }
        @media screen and (max-width: 1920px) {

            #deslizar {
                position: absolute;
                top: calc(-18px - 20px); /* Calcula la posición desde la parte superior del contenedor */
                left: 0;
                color: var(--blanco);
                border: none;
                cursor: pointer;
                padding: 10px 20px; /* ajuste del tamaño del botón */
                font-size: 16px;
                z-index: 1; /* asegura que esté por encima del formulario */
                width: 40.8%; /* Ancho fijo del botón */
                transition: transform 0.5s ease-in-out, background 0.5s ease-in-out; /* Animación de transformación y de fondo */
                /* Definir el fondo inicial del botón */
                background: linear-gradient(to right, #5d9cec, #4b89da);
            }
        }
            
    </style>
</head>

<body>
<?php if(isset($errorMessage)): ?>
        <div class="error-message"><?php echo $errorMessage; ?></div>
<?php endif; ?>
<?php include("components/header.php"); ?>
    <div id="formulario-container" class="contenedor-formulario contenedor">
        <button id="deslizar">Registrarse</button>

        <div class="imagen-formulario">
            <img src="https://cdn-icons-png.flaticon.com/512/6478/6478084.png" alt="Icono de inicio de sesión">
        </div>

        <!-- Formulario de inicio de sesión -->
        <form id="login-form" class="formulario mostrar" method="POST">
            <div class="texto-formulario">
                <h2>LOKE LEAGUE</h2>
                <h2>Iniciar Sesión</h2>
                <?php
                    // Muestra el mensaje de error almacenado en la variable de sesión
                    if (isset($_SESSION['login_error'])) {
                        echo '<p class="error-message">' . $_SESSION['login_error'] . '</p>';
                        unset($_SESSION['login_error']); // Limpia la variable de sesión después de mostrar el mensaje
                    }
                ?>
            </div>
            <div class="input">
                <label for="email">Correo electrónico:</label>
                <input type="email" placeholder="Ingresa tu correo electrónico" id="email" name="email" required>
            </div>
            <div class="input">
                <label for="password">Contraseña:</label>
                <input type="password" placeholder="Ingresa tu contraseña" id="password" name="password" required>
            </div>
            <div class="password-olvidada">
                <a href="recuperar_contrasena.php">¿Olvidaste tu contraseña?</a>
            </div>

            <div class="input">
                <input type="submit" value="Iniciar Sesión">
            </div>
        </form>

        <!-- Formulario de registro -->
        <form id="register-form" class="formulario" method="POST">
            <div class="texto-formulario">
                <h2>LOKE LEAGUE</h2>
                <h2>Regístrate</h2>
                <?php
                    // Muestra el mensaje de error almacenado en la variable de sesión
                    if (isset($_SESSION['register_error'])) {
                        echo '<p class="error-message">' . $_SESSION['register_error'] . '</p>';
                        unset($_SESSION['register_error']); // Limpia la variable de sesión después de mostrar el mensaje
                    }
                ?>
            </div>
            <div class="input">
                <label for="username">Nombre de usuario:</label>
                <input type="text" placeholder="Ingresa tu nombre de usuario" id="username" name="username" required>
            </div>
            <div class="input">
                <label for="password">Contraseña:</label>
                <input type="password" placeholder="Ingresa tu contraseña" id="password2" name="password" required>
            </div>
            <div class="input">
                <label for="confirmPassword">Confirma la contraseña:</label>
                <input type="password" placeholder="Confirma tu contraseña" id="confirmPassword2" name="confirmPassword" required>
            </div>
            <div class="input">
                <label for="email">Correo electrónico:</label>
                <input type="email" placeholder="Ingresa tu correo electrónico" id="email" name="email" required>
            </div>
            <div class="input">
                <input type="submit" value="Registrarse" name="register">
            </div>
        </form>

        <div id="notificationContainer"></div>
    </div>
<?php include("components/footer.php"); ?>

<script>
    document.getElementById("deslizar").addEventListener("click", function() {
        var boton = document.getElementById("deslizar");
        boton.classList.toggle("mostrar");
        var loginForm = document.getElementById("login-form");
        var registerForm = document.getElementById("register-form");
        if (boton.classList.contains("mostrar")) {
            boton.textContent = "Iniciar Sesión";
            loginForm.style.display = "none";
            registerForm.style.display = "block";
        } else {
            boton.textContent = "Registrarse";
            loginForm.style.display = "block";
            registerForm.style.display = "none";
        }
    });

    <?php if(isset($errorMessage)): ?>
            document.addEventListener("DOMContentLoaded", function() {
                // Muestra el mensaje emergente si existe un mensaje de error
                var mensajePopup = document.getElementById("mensajePopup");
                mensajePopup.innerText = "<?php echo $errorMessage; ?>";
                mensajePopup.style.display = "block";
            });
        <?php endif; ?>
        
        const errorMessage = document.querySelector('.error-message');

        // Si existe un mensaje de error
        if (errorMessage) {
            // Espera 10 segundos y luego oculta el mensaje
            setTimeout(function() {
                errorMessage.style.display = 'none';
            }, 5000); // 10000 milisegundos = 10 segundos
        }
</script>

</body>
</html>
