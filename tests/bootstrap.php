<?php

require __DIR__ . '/../vendor/autoload.php';

// Sessions (optional; only if some tests touch $_SESSION)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    $_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(16));
}