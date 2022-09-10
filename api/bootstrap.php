<?php

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

try
{
    // New log channel
    global $log;
    $log = new Logger('api');
    $log->pushHandler(new StreamHandler(__DIR__.'/../api.log', Level::Debug));

    // Loading .env configuration
    $dotenv = Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->load();

    // Database connection
    $dbHandler = new PDO('mysql:host='.$_ENV['DB_HOST'].';port='.$_ENV['DB_PORT'].';charset=utf8mb4', $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);

    // Check database
    $dbHandler->query('CREATE DATABASE IF NOT EXISTS '.$_ENV['DB_NAME']);

    // Use database
    $dbHandler->exec('USE '.$_ENV['DB_NAME']);

    // Check user table

    // NOTE : It is not a good idea to store UUID as a primary index, it is bad for performance.
    // More info here : https://stackoverflow.com/questions/2365132/uuid-performance-in-mysql/#answer-7578500
    // Since it is required by the exercice, I'm storing them as binary for better optimization, as explained here :
    // https://stitcher.io/blog/optimised-uuids-in-mysql
    $dbHandler->query('CREATE TABLE IF NOT EXISTS users (id binary(36), firstname varchar(255), lastname varchar(255))');

}

catch(PDOException $e)
{
    // Handling permission denied exceptions
    if(isset($e->errorInfo) && $e->errorInfo[1] === 1044) // Error 1044 : Access denied for user '%s'@'%s' to database '%s'
    {
        // REFACTOR Redirect error 500
        $log->error('Cannot access database "'.$_ENV['DB_NAME'].'" with user "'.$_ENV['DB_USER'].'". Make sure the database exists or that the user has the CREATE privilege.');
    }

    else
    {
        // REFACTOR Redirect error 500
        $log->error('Caught unhandled PDOException : '.$e->getMessage());
    }
}

catch(InvalidPathException $e)
{
    // REFACTOR Redirect error 500
    $log->error($e->getMessage());
    $log->info('Make sure you copied .env.example to .env and correctly edited it.');
}

