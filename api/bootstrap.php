<?php

include_once __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

try
{
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
            // REFACTOR Log to monolog
            echo 'Database "'.$_ENV['DB_NAME'].'" created.<br>';
    
        }

        else
        {
            // Manually throwing exception on unhandled errors
            throw new PDOException();
        }
    }
    
    // Setting PDOException back on in case something else goes wrong later on
    $dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // REFACTOR Log to monolog
    echo 'Database "'.$_ENV['DB_NAME'].'" found.<br>';
}

catch(PDOException $e)
{
    // Handling permission denied exceptions
    if(isset($e->errorInfo) && $e->errorInfo[1] === 1044)
    {
        // REFACTOR Log to monolog and return error 500
        echo 'Cannot access database "'.$_ENV['DB_NAME'].'" with user "'.$_ENV['DB_USER'].'". Make sure the database exists or that the user has the <b>CREATE</b> privilege.<br>';
    }

    else
    {
        // REFACTOR Log to monolog and return error 500
        echo '<b>Caught unhandled PDOException : </b>'.$e->getMessage();
    }
}

catch(InvalidPathException $e)
{
    // REFACTOR Log to monolog and return error 500
    echo $e->getMessage().'<br>';
    echo 'Make sure you copied .env.example to .env and correctly edited it.';
}

