<?php

namespace Ecoride\Ecoride\Core;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function set_session(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public function get_session(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function update_session(string $key, mixed $value): void
    {
        // Si une cle n'existe pas on l'ajoute
        if (!isset($_SESSION['$key'])) {
            $_SESSION[$key] = $value;
            return;
        }

        // Si la cle existe et que la nouvelle valeur est un tableau
        if (is_array($value) && is_array($_SESSION[$key])) {
            // Fusion recursive: les nouvelles cles sont ajoutees et les cles existantes sont fusionnees
            $_SESSION[$key] = $this->array_merge_recursive_distinct($_SESSION[$key], $value);
        } else {
            // Si les valeurs ne sont pas les tableaux, on met a jour
            if ($_SESSION[$key] !== $value) {
                $_SESSION[$key] = $value;
            }
        }
    }

    private function array_merge_recursive_distinct(array $array1, array $array2): array
    {
        $merged = $array1;
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                // Fusion recursive si les deux valeurs sont des tableaux
                $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
            } else {
                // Sinon, ajoute ou met a jour la nouvelle cle
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    public function set_errors(string $key, array $errorsData): void
    {
        $this->set_session($key, $errorsData);
    }

    public function get_errors(string $key): array {
        $data = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return $data ?? [];
    }

    public function remove_session(string $key): void {
        unset($_SESSION[$key]);
    }

    public function destroy_session(): void {
        session_destroy();
    }

    public function has_session(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public function set_flash(string $type, string $message): void {
        $_SESSION['flash'][$type] = $message;
    }

    public function get_flash($type): string {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    public function has_flash(string $type): bool {
        return isset($_SESSION['flash'][$type]);
    }
}