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
    <title>LOKE LEAGUE - Registro</title>
</head>
<body>
<?php
session_start();
?>
<?php include("components/header.php"); ?>
    <div class="contenedor-formulario contenedor">
        <div class="imagen-formulario">
            <img src="https://cdn-icons-png.flaticon.com/512/6478/6478084.png" alt="Icono de inicio de sesión">
        </div>

        <form class="formulario" method="POST">
            <div class="texto-formulario">
                <h2>LOKE LEAGUE</h2>
                <h2>Regístrate</h2>
                <?php
                
                // Muestra el mensaje de error almacenado en la variable de sesión
                if (isset($_SESSION['register_error'])) {
                    echo '<p>' . $_SESSION['register_error'] . '</p>';
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
                <input type="password" placeholder="Ingresa tu contraseña" id="password" name="password" required>
            </div>

            <div class="input">
                <label for="confirmPassword">Confirma la contraseña:</label>
                <input type="password" placeholder="Confirma tu contraseña" id="confirmPassword" name="confirmPassword" required>
            </div>

            <div class="input">
                <label for="email">Correo electrónico:</label>
                <input type="email" placeholder="Ingresa tu correo electrónico" id="email" name="email" required>
            </div>

            <div class="input">
                <input type="submit" value="Registrarse">
            </div>
        </form>

        <div id="notificationContainer"></div>
    </div>
<?php include("components/footer.php"); ?>

<?php




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
    $username = $_POST['username'];
    $password = hash('sha512', $_POST['password']);

    // Verificar si el correo ya está registrado
    $emailCheckQuery = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE Email = ?");
    $emailCheckQuery->execute([$destinatario]);
    $emailCount = $emailCheckQuery->fetchColumn();

    if ($emailCount > 0) {
        echo '<script>showNotification("error", "El correo electrónico ya está registrado");</script>';
    } else {
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
        echo '<script>showNotification("error", "Token antes de la inserción: "' . $token . PHP_EOL.'");</script>';
       

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
}
?>


</body>
</html>
