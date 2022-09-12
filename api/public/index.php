<?php

require_once __DIR__.'/../../vendor/autoload.php';

use API\Router;
use API\Users;

$users = new Users;

$routes = [
    ['GET', '/users', [$users, 'get'] ],
    ['GET', '/user/create', function() { echo 'Creating new user'; } ],
    ['GET', '/user/{id:[0-9a-f]{32}}', [$users, 'get'] ],
    ['GET', '/user/update/{id:\d+}', function($data) { echo 'Updating user with id '.$data['id']; } ],
    ['GET', '/user/delete/{id:\d+}', function($data) { echo 'Deleting user with id '.$data['id']; } ],
];

$router = new Router($routes);
$router->dispatch();