<?php

function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        echo "Método no permitido.";
        exit;
    }
}

function int_param(string $key, int $default = 0, int $source = INPUT_GET): int {
    $v = filter_input($source, $key, FILTER_VALIDATE_INT);
    return ($v === false || $v === null) ? $default : (int)$v;
}
