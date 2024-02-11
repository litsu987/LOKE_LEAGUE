<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirigir a la página de inicio de sesión si no ha iniciado sesión
    exit();
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

// Obtener información del usuario desde la sesión
$username = $_SESSION['username'];

// Consulta para obtener el ID del usuario
$sql_get_user_id = "SELECT ID FROM users WHERE Username = :username";
$stmt_get_user_id = $pdo->prepare($sql_get_user_id);
$stmt_get_user_id->bindParam(':username', $username, PDO::PARAM_STR);
$stmt_get_user_id->execute();
$user = $stmt_get_user_id->fetch(PDO::FETCH_ASSOC);

// Verificar si se encontró el usuario
if (!$user) {
    // Manejar el caso en el que el usuario no exista
    echo "Error: El usuario no existe.";
    exit;
}

$userID = $user['ID']; // Obtener el ID del usuario

// Obtener las ligas actuales
$sql_get_ligas = "SELECT l.*, 
                  (SELECT COUNT(*) FROM liga_usuarios lu WHERE lu.LigaID = l.ID) AS NumeroParticipantes
                  FROM ligas l
                  ORDER BY NumeroParticipantes DESC";

$stmt_get_ligas = $pdo->prepare($sql_get_ligas);
$stmt_get_ligas->execute();
$ligas = $stmt_get_ligas->fetchAll(PDO::FETCH_ASSOC);

// Verificar si se ha enviado un formulario mediante POST
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
        header("Location: test.php");
        exit;
    }

    // Si no se ha alcanzado el número máximo de participantes, proceder con la inserción del usuario
    $sql = "INSERT INTO liga_usuarios (LigaID, UserID) VALUES ((SELECT ID FROM ligas WHERE NombreLiga = :nombreTorneo LIMIT 1), :userID)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nombreTorneo', $nombreTorneo, PDO::PARAM_STR);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();

    // Redirigir a alguna página de éxito o hacer alguna otra acción
    header("Location: test.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torneos</title>
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
</head>
<body>
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
    <script>
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
