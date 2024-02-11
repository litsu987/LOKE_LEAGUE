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

// Obtener información del usuario desde la sesión (puedes obtener más datos según tu base de datos)
$userID = $_SESSION['username'];

// Obtener las ligas actuales
$sql = "SELECT * FROM ligas";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$ligas = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    </style>
</head>
<body>
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
</body>
</html>
