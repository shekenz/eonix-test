<?php

include_once __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

try
{
    // New log channel
    $log = new Logger('api');
    $log->pushHandler(new StreamHandler(__DIR__.'/../api.log', Level::Debug));

    // Loading .env configuration
    $dotenv = Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->load();

    // Database connection
    $dbHandler = new PDO('mysql:host='.$_ENV['DB_HOST'].';port='.$_ENV['DB_PORT'].';charset=utf8mb4', $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);

    // We deactivate momentary PDOException report to detetect manually if database exists
    $dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    // Try to use database
    $dbHandler->exec('USE '.$_ENV['DB_NAME']);

    // Handling manually missing database error
    if($dbHandler->errorCode() !== '00000')
    {
        if($dbHandler->errorInfo()[1] === 1049)
        {
            // Setting PDOException back on in case something else goes wrong with the query
            $dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // Creating database
            $dbHandler->query('CREATE DATABASE IF NOT EXISTS '.$_ENV['DB_NAME']);
            $log->info('Database "'.$_ENV['DB_NAME'].'" created.');
        }

        else
        {
            // Manually throwing exception on unhandled errors
            throw new PDOException();
        }
    }
    
    // Setting PDOException back on in case something else goes wrong later on
    $dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $log->info('Database "'.$_ENV['DB_NAME'].'" found.');
}

catch(PDOException $e)
{
    // Handling permission denied exceptions
    if(isset($e->errorInfo) && $e->errorInfo[1] === 1044)
    {
        // REFACTOR Return error 500
        $log->error('Cannot access database "'.$_ENV['DB_NAME'].'" with user "'.$_ENV['DB_USER'].'". Make sure the database exists or that the user has the CREATE privilege.');
    }

    else
    {
        // REFACTOR Return error 500
        $log->error('Caught unhandled PDOException : '.$e->getMessage());
    }
}

catch(InvalidPathException $e)
{
    // REFACTOR Return error 500
    $log->error($e->getMessage());
    $log->info('Make sure you copied .env.example to .env and correctly edited it.');
}

