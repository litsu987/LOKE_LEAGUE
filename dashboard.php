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


$username = $_SESSION['username'];

// Consulta para obtener el ID del usuario
$sql_get_user_id = "SELECT ID FROM users WHERE Username = :username";
$stmt_get_user_id = $pdo->prepare($sql_get_user_id);
$stmt_get_user_id->bindParam(':username', $username, PDO::PARAM_STR);
$stmt_get_user_id->execute();
$user = $stmt_get_user_id->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Manejar el caso en el que el usuario no exista
    echo "Error: El usuario no existe.";
    exit;
}

$userID = $user['ID']; // Obtener el ID del usuario

// Obtener las ligas actuales
$sql_get_ligas = "SELECT * FROM ligas";
$stmt_get_ligas = $pdo->prepare($sql_get_ligas);
$stmt_get_ligas->execute();
$ligas = $stmt_get_ligas->fetchAll(PDO::FETCH_ASSOC);


// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tipo_formulario']) && $_POST['tipo_formulario'] == 'crear_liga') {
    // Tu código para manejar el formulario de "Crear Liga" aquí
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
        $participantes = $_POST['numero_personas'];
        
        // Insertar los datos en la tabla ligas
        $insertLigaQuery = $pdo->prepare("INSERT INTO ligas (NombreLiga, NombreJuego, CreatorID, FechaCreacion, FechaFinalizacion, RandomLocke,NumeroParticipantes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertLigaQuery->execute([$nombreLiga, $nombreJuego, $creatorID, $fechaInicio, $fechaFin, $randomLocke, $participantes]);

        // Redirigir a alguna página después de agregar la liga (por ejemplo, la página de perfil del usuario)
        header("Location: dashboard.php");
        exit();
    }
}else{
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Recibir los datos del formulario
        $nombreTorneo = $_POST["nombre_torneo"];
        $modalidad = $_POST["modalidad"];
        $fechaInicio = $_POST["fecha_inicio"];
        $fechaFinalizacion = $_POST["fecha_finalizacion"];
    
        // Consulta para obtener el número máximo de participantes permitidos en la liga
        $sql_max_participantes = "SELECT NumeroParticipantes FROM ligas WHERE NombreLiga = :nombreTorneo";
        $stmt_max_participantes = $pdo->prepare($sql_max_participantes);
        $stmt_max_participantes->bindParam(':nombreTorneo', $nombreTorneo, PDO::PARAM_STR);
        $stmt_max_participantes->execute();
        $max_participantes_result = $stmt_max_participantes->fetch(PDO::FETCH_ASSOC);
        $max_participantes = $max_participantes_result['NumeroParticipantes'];
    
        // Consulta para obtener el número actual de participantes en la liga
        $sql_current_participantes = "SELECT COUNT(*) AS participantes_actuales FROM liga_usuarios WHERE LigaID = (SELECT ID FROM ligas WHERE NombreLiga = :nombreTorneo LIMIT 1)";
        $stmt_current_participantes = $pdo->prepare($sql_current_participantes);
        $stmt_current_participantes->bindParam(':nombreTorneo', $nombreTorneo, PDO::PARAM_STR);
        $stmt_current_participantes->execute();
        $current_participantes_result = $stmt_current_participantes->fetch(PDO::FETCH_ASSOC);
        $current_participantes = $current_participantes_result['participantes_actuales'];
    
        // Verificar si se ha alcanzado el número máximo de participantes
        if ($current_participantes >= $max_participantes) {
            header("Location: dashboard.php");
            exit;
        }
    
        // Si no se ha alcanzado el número máximo de participantes, proceder con la inserción del usuario
        $sql = "INSERT INTO liga_usuarios (LigaID, UserID) VALUES ((SELECT ID FROM ligas WHERE NombreLiga = :nombreTorneo LIMIT 1), :userID)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombreTorneo', $nombreTorneo, PDO::PARAM_STR);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
    
        // Redirigir a alguna página de éxito o hacer alguna otra acción
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
<style>


    .column {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                max-width: 1200px; /* Ancho máximo del contenedor principal */
                margin: 0 auto; /* Centrar el contenedor */
            }

        .liga-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 10px;
            padding: 20px;
            width: 25%; /* Ancho del 25% para mostrar 4 por fila */
            text-align: center;
            box-sizing: border-box; /* Incluye relleno y bordes en el ancho y alto */
             transition: transform 0.3s ease-in-out; /* Transición suave de la transformación */
            cursor: pointer; /* Cambia el cursor al pasar por encima */
            position: relative;
            
            
        }

        .liga-container:hover {
            transform: scale(1.05); /* Hace un zoom al hacer hover sobre el torneo */
        }

        .liga-container.zoomed {
            transform: scale(1.05); /* Aumenta el tamaño cuando está zoomed */
        }

        .liga-container:active {
            transform: scale(1); /* Vuelve al tamaño normal al hacer clic */
        }

        .liga-container img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 10px;
            max-height: 200px; /* Establece la altura máxima de las imágenes */
        }

        .info-label {
            font-weight: bold;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        .liga-info p {
    margin: 5px 0; /* Añade un pequeño margen arriba y abajo para separar los elementos */
    font-family: Arial, sans-serif; /* Cambia la fuente del texto */
    font-size: 16px; /* Cambia el tamaño de la fuente */
    color: #333; /* Cambia el color del texto */
}

.info-label {
    font-weight: bold;
}

.button-container {
    margin-top: 15px; /* Añade espacio entre la información del torneo y el botón */
}

.button-container button {
    font-family: Arial, sans-serif; /* Cambia la fuente del botón */
    font-size: 14px; /* Cambia el tamaño de la fuente del botón */
    font-weight: bold;
    color: white;
    background-color: #4CAF50;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.button-container button:hover {
    background-color: #45a049;
}

.liga-container.participating {
    background-color: rgba(128, 128, 128, 0.3); /* Gris con opacidad */
    pointer-events: none; /* Evita los eventos de puntero */
}

.liga-container {
    position: relative; /* Asegura que el posicionamiento del overlay sea relativo a este contenedor */
}

.overlay {
    position: absolute; /* Posicionamiento absoluto para que se pueda superponer */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(128, 128, 128, 0.5); /* Gris con opacidad */
    backdrop-filter: blur(0.5px); /* Ajusta el nivel de desenfoque */
    z-index: 1; /* Asegura que esté por encima del contenido */
    border-radius: 10px; /* Especifica esquinas redondeadas */
}

.modal {
    display: none; /* Por defecto, ocultar el modal */
    position: fixed; /* Posición fija para que se muestre encima del contenido */
    z-index: 1; /* Asegurar que el modal esté encima de otros elementos */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Fondo semitransparente */
    z-index: 9999;
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border-radius: 10px;
    max-width: 400px;
    position: relative;
}

.close {
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
}

.button-container {
    text-align: center;
}

button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    margin: 0 10px;
}

