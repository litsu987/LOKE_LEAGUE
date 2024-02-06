<?php
session_start();

// Verificar si la solicitud es de tipo POST
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
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
    escribirEnLog("[REGISTER] " . $e);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $destinatario = $_POST['email'];

    // Verificar si el correo está registrado
    $emailCheckQuery = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
    $emailCheckQuery->execute([$destinatario]);
    $userData = $emailCheckQuery->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        // Obtener la nueva contraseña del formulario
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_new_password'];

        // Verificar que las contraseñas coincidan
        if ($newPassword !== $confirmPassword) {
            // Contraseñas no coinciden, mostrar mensaje de error
            $_SESSION['recovery_error'] = "Las contraseñas no coinciden.";
        } else {
            // Cifrar la contraseña con hash sha512
            $hashedPassword = hash('sha512', $newPassword);

            // Actualizar la contraseña en la base de datos
            $updatePasswordQuery = $pdo->prepare("UPDATE Users SET Password = ? WHERE Email = ?");
            $updatePasswordQuery->execute([$hashedPassword, $destinatario]);

            // Enviar un correo de confirmación u otro mensaje si es necesario
            $_SESSION['recovery_success'] = "La contraseña se ha actualizado correctamente.";
        }
    } else {
        // El correo no está registrado, mostrar mensaje de error
        $_SESSION['recovery_error'] = "El correo electrónico no está registrado.";
    }
}



// Verificar si hay un mensaje de éxito o error almacenado en la sesión
$successMessage = isset($_SESSION['recovery_success']) ? $_SESSION['recovery_success'] : "";
$errorMessage = isset($_SESSION['recovery_error']) ? $_SESSION['recovery_error'] : "";

// Limpiar la sesión
unset($_SESSION['recovery_success']);
unset($_SESSION['recovery_error']);
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
    <script src="https://kit.fontawesome.com/8946387bf5.js" crossorigin="anonymous"></script>
    <title>LOKE LEAGUE - Recuperar</title>
</head>
<body>
    <?php include("components/header.php"); ?>
    <div class="contenedor-formulario contenedor">

         <div class="imagen-formulario">
            <img src="https://cdn-icons-png.flaticon.com/512/6478/6478084.png" alt="Icono de inicio de sesión">
        </div>
        <!-- Contenido del formulario de recuperación de contraseña -->
        <form class="formulario" method="POST" action="">
            <div class="texto-formulario">
                <h2>LOKE LEAGUE</h2>
                <h2>Recuperar Contraseña</h2>
                <?php
                // Muestra el mensaje de éxito o error almacenado en la variable de sesión
                if (!empty($successMessage)) {
                    echo '<p class="success-message" style="color: green;">' . $successMessage . '</p>';
                } elseif (!empty($errorMessage)) {
                    echo '<p class="error-message">' . $errorMessage . '</p>';
                }
                ?>
            </div>
            <div class="input">
                <label for="email">Correo electrónico:</label>
                <input type="email" placeholder="Ingresa tu correo electrónico" id="email" name="email" required>
            </div>
            <div class="input">
                <label for="new_password">Nueva Contraseña:</label>
                <input type="password" placeholder="Ingresa tu nueva contraseña" id="new_password" name="new_password" required>
            </div>
            <div class="input">
                <label for="confirm_new_password">Confirmar Contraseña:</label>
                <input type="password" placeholder="Confirma tu nueva contraseña" id="confirm_new_password" name="confirm_new_password" required>
            </div>
            <div class="input">
                <input type="submit" value="Recuperar Contraseña">
            </div>
        </form>
    </div>
    <?php include("components/footer.php"); ?>
</body>
</html>
