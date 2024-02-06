<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="styles2.css">
    <link rel="icon" href="./img/vota-si.png" />
    <script src="https://kit.fontawesome.com/8946387bf5.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="functions.js"></script>
    <script src="register.js"></script>
    <title>LOKE LEAGUE</title>
</head>
<body>
<?php
include("config.php");

try {
    $hostname = "localhost";
    $dbname = "lokeleague";
    $username = $dbUser;
    $pw = $dbPass;
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
} catch (PDOException $e) {
    echo "Failed to get DB handle: ". $e->getMessage();
    escribirEnLog("[DASHBOARD] ".$e);
    exit;
}

if (isset($_GET['validToken'])) {
    $validToken = $_GET['validToken'];

    // Verificar si el token está en la base de datos
    $tokenCheckQuery = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE ValidationToken = ?");
    $tokenCheckQuery->execute([$validToken]);
    $tokenCount = $tokenCheckQuery->fetchColumn();

    if ($tokenCount > 0) {
        // Actualizar la columna IsAuthenticated a 1
        $updateQuery = $pdo->prepare("UPDATE Users SET IsAuthenticated = 1 WHERE ValidationToken = ?");
        $updateQuery->execute([$validToken]);

        // Redirigir a http://localhost/LokeLeague/dashboard.php
        header("Location: login.php");
        exit();
    }
} 
?>


</body>
</html>
