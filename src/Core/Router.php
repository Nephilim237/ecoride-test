<?php

namespace Ecoride\Ecoride\Core;

class Router
{
    private array $routes = [];

    public function get($path, $callback): static
    {
        $this->routes['GET'][$path] = $callback;
        return $this;
    }

    public function post($path, $callback): static
    {
        $this->routes['POST'][$path] = $callback;
        return $this;
    }

    public function dispatch() {
        $url = $_GET['url'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];

        // REcuperer tous les parametre GET de l'URL
        $params = $this->getAllParams();

        foreach($this->routes[$method] as $path => $callback) {
            if ($this->matchRoute($path, $url)) {
                return $this->executeCallback($callback, $params);
            }
        }

        return $this->handle_not_found('Aucune Route trouvee.');
    }

    private function matchRoute($route, $url): bool
    {
        $route = trim($route, '/');
        $url = trim($url, '/');

        return $route === $url;
    }

    private function executeCallback($callback, array $params = []) {
        if (is_callable($callback)) {
            return call_user_func($callback, $params);
        }

        if(is_string($callback)) {
            list($controllerName, $method) = explode('@', $callback); //CarpoolModel@search
            $controller = "Ecoride\\Ecoride\\Controllers\\$controllerName";

            if(class_exists($controller)) {
                $controllerInstance = new $controller();
                if (method_exists($controllerInstance, $method)) {
                    return $controllerInstance->$method();
                }

                return $this->handle_server_error('Methode introuvable');
            }

            return $this->handle_server_error('Controleur introuvable');
        }

        // Si callback invalide, alors, echec
        return $this->handle_server_error("Callback Invalide.");
    }

    private function getAllParams(): array {
        $params = [];

        foreach ($_GET as $key => $value) {
            $params[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return $params;
    }

    private function handle_not_found(string $message = ''): bool
    {
        require_once __DIR__ .'/../Controllers/ErrorController.php';
        //Facultatif
        error_log($message);
        notFound();
        http_response_code(404);
        $this->renderView('error/404');
        return false;
    }

    public function handle_server_error(string $message): bool
    {
        require_once __DIR__ . '/../Controllers/ErrorController.php';
        error_log($message);
        serverError();
        http_response_code(500);
        $this->renderView('error/500');
        return false;

    }

    public function renderView($view, $data = []): bool
    {
        extract($data);
        require_once "../src/Views/$view.php";
        return true;
    }

}