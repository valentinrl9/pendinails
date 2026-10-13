<?php

function pendinails_host(): string
{
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    return (string)preg_replace('/:\d+$/', '', $host);
}

function pendinails_is_production(): bool
{
    $host = pendinails_host();
    return $host === 'pendinails.com' || $host === 'www.pendinails.com';
}

function pendinails_request_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }
    $proto = strtolower(trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
    if ($proto === 'https') {
        return true;
    }
    return stripos((string)($_SERVER['HTTP_CF_VISITOR'] ?? ''), 'https') !== false;
}

function pendinails_public_scheme(): string
{
    if (pendinails_is_production() || pendinails_request_is_https()) {
        return 'https';
    }
    return 'http';
}

function pendinails_https_redirect_target(): ?string
{
    if (!pendinails_is_production()) {
        return null;
    }

    $needsHttps = !pendinails_request_is_https();
    $needsApex = pendinails_host() === 'www.pendinails.com';
    if (!$needsHttps && !$needsApex) {
        return null;
    }

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if (!is_string($uri) || $uri === '' || $uri[0] !== '/') {
        $uri = '/';
    }
    $uri = str_replace(["\r", "\n"], '', $uri);

    return 'https://pendinails.com' . $uri;
}

function pendinails_force_https(): void
{
    $target = pendinails_https_redirect_target();
    if ($target === null || headers_sent()) {
        return;
    }

    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $code = ($method === 'GET' || $method === 'HEAD') ? 301 : 308;
    header('Location: ' . $target, true, $code);
    exit;
}

function pendinails_send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('Content-Security-Policy: upgrade-insecure-requests');

    if (pendinails_request_is_https()) {
        header('Strict-Transport-Security: max-age=86400');
    }
}

function pendinails_bootstrap(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => pendinails_is_production() || pendinails_request_is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    pendinails_send_security_headers();
    pendinails_force_https();
}
