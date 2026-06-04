<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function current_role(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}

function is_admin(): bool
{
    return current_role() === 'admin';
}

function is_pimpinan(): bool
{
    return current_role() === 'pimpinan';
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: index.php?page=login');
        exit;
    }
}

function require_login_action(): void
{
    if (!is_logged_in()) {
        header('Location: ../index.php?page=login');
        exit;
    }
}

function require_role(array $roles): void
{
    require_login();
    if (!in_array(current_role(), $roles, true)) {
        header('Location: index.php?page=dashboard&error=akses_ditolak');
        exit;
    }
}

function require_role_action(array $roles): void
{
    require_login_action();
    if (!in_array(current_role(), $roles, true)) {
        header('Location: ../index.php?page=dashboard&error=akses_ditolak');
        exit;
    }
}

function can_access_page(string $page): bool
{
    if (!is_logged_in()) {
        return $page === 'login';
    }

    if (is_admin()) {
        return true;
    }

    if (is_pimpinan()) {
        return in_array($page, ['dashboard', 'proses', 'hasil', 'laporan'], true);
    }

    return false;
}
