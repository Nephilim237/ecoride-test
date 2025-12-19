<?php

namespace Ecoride\Ecoride\Core;

use Ecoride\Ecoride\Models\UserModel;
use Ecoride\Ecoride\Models\VehicleModel;
use Ecoride\Ecoride\Services\AuthService;

class Controller
{
    protected AuthService $auth;
    protected Session $session;
    protected UserModel $userModel;
    private VehicleModel $vehicleModel;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->session = new Session();
        $this->userModel = new UserModel();
        $this->vehicleModel = new VehicleModel();
    }


    protected function renderView($view, $data = []): void
    {
        // Verifier le remember token au chargement de la page
        if (!$this->auth->is_logged_in()) {
            $this->auth->login_with_remember_token();
        }

        $currentUser = $this->auth->get_connected_user() ?? null;
        $user = $this->userModel->find_by_id($this->auth->get_connected_user_id() ?? 0);
        $userRoles = $this->userModel->get_user_roles($user->user_id ?? 0);
        $userNumberCars = $this->userModel->count_user_cars($user->user_id ?? 0);
        $isDriver = $this->userModel->is_driver($user->user_id ?? 0);
        $isPassenger = $this->userModel->is_passenger($user->user_id ?? 0);
        $userVehicles = $this->vehicleModel->get_user_vehicles($user->user_id ?? 0);

        $globalData = [
            'isLoggedIn' => $this->auth->is_logged_in(),
            'currentUser' => $currentUser,
            'user' => $user,
            'siteName' => APP_NAME,
            'userRoles' => $userRoles,
            'userNumberCars' => $userNumberCars,
            'isDriver' => $isDriver,
            'isPassenger' => $isPassenger,
            'userVehicles' => $userVehicles
        ];

        $viewData = array_merge($globalData, $data);

        extract($viewData);
        require __DIR__ . "/../Views/partials/header.php";
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . "/../Views/partials/footer.php";
    }

    protected function json($data, $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    protected function redirect(string $path, array $params = []): void
    {
        $url = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
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

    public function build_clear_filters_url(): string
    {
        $params = $_GET;
        unset($params['is_ecologic'], $params['prix_max'], $params['duree_max'], $params['note_min'], $params['url']);
        return url('carpool/search', $params);
    }

    public function build_remove_filter_url(string $filterName): string
    {
        $params = $_GET;
        unset($params[$filterName], $params['url']);
        return url('carpool/search', $params);
    }

}