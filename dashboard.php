<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirigir a la página de inicio de sesión si no ha iniciado sesión
    exit();
}

// Obtener información del usuario desde la sesión (puedes obtener más datos según tu base de datos)
$userID = $_SESSION['username'];

// Aquí podrías realizar consultas a la base de datos para obtener más información sobre el usuario si es necesario
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./img/vota-si.png" />
    <title>LOKE LEAGUE - Dashboard</title>
</head>
<body>
    <?php include("components/header.php"); ?>
    <!--
    <section class="dashboardSection">
        <div id="notificationContainer"></div>
        <div class="dashboardContent">
            <div class="userInfoContainer">
                <img src="https://hips.hearstapps.com/hmg-prod/images/pokemon-rojo-azul-pelicula-live-action-1548079374.jpg" alt="Imagen de usuario" class="userImage">
                <h3 class="userName"><?php echo $userID; ?></h3>
                <div class="statusToggle">
                    <input type="checkbox" id="statusCheckbox">
                    <label for="statusCheckbox" class="toggle" title="Cambiar estado"></label>
                    <span class="toggleFeedback">Conectado</span>
                </div>
                <a href="#" onclick="openPopup('account_settings.php')" class="accountLink">Configurar cuenta</a>
                <a href="logout.php" class="logoutLink">Cerrar sesión</a>
            </div>
        </div>
    </section>
    -->
    <div class="centeredContainer"> 
            <div class="horizontalContainer">
                <div class="elemento1" id="abrirContenido">
                    <h1 class="titulo">Mis Ligas</h1>
                </div>
                <div class="elemento2">
                    <h1 class="titulo">Crear Liga</h1>
                </div>  
                <div class="elemento3">
                    <h1 class="titulo">Invitar a Liga</h1>
                </div>
            </div>
    </div>
   

    <div id="contenidoAdicional" class="popupContainer">
        <div class="popupContent">
            <!-- Aquí va el contenido que deseas mostrar -->
            <p>Este es el contenido adicional</p>
            <button id="cerrarContenido">Cerrar</button>
        </div>
    </div>
    </div>
    <div id="popupContainer" class="popupContainer">
        <div class="popupContent">
            <!-- Contenido de la ventana emergente -->
            <!-- Puedes agregar aquí los elementos que desees -->
            <h2>Configuración de cuenta</h2>
            <!-- Por ejemplo, un formulario para configurar la cuenta -->
            <form action="update_account.php" method="POST">
                <!-- Campos del formulario -->
            </form>
            <button onclick="closePopup()">Cerrar</button>
        </div>
    </div>

    <?php include("components/footer.php"); ?>

    <script>

        document.addEventListener("DOMContentLoaded", function() {
            const abrirContenido = document.getElementById("abrirContenido");
            const contenidoAdicional = document.getElementById("contenidoAdicional");
            const cerrarContenido = document.getElementById("cerrarContenido");

            // Mostrar contenido adicional al hacer clic en abrirContenido
            abrirContenido.addEventListener("click", function() {
                contenidoAdicional.style.display = "block";
            });

            // Ocultar contenido adicional al hacer clic en el botón de cerrar
            cerrarContenido.addEventListener("click", function() {
                contenidoAdicional.style.display = "none";
            });
        });


        var statusCheckbox = document.getElementById("statusCheckbox");
        var toggleFeedback = document.querySelector(".toggleFeedback");

        statusCheckbox.addEventListener("change", function() {
            if (statusCheckbox.checked) {
                toggleFeedback.textContent = "Ocupado";
            } else {
                toggleFeedback.textContent = "Conectado";
            }
        });

        function openPopup(url) {
            var popupContainer = document.getElementById("popupContainer");
            popupContainer.classList.add("active"); // Muestra el div emergente
        }

        function closePopup() {
            var popupContainer = document.getElementById("popupContainer");
            popupContainer.classList.remove("active"); // Oculta el div emergente
        }

    </script>
</body>
</html>
