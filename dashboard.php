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


// Verificar si el usuario ha iniciado sesión


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

// Obtener el ID del usuario actual usando el nombre de usuario de la sesión
$creatorUsername = $_SESSION['username'];
$getUserIDQuery = $pdo->prepare("SELECT ID FROM users WHERE Username = ?");
$getUserIDQuery->execute([$creatorUsername]);
$creatorID = $getUserIDQuery->fetchColumn();

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $nombreLiga = $_POST['nombre'];
    $nombreJuego = $_POST['tipo'];
    $fechaInicio = $_POST['fecha_inicio'];
    $fechaFin = $_POST['fecha_fin'];
    $randomLocke = isset($_POST['randomlocke']) ? 1 : 0; // Convertir el valor del checkbox a 1 o 0

    // Insertar los datos en la tabla ligas
    $insertLigaQuery = $pdo->prepare("INSERT INTO ligas (NombreLiga, NombreJuego, CreatorID, FechaCreacion, FechaFinalizacion, RandomLocke) VALUES (?, ?, ?, ?, ?, ?)");
    $insertLigaQuery->execute([$nombreLiga, $nombreJuego, $creatorID, $fechaInicio, $fechaFin, $randomLocke]);

    // Redirigir a alguna página después de agregar la liga (por ejemplo, la página de perfil del usuario)
    header("Location: dashboard.php");
    exit();
}
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
<style>
    .imagenes-tipos {
        display: flex;
        flex-wrap: wrap;
        gap: 10px; /* Espacio entre las imágenes */
    }

    .imagenes-tipos label {
        flex: 0 0 calc(25% - 20px); /* Cambia el 33.33% por 25% para tener 4 columnas */
        margin-bottom: 20px;
        text-align: center;
    }

    .imagenes-tipos img {
        width: 150px; /* Tamaño de las imágenes */
        height: auto;
        border: 2px solid transparent;
        cursor: pointer;
    }

    .imagenes-tipos input[type="radio"] {
        display: none;
    }

    .imagenes-tipos input[type="radio"]:checked + label img {
        border-color: #007bff; /* Cambia el color del borde a un tono de azul */
        border-width: 4px; /* Ajusta el grosor del borde */
    }

    @media screen and (max-width: 1920px) {
    .imagenes-tipos {
        display: flex;
        flex-wrap: wrap;
        gap: 0; /* Sin espacio entre las imágenes */
    }

    .imagenes-tipos label {
        flex: 0 0 calc(25% - 20px); /* Cambia el 33.33% por 25% para tener 4 columnas */
        margin-bottom: 0; /* Elimina el margen inferior */
        text-align: center;
    }

    .imagenes-tipos input[type="radio"] {
        display: none;
    }

    .imagenes-tipos input[type="radio"]:checked + label img {
        border-color: #007bff; /* Cambia el color del borde a un tono de azul */
        border-width: 4px; /* Ajusta el grosor del borde */
    }
}



