<?php

namespace API;

use PDOException;
use API\Logger;
use API\Environement;
use API\View;
use Exception;
use Error;

/**
 * Singleton to initiate database connection
 * 
 */
class Database
{
    private static $instance;
    private static $handler;
    private $logger;

    private function __construct()
    {
        $this->logger = Logger::getInstance();

        // Loads .env
        Environement::init();

        try
        {
            // Connect to MySQL/MariaDB server
            self::$handler = new \PDO(
                'mysql:host='.$_ENV['DB_HOST'].';
                       port='.$_ENV['DB_PORT'].';
                       charset=utf8mb4',
                       $_ENV['DB_USER'],
                       $_ENV['DB_PASSWORD']
            );

            // Using database
            self::$handler->exec('USE `'.$_ENV['DB_NAME'].'`');
        }
        
        catch(PDOException $e)
        {
            if(isset($e->errorInfo) && $e->errorInfo[1] === 1049) // Error 1049 : Unknown database
            {
                // Try to create database and table
                $this->init();
            }

            else
            {
                $this->logger->emergency('Caught unhandled PDOException : '.$e->getMessage());
                View::serverError();
            }
        }
    }
    
    /**
     * Singleton instanciation
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if(null === self::$instance) {
            self::$instance = new Database();  
        }
      
        return self::$instance;
    }
    
    /**
     * Getter for the PDO handler
     * 
     * @return PDO
     */
    public function handler(): \PDO
    {
        return self::$handler;
    }
    
    /**
     * Tries to create the database or table or both
     * 
     * @param string $type (optional) [table|database|user] What to create
     * @return void
     */
    public function init(string $type = 'all'): void
    {
        try
        {
            switch($type)
            {   
                case 'table': // Create only the missing table

                    // More info on why I used binary(16) for GUID
                    // https://stackoverflow.com/questions/2365132/uuid-performance-in-mysql/#answer-7578500
                    // https://stitcher.io/blog/optimised-uuids-in-mysql
                    $query = 'CREATE TABLE IF NOT EXISTS users (id BINARY(16) PRIMARY KEY, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, INDEX names (firstname, lastname))';
                    $successMessage = 'Created table users';
                    break;

                case 'database': // Create only the missing database
                    $query = 'CREATE DATABASE IF NOT EXISTS `'.$_ENV['DB_NAME'].'` CHARACTER SET utf8mb4; USE `'.$_ENV['DB_NAME'].'`';
                    $successMessage = 'Created database '.$_ENV['DB_NAME'];
                    break;

                case 'all': // Create both
                default :
                    $query = 'CREATE DATABASE IF NOT EXISTS `'.$_ENV['DB_NAME'].'` CHARACTER SET utf8mb4'.
                             '; USE `'.$_ENV['DB_NAME'].'`'.
                             '; CREATE TABLE IF NOT EXISTS users (id BINARY(16) PRIMARY KEY, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, INDEX names (firstname, lastname))';
                    $successMessage = 'Created database '.$_ENV['DB_NAME'].' and table users in one query';
            }

            self::$handler->exec($query);
            $this->logger->info($successMessage);
        }

        catch(PDOException $e)
        {            
            if(isset($e->errorInfo) && ($e->errorInfo[1] === 1044 || $e->errorInfo[1] === 1142))
            // Error 1044 Access denied for user
            // Error 1142 : CREATE command denied to user
            {
                $this->logger->emergency('Cannot access database "'.$_ENV['DB_NAME'].'" with user "'.$_ENV['DB_USER'].'". Make sure the database exists or that the user has the CREATE privilege.');
            }

            else
            {
                $this->logger->emergency('Caught unhandled PDOException : '.$e->getMessage());
            }

            View::serverError();
        }

        // Just in case
        catch(Exception $e)
        {
            $this->logger->emergency('Caught unhandled Exception : '.$e->getMessage());
            View::serverError();
        }

        // Never too sure
        catch(Error $e)
        {
            $this->logger->emergency('Caught unhandled Error : '.$e->getMessage());
            View::serverError();
        }
    }
}