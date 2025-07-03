<?php
require_once __DIR__ . '/../helpers/loadenv.php';
loadEnv(__DIR__ . '/../.env'); // Asegúrate que la ruta es correcta

define('CIPHER_METHOD', 'AES-256-CBC');
define('SECRET_KEY', getenv('SECRET_KEY'));
define('SECRET_IV', getenv('SECRET_IV'));