</style>
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
            <div class="elemento1">
                <h1 class="titulo">Mis Ligas</h1>
            </div>
            <div class="elemento2" id="abrirContenido">
                <h1 class="titulo">Crear Liga</h1>
            </div>  
            <div class="elemento3">
                <h1 class="titulo">Invitar a Liga</h1>
            </div>
        </div>
    </div>


    <div id="contenidoAdicional" class="popupContainer">
        <div class="popupContent">
            <span id="cerrarVentana">X</span>
            <form class="formulario" method="POST">
                <div class="texto-formulario">
                    <h2>Crear Liga</h2>
                    <!-- Puedes agregar un mensaje de error si lo necesitas -->
                </div>
                <div class="input">
                    <label for="nombre">Nombre de la liga:</label>
                    <input type="text" placeholder="Ingresa el nombre de la liga" id="nombre" name="nombre" required>
                </div>
                <div class="input">
                    <label>Selecciona el Juego:</label>
                    <div class="imagenes-tipos">
                        
                        <input type="radio" id="rojofuego" name="tipo" value="Rojo Fuego y Verde Hoja">
                        <label for="rojofuego"><img src="https://images.wikidexcdn.net/mwuploads/wikidex/a/ac/latest/20211108122617/Car%C3%A1tula_de_Rojo_Fuego.png" alt="Rojo Fuego y Verde Hoja"></label>

                        <input type="radio" id="Esmeralda" name="tipo" value="Esmeralda">
                        <label for="Esmeralda"><img src="https://images.wikidexcdn.net/mwuploads/wikidex/0/02/latest/20211108123052/Caratula_Esmeralda.jpg" alt="Platino"></label>

                        <input type="radio" id="Platino" name="tipo" value="Platino">
                        <label for="Platino"><img src="https://images.wikidexcdn.net/mwuploads/wikidex/f/f4/latest/20211108120853/Car%C3%A1tula_Pok%C3%A9mon_Platino_%28ESP%29.png" alt="Platino"></label>

                        <input type="radio" id="HeartGoldSoulSilver" name="tipo" value="HeartGold y SoulSilver">
                        <label for="HeartGoldSoulSilver"><img src="https://images.wikidexcdn.net/mwuploads/wikidex/5/5c/latest/20211108121148/Pok%C3%A9mon_Edici%C3%B3n_Plata_SoulSilver_car%C3%A1tula_ES.jpg" alt="HeartGold y SoulSilver"></label>

                        <input type="radio" id="Negro y Blanco" name="tipo" value="Negro y Blanco">
                        <label for="Negro y Blanco"><img src="https://images.wikidexcdn.net/mwuploads/wikidex/9/94/latest/20211117102637/Pok%C3%A9mon_Edici%C3%B3n_Negra.png" alt="Negro y Blanco"></label>

                        <input type="radio" id="Negro2 y Blanco2" name="tipo" value="Negro2 y Blanco2">
                        <label for="Negro2 y Blanco2"><img src="https://images.wikidexcdn.net/mwuploads/wikidex/1/17/latest/20140218002530/Box_Pok%C3%A9mon_Blanco_2.png" alt="Negro2 y Blanco2"></label>

                            
                        <input type="radio" id="X y Y" name="tipo" value="X y Y">
                        <label for="X y Y"><img src="https://images.wikidexcdn.net/mwuploads/wikidex/6/6f/latest/20130621162348/Pok%C3%A9mon_Y_Car%C3%A1tula.png" alt="X y Y"></label>

                            
                        <input type="radio" id="Rubí Omega y Zafiro Alfa" name="tipo" value="Rubí Omega y Zafiro Alfa">
                        <label for="Rubí Omega y Zafiro Alfa"><img src="https://images.wikidexcdn.net/mwuploads/wikidex/5/5d/latest/20150827182103/Car%C3%A1tula_Pok%C3%A9mon_Rub%C3%AD_Omega.png" alt="Rubí Omega y Zafiro Alfa"></label>
                        
                        <!-- Agrega más imágenes y opciones según sea necesario -->
                    </div>
                </div>
                    <div class="input">
                    <label>Modalidad de Juego:</label>
                    <div class="statusToggle">
                        <input type="checkbox" id="randomlocke" name="randomlocke">
                        <label for="randomlocke" class="toggle" title="Cambiar estado"></label>
                        <span class="toggleFeedback">Normal</span>
                    </div>
                </div>
                <div class="input fechas">
                    <div class="input-container">
                        <label for="fecha_inicio">Fecha inicial:</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="input-container">
                        <label for="fecha_fin">Fecha final:</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" required>
                    </div>
                </div>


                <div class="input">
                    <input type="submit" value="Crear Liga">
                </div>
        </form>
            </form>
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


        var statusCheckbox = document.getElementById("randomlocke");
        var toggleFeedback = document.querySelector(".toggleFeedback");

        statusCheckbox.addEventListener("change", function() {
            if (statusCheckbox.checked) {
                toggleFeedback.textContent = "Random";
            } else {
                toggleFeedback.textContent = "Normal";
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

        var cerrarVentana = document.getElementById('cerrarVentana');

        // Agregar un evento de clic al botón de cierre
        cerrarVentana.addEventListener('click', function() {
            // Ocultar la ventana emergente al hacer clic en el botón de cierre
            document.getElementById('contenidoAdicional').style.display = 'none';
        });

        // Obtener todos los campos de entrada del formulario
        const formInputs = document.querySelectorAll('.formulario input');

        // Agregar un evento de escucha a cada campo de entrada
        formInputs.forEach(input => {
            input.addEventListener('input', () => {
                // Obtener el siguiente campo de entrada
                const nextInput = input.parentElement.nextElementSibling.querySelector('input');
                
                // Verificar si hay un siguiente campo de entrada
                if (nextInput) {
                    // Hacer scroll automáticamente hasta el siguiente campo de entrada
                    nextInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });


    </script>
</body>
</html>
