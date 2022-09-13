<?php

require_once __DIR__.'/../../vendor/autoload.php';

use API\Router;
use API\Users;
use API\View;

$users = new Users;

$routes = [
    ['GET', '/users', [$users, 'get'] ],
    ['GET', '/users/', [$users, 'get'] ],
    ['POST', '/user/create', [$users, 'create'] ],
    ['POST', '/user/create/', [$users, 'create'] ],
    ['GET', '/user/{id:[0-9a-f]{32}}', [$users, 'get'] ],
    ['POST', '/user/update/{id:[0-9a-f]{32}}', [$users, 'update'] ],
    ['DELETE', '/user/delete/{id:[0-9a-f]{32}}', [$users, 'delete'] ],
];

$router = new Router($routes);
$router->dispatch();

View::render();