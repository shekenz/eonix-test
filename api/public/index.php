<?php

require_once __DIR__.'/../../vendor/autoload.php';

use API\Router;
use API\Users;
use API\View;

// Checking for application/json data-type
if($_SERVER['CONTENT_TYPE'] !== 'application/json')
{
    View::wrongContentType();
}

// Instanciate Users controller with JSON data;
$users = new Users(json_decode(file_get_contents('php://input'), true));

// Request route
$routes = [
    ['POST', '/users', [$users, 'get'] ],
    ['POST', '/users/', [$users, 'get'] ],
    ['PUT', '/user/create', [$users, 'create'] ],
    ['PUT', '/user/create/', [$users, 'create'] ],
    ['POST', '/user/{id:[0-9a-f]{32}}', [$users, 'get'] ],
    ['PATCH', '/user/update/{id:[0-9a-f]{32}}', [$users, 'update'] ],
    ['DELETE', '/user/delete/{id:[0-9a-f]{32}}', [$users, 'delete'] ],
];

$router = new Router($routes);
$router->dispatch();

// Close output buffer and sends final page with headers and data
View::render();