<?php

use API\Router;

require_once __DIR__.'/../../vendor/autoload.php';

$routes = [
    ['GET', '/users', function() { echo 'Getting list of user filtered by firstname or lastname'; } ],
    ['GET', '/user/create', function() { echo 'Creating new user'; } ],
    ['GET', '/user/{id:\d+}', function($data) { echo 'Fetching user with id '.$data['id']; } ],
    ['GET', '/user/update/{id:\d+}', function($data) { echo 'Updating user with id '.$data['id']; } ],
    ['GET', '/user/delete/{id:\d+}', function($data) { echo 'Deleting user with id '.$data['id']; } ],
];

$router = new Router($routes);
$router->dispatch();