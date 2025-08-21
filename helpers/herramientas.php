<?php
function generarToken($usuarioId)
{
    $ahora = $ahora ?:new DateTime('now', new DateTimeZone('America/Tijuana'));
    $fechaCode = deFechaANombre($ahora->format('Ymdhis'));
    $prefix = $fechaCode . sprintf('%06d', (int)$usuarioId);//20 caracteres
    $secret = bin2hex(random_bytes(16));
    $token = $prefix . '-' . $secret;

    return $token;
}

function deFechaANombre($fecha)
{
    // Tabla de equivalencias: 0=A, 1=B, ..., 9=J
    static $mapa = [
        '0' => 'A', '1' => 'B', '2' => 'C', '3' => 'D', '4' => 'E',
        '5' => 'F', '6' => 'G', '7' => 'H', '8' => 'I', '9' => 'J'
    ];
    $resultado = '';
    $len = strlen($fecha);
    // Recorre cada caracter de la fecha
    for ($i = 0; $i < $len; $i++) {
        $ch = $fecha[$i];
        $resultado .= $mapa[$ch] ?? '?'; // Si no es número, pone "?"
    }
    return $resultado;
}

function parcearToken($token)
{
    if(!preg_match('/^([A-J]{20})-([a-f0-9]{32})$/i', $token, $m)) {
        return null; // Token no válido
    }
    // Extrae la fecha codificada y el secreto
    return [$m[1], $m[2]];
}
