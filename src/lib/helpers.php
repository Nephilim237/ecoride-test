<?php

function sanitize(mixed $value): string
{
    return stripslashes(htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8'));
}

function view_include($partial, $data = []): void
{
    extract($data);
    include __DIR__ . "/../Views/partials/$partial.php";
}

function provide_path(string $path = '', string $directory = null): string
{
    $directory = $directory !== null ? "$directory/" : '';
    return rtrim(BASE_URL, '/') . "/$directory" . ltrim($path, '/');
}

function redirect(string $path = '', array $params = []): void
{
    $url = provide_path($path);
    if (!empty($params)) {
        // Filtrer les parametres null ou vides (Facultatif)
        $filteredParams = array_filter($params, function ($value) {
            return $value !== null && $value !== '';
        });

        if (!empty($filteredParams)) {
            $url .= '?' . http_build_query($filteredParams);
        }
    }
    header("Location: $url");
    exit();
}

function assets(string $path): string
{
    return provide_path($path, 'assets');
}

function url(string $path = '', array $params = []): string
{
    $url = provide_path($path);
    if (!empty($params)) {
        // Filtrer les parametres null ou vides (Facultatif)
        $filteredParams = array_filter($params, function ($value) {
            return $value !== null && $value !== '';
        });

        if (!empty($filteredParams)) {
            $url .= '?' . http_build_query($filteredParams);
        }
    }

    return $url;
}

function fill_form(string $field, mixed $default = '')
{
    return $_POST[$field] ?? $default;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
