<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles2.css">
    <link rel="icon" href="./img/vota-si.png" />
    <title>LOKE LEAGUE</title>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            padding: 10px;
            color: white;
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            padding: 8px 15px; /* Ajusta el relleno para que parezcan botones */
            border: 1px solid white; /* Agrega un borde blanco */
            border-radius: 5px; /* Añade esquinas redondeadas */
            transition: background-color 0.3s; /* Agrega transición al cambio de color de fondo */
        }

        header #logo {
            order: -1;
            margin-right: auto; /* Alinea "LOKE LEAGUE" a la izquierda */
        }

        header a:hover {
            background-color: white;
            color: #333;
        }

        header .login-register {
            display: flex;
            margin-left: auto;
        }

        header .login-register a {
            margin-left: 15px;
        }
    </style>
</head>
<body>
    <?php
    // Verificar si el usuario ha iniciado sesión
    if (isset($_SESSION['user_id'])) {
        // Obtener el nombre de usuario desde la sesión
        $username = $_SESSION['username'];
        // Mostrar enlace al dashboard con el nombre del usuario
        echo '<header>';
        echo '<a href="index.php" id="logo">LOKE LEAGUE</a>';
        echo '<div class="login-register">';
        echo '<a href="dashboard.php">' . $username . '</a>';
        echo '<a href="logout.php">CERRAR SESIÓN</a>';
        echo '</div>';
        echo '</header>';
    } else {
        // Mostrar enlaces estándar de Iniciar Sesión y Registrarse si no ha iniciado sesión
        echo '<header>';
        echo '<a href="index.php" id="logo">LOKE LEAGUE</a>';
        echo '<div class="login-register">';
        echo '<a href="login.php">INICIAR SESIÓN</a>';
        echo '</div>';
        echo '</header>';
    }
    ?>
</body>
</html>
