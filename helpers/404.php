<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página no encontrada - 404</title>
    <style>
        body {
            background: #f8f8f8;
            color: #333;
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 10%;
        }
        h1 {
            font-size: 5em;
            margin-bottom: 0.2em;
        }
        p {
            font-size: 1.5em;
            margin-bottom: 1em;
        }
        a {
            color: #007bff;
            text-decoration: none;
            font-size: 1.2em;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>404</h1>
    <p>La página que buscas no existe.</p>
    <a href="/">Volver al inicio</a>
</body>
</html>