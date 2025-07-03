<?php
require_once __DIR__ . '/../config/secret.php';

function encriptar($dato) {
    return base64_encode(
        openssl_encrypt($dato, CIPHER_METHOD, SECRET_KEY, 0, SECRET_IV)
    );
}

function desencriptar($dato_encriptado) {
    return openssl_decrypt(
        base64_decode($dato_encriptado), CIPHER_METHOD, SECRET_KEY, 0, SECRET_IV
    );
}
