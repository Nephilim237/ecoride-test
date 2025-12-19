<?php

require_once '../vendor/autoload.php';
require_once '../config/config.php';

use Ecoride\Ecoride\Core\Router;
use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\MongoManager;

// Initialisation de la base de donnees
$db = Database::getInstance();
$mongoDB = MongoManager::getInstance();

// Initialisation du Router
$router = new Router();

// Creation des Routes
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@login');
$router->post('/login/handle', 'AuthController@handleLogin');
$router->get('/register', 'AuthController@register');
$router->post('/register/handle', 'AuthController@handleRegister');
$router->get('/trajets', 'RideController@index');
$router->get('/logout', 'AuthController@logout');

//$router->get('/trajets/recherche', 'RideController@search');
//$router->post('/trajets/creer', 'RideController@create');
//$router->get('/profil', 'UserController@profile');

$router->dispatch();
