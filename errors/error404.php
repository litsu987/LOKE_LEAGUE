<?php
        header("Location: ./error404.php");
        http_response_code(404);
?><!DOCTYPE html>
<html lang="es">

<head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="./img/vota-si.png" />
        <title>Error 403</title>
        <link href="https://fonts.googleapis.com/css?family=Montserrat:700,900" rel="stylesheet">
        <link type="text/css" rel="stylesheet" href="./styles.css" />
</head>

<body>

        <div id="notfound">
                <div class="notfound">
                        <div class="notfound-404">
                                <h1>404</h1>
                                <h2>Aqui no hay nada</h2>
                        </div>
                        <a href="./">Volver</a>
                </div>
        </div>

</body>

</html>