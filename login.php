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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
</head>
<body>
<?php include("components/header.php"); ?>
    <div class="contenedor-formulario contenedor">
        <div class="imagen-formulario">
            <img src="https://cdn-icons-png.flaticon.com/512/6478/6478084.png" alt="Icono de inicio de sesión">
        </div>


        <form class="formulario" method="POST">
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
        <div id="notificationContainer"></div>
    </div>
<?php include("components/footer.php"); ?>
</body>
</html>

