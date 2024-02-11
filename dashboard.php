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
    
    // Verificar si ya existe una liga asociada con el usuario actual
    $checkLeagueQuery = $pdo->prepare("SELECT COUNT(*) FROM ligas WHERE CreatorID = ?");
    $checkLeagueQuery->execute([$creatorID]);
    $leagueCount = $checkLeagueQuery->fetchColumn();

    if ($leagueCount > 0) {
        $errorMessage = "Error: Ya tienes una liga creada.";
    } else {
        // Si no existe una liga asociada con el usuario, procede a insertar la nueva liga
        // Obtener los datos del formulario
        $nombreLiga = $_POST['nombre'];
        $nombreJuego = $_POST['nombreJuego']; 
        $fechaInicio = $_POST['fecha_inicio'];
        $fechaFin = $_POST['fecha_fin'];
        $randomLocke = isset($_POST['randomlocke']) ? true : false;
        
        // Insertar los datos en la tabla ligas
        $insertLigaQuery = $pdo->prepare("INSERT INTO ligas (NombreLiga, NombreJuego, CreatorID, FechaCreacion, FechaFinalizacion, RandomLocke) VALUES (?, ?, ?, ?, ?, ?)");
        $insertLigaQuery->execute([$nombreLiga, $nombreJuego, $creatorID, $fechaInicio, $fechaFin, $randomLocke]);

        // Redirigir a alguna página después de agregar la liga (por ejemplo, la página de perfil del usuario)
        header("Location: dashboard.php");
        exit();
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
    <title>LOKE LEAGUE - Dashboard</title>
</head>

<body>

    <?php include("components/header.php"); ?>

    <?php if(isset($errorMessage)): ?>
        <div class="error-message"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
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
            <div class="elemento1" id="abrirOtroContenido">
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
            <span id="cerrarVentana" class="cerrarVentana">X</span>
            <form class="formulario" method="POST" >
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
                    <!-- Div que contendrá las imágenes del juego -->
                    <div id="contenedorImagenes">
                        <!-- Imagen inicial -->
                        <span class="flecha" id="flechaIzquierda">&#10094;</span>
                        <img id="imagenJuego" src="https://images.wikidexcdn.net/mwuploads/wikidex/a/ac/latest/20211108122617/Car%C3%A1tula_de_Rojo_Fuego.png" alt="Rojo Fuego y Verde Hoja" data-nombre="Rojo Fuego y Verde Hoja">
                        <span class="flecha" id="flechaDerecha">&#10095;</span>
                    </div>

                    <div class="input" >
                        <div class="statusToggle">
                            <input type="checkbox" id="randomlocke" name="randomlocke">
                            <label for="randomlocke" class="customToggle"></label>
                            <span class="toggleFeedback">Normal</span>
                        </div>
                    </div>
                    
                </div>
                
                <div class="input fechas">
                    <div class="input-container	">
                        <label for="fecha_inicio">Fecha inicial:</label>
                        <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="input-container">
                        <label for="fecha_fin">Fecha final:</label>
                        <input type="datetime-local" id="fecha_fin" name="fecha_fin" required>
                    </div>
                </div>
                <div class="input">
                    <input type="hidden" id="nombreJuego" name="nombreJuego">
                    <input type="submit" value="Crear Liga">
                </div>
            </form>
        </div>
    </div>
    
    <div id="otroContenido" class="popupContainer">
        <?php
        $sql = "SELECT * FROM ligas";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $ligas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="popupContent">
        <span id="cerrarOtroContenido" class="cerrarVentana">X</span>
        <h1 style="text-align: center;">Torneos</h1>
        <div class="column">
            <?php foreach ($ligas as $liga): ?>
                <?php
                $imagen = '';
                switch ($liga['NombreJuego']) {
                    case 'Rojo':
                        $imagen = 'https://images.wikidexcdn.net/mwuploads/wikidex/a/ac/latest/20211108122617/Car%C3%A1tula_de_Rojo_Fuego.png';
                        break;
                    case 'Esmeralda':
                        $imagen = 'https://images.wikidexcdn.net/mwuploads/wikidex/0/02/latest/20211108123052/Caratula_Esmeralda.jpg';
                        break;
                    case 'Platino':
                        $imagen = 'https://images.wikidexcdn.net/mwuploads/wikidex/f/f4/latest/20211108120853/Car%C3%A1tula_Pok%C3%A9mon_Platino_%28ESP%29.png';
                        break;
                    case 'SoulSilver':
                        $imagen = 'https://images.wikidexcdn.net/mwuploads/wikidex/5/5c/latest/20211108121148/Pok%C3%A9mon_Edici%C3%B3n_Plata_SoulSilver_car%C3%A1tula_ES.jpg';
                        break;
                    case 'Negro':
                        $imagen = 'https://images.wikidexcdn.net/mwuploads/wikidex/9/94/latest/20211117102637/Pok%C3%A9mon_Edici%C3%B3n_Negra.png';
                        break;
                    case 'Negro2':
                        $imagen = 'https://images.wikidexcdn.net/mwuploads/wikidex/1/17/latest/20140218002530/Box_Pok%C3%A9mon_Blanco_2.png';
                        break;
                    case 'Y':
                        $imagen = 'https://images.wikidexcdn.net/mwuploads/wikidex/6/6f/latest/20130621162348/Pok%C3%A9mon_Y_Car%C3%A1tula.png';
                        break;
                    case 'omega':
                        $imagen = 'https://images.wikidexcdn.net/mwuploads/wikidex/5/5d/latest/20150827182103/Car%C3%A1tula_Pok%C3%A9mon_Rub%C3%AD_Omega.png';
                        break;
                    default:
                        $imagen = 'ruta de la imagen predeterminada';
                        break;
                }
                ?>
                <div class="liga-container">
                    <img src="<?php echo $imagen; ?>" alt="<?php echo $liga['NombreJuego']; ?>">
                    <div>
                        <p class="info-label">Nombre del Torneo:</p>
                        <p><?php echo $liga['NombreLiga']; ?></p>
                    </div>
                    <?php if ($liga['RandomLocke'] == 0): ?>
                        <div>
                            <p class="info-label">Modalidad:</p>
                            <p>Normal</p>
                        </div>
                    <?php elseif ($liga['RandomLocke'] == 1): ?>
                        <div>
                            <p class="info-label">Modalidad:</p>
                            <p>Random</p>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="info-label">Fecha de Inicio:</p>
                        <p><?php echo $liga['FechaCreacion']; ?></p>
                    </div>
                    <div>
                        <p class="info-label">Fecha de Finalización:</p>
                        <p><?php echo $liga['FechaFinalizacion']; ?></p>
                    </div>
                    <button>Unirme al torneo</button>
                </div>
            <?php endforeach; ?>
        </div>
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


    <script>
       document.addEventListener('DOMContentLoaded', function() {
        // Obtener la fecha y hora actual en el formato YYYY-MM-DDTHH:MM (formato datetime-local)
        const now = new Date();
        const year = now.getFullYear();
        const month = ('0' + (now.getMonth() + 1)).slice(-2); // Añadir 1 porque los meses se indexan desde 0
        const day = ('0' + now.getDate()).slice(-2);
        const hours = ('0' + now.getHours()).slice(-2);
        const minutes = ('0' + now.getMinutes()).slice(-2);
        const datetime = `${year}-${month}-${day}T${hours}:${minutes}`;
        
        // Establecer la fecha y hora actual como el valor por defecto para el input de fecha inicial
        document.getElementById('fecha_inicio').value = datetime;
    });

    const imagenJuego = document.getElementById('imagenJuego');
    const flechaIzquierda = document.getElementById('flechaIzquierda');
    const flechaDerecha = document.getElementById('flechaDerecha');

    const imagenesJuegos = [
        {
            url: 'https://images.wikidexcdn.net/mwuploads/wikidex/a/ac/latest/20211108122617/Car%C3%A1tula_de_Rojo_Fuego.png',
            name:"tipo",
            value: 'Rojo Fuego y Verde Hoja',

        },
        {
            url: 'https://images.wikidexcdn.net/mwuploads/wikidex/0/02/latest/20211108123052/Caratula_Esmeralda.jpg',
            name:"tipo",
            value: 'Esmeralda',
        },
        {
            url: 'https://images.wikidexcdn.net/mwuploads/wikidex/f/f4/latest/20211108120853/Car%C3%A1tula_Pok%C3%A9mon_Platino_%28ESP%29.png',
            name:"tipo",
            value: 'Platino',
        },
        {
            url: 'https://images.wikidexcdn.net/mwuploads/wikidex/5/5c/latest/20211108121148/Pok%C3%A9mon_Edici%C3%B3n_Plata_SoulSilver_car%C3%A1tula_ES.jpg',
            name:"tipo",
            value: 'SoulSilver',
        },
        {
            url: 'https://images.wikidexcdn.net/mwuploads/wikidex/9/94/latest/20211117102637/Pok%C3%A9mon_Edici%C3%B3n_Negra.png',
            name:"tipo",
            value: 'Negro',
        },
        {
            url: 'https://images.wikidexcdn.net/mwuploads/wikidex/1/17/latest/20140218002530/Box_Pok%C3%A9mon_Blanco_2.png',
            name:"tipo",
            value: 'Negro2',
        },
        {
            url: 'https://images.wikidexcdn.net/mwuploads/wikidex/6/6f/latest/20130621162348/Pok%C3%A9mon_Y_Car%C3%A1tula.png',
            name:"tipo",
            value: 'Y',
        },
        {
            url: 'https://images.wikidexcdn.net/mwuploads/wikidex/5/5d/latest/20150827182103/Car%C3%A1tula_Pok%C3%A9mon_Rub%C3%AD_Omega.png',
            name:"tipo",
            value: 'Omega',
        },
    ];

    // Índice actual de la imagen mostrada
    let indiceImagenActual = 0;

    // Función para cambiar la imagen a la izquierda
   // Función para cambiar la imagen a la izquierda
    // Función para cambiar la imagen a la izquierda
    // Función para cambiar la imagen a la izquierda
    // Función para cambiar la imagen a la izquierda
    function cambiarImagenIzquierda() {
        // Decrementar el índice circularmente
        indiceImagenActual = (indiceImagenActual - 1 + imagenesJuegos.length) % imagenesJuegos.length;
        // Obtener la URL y el alt de la nueva imagen
        const { url, alt, value } = imagenesJuegos[indiceImagenActual];
        // Cambiar la fuente, el alt y el nombre del juego de la imagen actual
        imagenJuego.src = url;
        imagenJuego.alt = alt;
        // Actualizar el valor del input hidden con el nombre del juego
        document.getElementById('nombreJuego').value = value;
    }

    // Función para cambiar la imagen a la derecha
    function cambiarImagenDerecha() {
        // Incrementar el índice circularmente
        indiceImagenActual = (indiceImagenActual + 1) % imagenesJuegos.length;
        // Obtener la URL y el alt de la nueva imagen
        const { url, alt, value } = imagenesJuegos[indiceImagenActual];
        // Cambiar la fuente, el alt y el nombre del juego de la imagen actual
        imagenJuego.src = url;
        imagenJuego.alt = alt;
        // Actualizar el valor del input hidden con el nombre del juego
        document.getElementById('nombreJuego').value = value;
    }

    // Agregar evento de escucha para detectar las teclas de flecha
    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowLeft') {
            cambiarImagenIzquierda(); // Cambiar imagen a la izquierda al presionar la tecla de flecha izquierda
        } else if (event.key === 'ArrowRight') {
            cambiarImagenDerecha(); // Cambiar imagen a la derecha al presionar la tecla de flecha derecha
        }
    });


    // Agregar eventos de clic a las flechas para cambiar la imagen
    flechaIzquierda.addEventListener('click', cambiarImagenIzquierda);
    flechaDerecha.addEventListener('click', cambiarImagenDerecha);

        document.addEventListener("DOMContentLoaded", function() {
            const cerrarOtroContenido = document.getElementById("cerrarOtroContenido"); // Cambiado el ID
            const otroContenido = document.getElementById("otroContenido");

            cerrarOtroContenido.addEventListener("click", function() { // Cambiado el ID
                // Oculta el contenido cuando se hace clic en el botón
                otroContenido.style.display = "none";
            });
        });


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

        document.addEventListener("DOMContentLoaded", function() {
            const abrirOtroContenido = document.getElementById("abrirOtroContenido");
            const otroContenido = document.getElementById("otroContenido");

            abrirOtroContenido.addEventListener("click", function() {
                // Muestra el contenido cuando se hace clic
                otroContenido.style.display = "block";
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
            }, 10000); // 10000 milisegundos = 10 segundos
        }

        
    </script>
</body>
</html>
