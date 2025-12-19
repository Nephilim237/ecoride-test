<?php

require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../src/lib/helpers.php';

use Ecoride\Ecoride\Core\Router;
use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\MongoManager;
use Whoops\run;
use Whoops\Handler\PrettyPageHandler;

$whoops = new Run();
$whoops->pushHandler(new PrettyPageHandler);

$envirenment = $_ENV['APP_ENV'] ?? 'development';
if ($envirenment = 'development') {

    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    $whoops->register();
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

$whoops->register();


// Initialisation de la base de donnees
$db = Database::getInstance();
$mongoDB = MongoManager::getInstance();

try {
    // Initialisation du Router
    $router = new Router();

    // Creation des Routes
    $router
        ->get('/', 'HomeController@index')
        ->get('/login', 'AuthController@login')
        ->post('/login/handle', 'AuthController@handle_login')
        ->get('/register', 'AuthController@register')
        ->post('/register/handle', 'AuthController@handle_register')
        ->get('/profile', 'UserController@profile')
        ->get('/become-passenger', 'PartnerController@become_passenger')
        ->get('/become-partner', 'PartnerController@become_partner')
        ->post('/become-partner/handle', 'PartnerController@handle_become_partner')
        ->get('/add-car', 'VehicleController@index')
        ->post('/add-car/handle', 'VehicleController@handle_add_car')
        ->post('/add-preference', 'UserController@add_preference')
        ->get('/carpool', 'CarpoolController@index')
        ->get('/carpool/search', 'CarpoolController@search')
        ->get('/carpool/autocomplete', 'CarpoolController@autocomplete')
        ->get('/logout', 'AuthController@logout');

    $router->dispatch();
} catch (Throwable $e) {
    if ($envirenment = 'development') {
        throw $e;
    } else {
        error_log("Erreur: {$e->getMessage()} dans {$e->getFile()} : {$e->getLine()}");
        http_response_code(500);
        echo "Une erreur s'est produite. Notre equipe y travaille.";
    }
}
