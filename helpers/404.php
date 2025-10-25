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
            padding-top: 26%;   
            background-image: url("../img/Tecnologias.png");
            background-repeat: no-repeat;    
            background-position: center 8%;  /* lo mantenemos más arriba */
            background-size: 40%;            /* ahora el logo es más chico */
        }

        .contenido {
            margin-top: 20px; /* separa el texto del logo */
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
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #fff;
            border-radius: 6px;
            font-size: 1.1em;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        a:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
    </style>
</head>
    <body>
        <div class="contenido">
            <h1>404</h1>
            <p>La página que buscas no existe.</p>
            <a href="../public/index.php">Volver al inicio</a>
        </div>
    </body>

</html>