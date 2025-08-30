<?php
/**
 * Genera un token de restablecimiento.
 * Formato: [fechaCodificada(14)] + [usuarioId(6)] + '-' + [hex(32)]
 * Ej.: A...J000123-4f8a... (total 53 chars)
 */
function generarToken(int $usuarioId, ?DateTimeInterface $ahora = null, string $tz = 'America/Tijuana'): string
{
    // Normaliza $ahora y usa DateTimeImmutable para evitar mutaciones
    if ($ahora === null) {
        $ahora = new DateTimeImmutable('now', new DateTimeZone($tz));
    } elseif (!($ahora instanceof DateTimeImmutable)) {
        $ahora = DateTimeImmutable::createFromInterface($ahora);
    }

    // Usa 24h (H) para evitar ambigüedad 12h
    $fechaNum  = $ahora->format('YmdHis'); // 14 dígitos
    $fechaCode = deFechaANombre($fechaNum); // A-J para 0-9 → 14 letras

    // 6 posiciones para el ID (con ceros a la izquierda)
    $idPadded = str_pad((string)$usuarioId, 6, '0', STR_PAD_LEFT);

    // Prefijo de 20 caracteres
    $prefix = $fechaCode . $idPadded;

    // Secreto criptográficamente aleatorio: 16 bytes → 32 hex
    $secret = bin2hex(random_bytes(16));

    return $prefix . '-' . $secret; // 20 + 1 + 32 = 53 chars
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
