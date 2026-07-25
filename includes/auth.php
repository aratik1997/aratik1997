<?php

define('ADMIN_FILE', __DIR__ . '/../data/admin.json');

function admin_exists(): bool {
    return file_exists(ADMIN_FILE);
}

function create_admin(string $username, string $password): void {
    $payload = [
        'username' => $username,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ];
    file_put_contents(ADMIN_FILE, json_encode($payload, JSON_PRETTY_PRINT));
}

function verify_admin(string $username, string $password): bool {
    if (!admin_exists()) {
        return false;
    }
    $admin = json_decode(file_get_contents(ADMIN_FILE), true);
    if (!$admin || !hash_equals($admin['username'], $username)) {
        return false;
    }
    return password_verify($password, $admin['password_hash']);
}

function start_admin_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in(): bool {
    start_admin_session();
    return !empty($_SESSION['admin_logged_in']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}