button:hover {
    background-color: #45a049;
}

</style>
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
                <h1 class="titulo">Unirse a Liga</h1>
            </div>
            <div class="elemento2" id="abrirContenido">
                <h1 class="titulo">Crear Liga</h1>
            </div>  
            <div class="elemento3">
                <h1 class="titulo">Mis Ligas</h1>
            </div>
        </div>
    </div>


    <div id="contenidoAdicional" class="popupContainer">
        <div class="popupContent">
            <span id="cerrarVentana" class="cerrarVentana">X</span>
            <form class="formulario" method="POST" >
                <input type="hidden" name="tipo_formulario" value="crear_liga">
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
                        <img id="imagenJuego" name="tipo" value="" src="">
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
                    <div class="input-container">
                        <label for="fecha_inicio">Fecha inicial:</label>
                        <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="input-container">
                        <label for="numero_personas">Participantes:</label>
                        <input type="number" id="numero_personas" name="numero_personas" required>
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
    // Consulta SQL para obtener todas las ligas ordenadas por número de participantes
    $sql = "SELECT * FROM ligas ORDER BY (SELECT COUNT(*) FROM liga_usuarios WHERE LigaID = ligas.ID) DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $ligas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="popupContent">
        <div class="popupContent2">
            <div class="title-container">
                <h2>Ligas disponibles</h2>
            </div>
            <div class="column">
                <span id="cerrarOtroContenido" class="cerrarVentana">X</span>
                <?php foreach ($ligas as $liga): ?>
                    <?php
                    // Verificar si el usuario está participando en la liga actual
                    $sql_check_participation = "SELECT COUNT(*) AS participacion FROM liga_usuarios WHERE LigaID = :ligaID AND UserID = :userID";
                    $stmt_check_participation = $pdo->prepare($sql_check_participation);
                    $stmt_check_participation->bindParam(':ligaID', $liga['ID'], PDO::PARAM_INT);
                    $stmt_check_participation->bindParam(':userID', $userID, PDO::PARAM_INT);
                    $stmt_check_participation->execute();
                    $participation_result = $stmt_check_participation->fetch(PDO::FETCH_ASSOC);
                    $participating = $participation_result['participacion'] > 0;

                   // Clase CSS adicional para resaltar la participación del usuario
                    $additionalClass = $participating ? 'participating' : '';

                    // Imagen predeterminada
                    $imagen = 'ruta de la imagen predeterminada';

                    // Definir la URL de la imagen según el NombreJuego de la liga
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


                    // Número total de participantes en la liga
                    $total_participantes = $liga['NumeroParticipantes'];

                    // Consulta para obtener el número de participantes actuales en esta liga
                    $sql_participantes_actuales = "SELECT COUNT(*) AS participantes_actuales FROM liga_usuarios WHERE LigaID = :ligaID";
                    $stmt_participantes_actuales = $pdo->prepare($sql_participantes_actuales);
                    $stmt_participantes_actuales->bindParam(':ligaID', $liga['ID'], PDO::PARAM_INT);
                    $stmt_participantes_actuales->execute();
                    $result_participantes_actuales = $stmt_participantes_actuales->fetch(PDO::FETCH_ASSOC);
                    $participantes_actuales = $result_participantes_actuales['participantes_actuales'];

                    if ($participantes_actuales == $total_participantes) {
                        // Si se alcanzó el límite, añadir la clase 'participating' y deshabilitar eventos de puntero
                        $additionalClass .= ' participating';
                    }
                    ?>
                    <!-- Div del torneo -->
                    <div class="liga-container <?php echo $additionalClass; ?>" onclick="openModal('<?php echo $liga['NombreLiga']; ?>', '<?php echo ($liga['RandomLocke'] == 0) ? "Normal" : "Random"; ?>', '<?php echo $liga['FechaCreacion']; ?>', '<?php echo $liga['FechaFinalizacion']; ?>')">
                        <?php if ($participating): ?>
                            <!-- Div para superponer cuando el usuario está participando -->
                            <div class="overlay"></div>
                        <?php endif; ?>
                        <!-- Formulario oculto -->
                        <form action="" method="post" id="form_<?php echo $liga['NombreLiga']; ?>" style="display: none;">
                            <input type="hidden" name="nombre_torneo" value="<?php echo $liga['NombreLiga']; ?>">
                            <input type="hidden" name="modalidad" value="<?php echo ($liga['RandomLocke'] == 0) ? "Normal" : "Random"; ?>">
                            <input type="hidden" name="fecha_inicio" value="<?php echo $liga['FechaCreacion']; ?>">
                            <input type="hidden" name="fecha_finalizacion" value="<?php echo $liga['FechaFinalizacion']; ?>">
                        </form>
                        <!-- Imagen -->
                        <img src="<?php echo $imagen; ?>" alt="<?php echo $liga['NombreJuego']; ?>">
                        <!-- Información del torneo -->
                        <div class="liga-info">
                            <p class="info-label">Nombre del Torneo:</p>
                            <p><?php echo $liga['NombreLiga']; ?></p>
                            <div>
                                <p class="info-label">Modalidad:</p>
                                <p><?php echo ($liga['RandomLocke'] == 0) ? "Normal" : "Random"; ?></p>
                            </div>
                            <div>
                                <p class="info-label">Fecha de Inicio:</p>
                                <p><?php echo $liga['FechaCreacion']; ?></p>
                            </div>
                            <div>
                                <p class="info-label">Fecha de Finalización:</p>
                                <p data-name="fecha-finalizacion"><?php echo $liga['FechaFinalizacion']; ?></p>
                            </div>
                            <div>
                                <p class="info-label">Total Participantes:</p>
                                <p><?php echo $total_participantes; ?></p>
                            </div>
                            <div>
                                <p class="info-label">Participantes Actuales:</p>
                                <p><?php echo $participantes_actuales; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
                </div>
        </div>
    </div>
    <div id="confirmationModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>¿Estás seguro de que deseas participar en este torneo?</p>
        <div class="button-container">
            <button id="confirmButton">Confirmar</button>
            <button id="cancelButton">Cancelar</button>
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

    document.addEventListener("DOMContentLoaded", function() {
        const imagenJuego = document.getElementById("imagenJuego");
        const nombreJuegoInput = document.getElementById("nombreJuego");

        const imagenesJuegos = [
            {
                url: 'https://images.wikidexcdn.net/mwuploads/wikidex/a/ac/latest/20211108122617/Car%C3%A1tula_de_Rojo_Fuego.png',
                name: "tipo",
                value: 'Rojo',
            },
            // Agrega más objetos de imagen aquí según sea necesario
        ];

        // Mostrar la primera imagen al cargar la página
        imagenJuego.src = imagenesJuegos[0].url;
        nombreJuegoInput.value = imagenesJuegos[0].value;

        // Agregar un evento de escucha para cambiar el valor del campo cuando cambie la imagen
        imagenJuego.addEventListener('click', function(event) {
            const imagenSeleccionada = imagenesJuegos.find(imagen => imagen.url === event.target.src);
            if (imagenSeleccionada) {
                nombreJuegoInput.value = imagenSeleccionada.value;
            }
        });
    });


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
            value: 'Rojo',

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
            }, 5000); // 10000 milisegundos = 10 segundos
        }


         // Función para cambiar la clase al hacer clic en el torneo
        function toggleZoom(event) {
            event.currentTarget.classList.toggle('zoomed');
        }
        // Función para abrir el modal y mostrar la confirmación
        var modalContent; // Declara modalContent como una variable global

    // Función para abrir el modal y mostrar la confirmación
        function openModal(nombreTorneo, modalidad, fechaInicio, fechaFinalizacion) {
            // Obtener el modal y mostrarlo
            var modal = document.getElementById("confirmationModal");
            modal.style.display = "block";

            // Establecer los detalles del torneo en el modal
            modalContent = modal.querySelector(".modal-content"); // Asigna modalContent en el ámbito global
            var modalText = modalContent.querySelector("p");
            modalText.innerHTML = "¿Estás seguro de que deseas participar en el torneo " + nombreTorneo + "?";

            // Guardar los detalles del torneo en el modal
            modalContent.dataset.nombreTorneo = nombreTorneo;
            modalContent.dataset.modalidad = modalidad;
            modalContent.dataset.fechaInicio = fechaInicio;
            modalContent.dataset.fechaFinalizacion = fechaFinalizacion;
            
            // Retornar modalContent para que esté disponible fuera de la función
            return modalContent;
        }


        function submitForm(event, nombreTorneo, modalidad, fechaInicio, fechaFinalizacion) {
            // Evitar que se envíe el formulario de manera convencional
            event.preventDefault();
            
            // Obtener el formulario asociado al torneo
            var form = document.getElementById("form_" + nombreTorneo);
            
            // Llenar los datos del formulario
            form.querySelector("[name='nombre_torneo']").value = nombreTorneo;
            form.querySelector("[name='modalidad']").value = modalidad;
            form.querySelector("[name='fecha_inicio']").value = fechaInicio;
            form.querySelector("[name='fecha_finalizacion']").value = fechaFinalizacion;
            
            // Enviar el formulario
            form.submit();
        }

        // Obtener el modal
        var modal = document.getElementById("confirmationModal");

        // Obtener el botón de cierre
        var span = document.getElementsByClassName("close")[0];

        // Obtener los botones de confirmar y cancelar
        var confirmButton = document.getElementById("confirmButton");
        var cancelButton = document.getElementById("cancelButton");

        // Cuando el usuario haga clic en el botón de cierre, ocultar el modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Cuando el usuario haga clic fuera del modal, ocultarlo
        function confirmForm(event, modalContent) {
            modalContent.style.display = "none"; // Ocultar el modal
            // Obtener los detalles del torneo desde el modal
            var nombreTorneo = modalContent.dataset.nombreTorneo;
            var modalidad = modalContent.dataset.modalidad;
            var fechaInicio = modalContent.dataset.fechaInicio;
            var fechaFinalizacion = modalContent.dataset.fechaFinalizacion;
            // Enviar el formulario
            submitForm(event, nombreTorneo, modalidad, fechaInicio, fechaFinalizacion);
        }

            // Cuando el usuario confirme, enviar el formulario
            // Cuando el usuario confirme, enviar el formulario
            confirmButton.onclick = function(event) {
            // Acceder a modalContent desde la variable global
            confirmForm(event, modalContent);
        }

        // Cuando el usuario confirme, enviar el formulario
        confirmButton.onclick = function(event) {
            modal.style.display = "none"; // Ocultar el modal
            // Obtener los detalles del torneo desde el modal
            var nombreTorneo = modalContent.dataset.nombreTorneo;
            var modalidad = modalContent.dataset.modalidad;
            var fechaInicio = modalContent.dataset.fechaInicio;
            var fechaFinalizacion = modalContent.dataset.fechaFinalizacion;
            // Enviar el formulario
            submitForm(event, nombreTorneo, modalidad, fechaInicio, fechaFinalizacion);
        }



        // Cuando el usuario cancele, simplemente ocultar el modal
        cancelButton.onclick = function() {
            modal.style.display = "none";
        }

        cancelButton.onclick = function() {
            modal.style.display = "none";
        }
        

        document.addEventListener("DOMContentLoaded", function() {
        // Obtener la fecha actual
        var fechaActual = new Date();

        // Obtener todos los contenedores de liga
        var ligaContainers = document.querySelectorAll('.liga-container');

        // Iterar sobre cada contenedor de liga
        ligaContainers.forEach(function(container) {
            // Obtener la fecha límite del torneo
            var fechaLimiteStr = container.querySelector('.liga-info [data-name="fecha-finalizacion"]').textContent;
            var fechaLimite = new Date(fechaLimiteStr);

            // Comparar la fecha límite con la fecha actual
            if (fechaLimite < fechaActual) {
                // Si la fecha límite es inferior a la actual, añadir la clase 'participating'
                container.classList.add('participating');
            }
        });
    });

        
    </script>
</body>
</html>
