<?php

function notfound(): void {
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/../views/error/404.php';
    exit();
}

function serverError() {
    header("HTTP/1.0 500 Internal Server Error");
    include __DIR__ . '/../views/error/500.php';
    exit();
}

function forbidden() {
    header("HTTP/1.0 403 Internal Server Error");
    include __DIR__ . '/../views/error/403.php';
    exit();
}

function showError(string $message = '', string $title = 'Erreur') {
    $error_title = $title;
    $error_message = $message;
    include __DIR__ . '/../views/error/main.php';
    exit();
}