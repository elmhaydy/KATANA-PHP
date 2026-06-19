<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================
   AUTH / SESSION HELPERS
   ============================ */

/**
 * Vérifie si l'utilisateur est connecté
 */
if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool {
        return isset($_SESSION['user']);
    }
}

/**
 * Récupère l'utilisateur courant
 */
if (!function_exists('current_user')) {
    function current_user(): ?array {
        return $_SESSION['user'] ?? null;
    }
}

/**
 * Vérifie si admin
 */
if (!function_exists('is_admin')) {
    function is_admin(): bool {
        return isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'ROLE_ADMIN';
    }
}

/**
 * Oblige la connexion
 */
if (!function_exists('require_login')) {
    function require_login(): void {
        if (!is_logged_in()) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg'  => 'Veuillez vous connecter.'
            ];
            header("Location: /mini-ecommerce/php/public/login.php");
            exit;
        }
    }
}

/**
 * Protection admin (403)
 */
if (!function_exists('require_admin')) {
    function require_admin(): void {
        if (!is_admin()) {
            http_response_code(403);
            require __DIR__ . "/../public/403.php";
            exit;
        }
    }
}

/**
 * ✅ Owner rule
 * - USER → uniquement ses données
 * - ADMIN → tout
 */
if (!function_exists('require_owner_or_admin')) {
    function require_owner_or_admin(int $ownerUserId): void {
        $u = current_user();

        if (!$u) {
            require_login();
        }

        if (!is_admin() && (int)$u['id'] !== (int)$ownerUserId) {
            http_response_code(403);
            require __DIR__ . "/../public/403.php";
            exit;
        }
    }
}

/* ============================
   FLASH MESSAGES
   ============================ */

if (!function_exists('set_flash')) {
    function set_flash(string $type, string $msg): void {
        $_SESSION['flash'] = [
            'type' => $type,
            'msg'  => $msg
        ];
    }
}

if (!function_exists('get_flash')) {
    function get_flash(): ?array {
        if (!isset($_SESSION['flash'])) {
            return null;
        }
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
}
