<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('ensureCsrfToken')) {
    function ensureCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validateCsrfToken')) {
    function validateCsrfToken(?string $token): bool
    {
        $current = $_SESSION['csrf_token'] ?? '';
        if (!is_string($current) || !is_string($token) || $token === '') {
            return false;
        }
        return hash_equals($current, $token);
    }
}

if (!function_exists('rotateCsrfToken')) {
    function rotateCsrfToken(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('resetLoginThrottle')) {
    function resetLoginThrottle(): void
    {
        unset($_SESSION['login_lock_expires'], $_SESSION['login_failures']);
    }
}

if (!function_exists('registerLoginFailure')) {
    function registerLoginFailure(int $maxAttempts, int $lockSeconds): void
    {
        $failures = (int)($_SESSION['login_failures'] ?? 0);
        $failures++;
        $_SESSION['login_failures'] = $failures;
        if ($failures >= $maxAttempts) {
            $_SESSION['login_lock_expires'] = time() + $lockSeconds;
        }
    }
}

if (!function_exists('isLoginLocked')) {
    function isLoginLocked(): bool
    {
        $expires = $_SESSION['login_lock_expires'] ?? 0;
        if (is_numeric($expires) && (int)$expires > time()) {
            return true;
        }
        if ($expires && (int)$expires <= time()) {
            resetLoginThrottle();
        }
        return false;
    }
}
