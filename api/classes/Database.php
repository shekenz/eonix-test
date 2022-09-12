<?php

namespace API;

use PDOException;
use API\Logger;
use API\Environement;
use API\View;
use Exception;
use Error;

/**
 * Database
 * 
 * Singleton to initiate database connection
 * 
 */
class Database
{
    private static $instance;
    private static $handler;
    private $logger;
    private $fatalErrors;

    private function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->fatalErrors = false;

        Environement::init();

        try
        {
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
                $this->init();
            }

            else
            {
                $this->logger->emergency('Caught unhandled PDOException : '.$e->getMessage());
                View::serverError(true);
            }
        }
    }

    public static function getInstance(): self
    {
        if(null === self::$instance) {
            self::$instance = new Database();  
        }
      
        return self::$instance;
    }
    
    /**
     * handler
     *
     * Getter for the PDO handler
     * 
     * @return PDO
     */
    public function handler(): \PDO
    {
        return self::$handler;
    }
    
    /**
     * init
     *
     * Tries to create the database and table
     * 
     * @return void
     */
    public function init(string $type = 'all'): void
    {
        try
        {
            $query = '';
            $successMessage = '';

            switch($type)
            {   
                case 'table': // Create only the missing table
                    // More info on why I used binary(16) for GUID
                    // https://stackoverflow.com/questions/2365132/uuid-performance-in-mysql/#answer-7578500
                    // https://stitcher.io/blog/optimised-uuids-in-mysql
                    $query = 'CREATE TABLE IF NOT EXISTS users (id binary(16) PRIMARY KEY, firstname varchar(255), lastname varchar(255))';
                    $successMessage = 'Created table users';
                    break;

                case 'database': // Create only the missing database
                    $query = 'CREATE DATABASE IF NOT EXISTS `'.$_ENV['DB_NAME'].'`; USE `'.$_ENV['DB_NAME'].'`';
                    $successMessage = 'Created database '.$_ENV['DB_NAME'];
                    break;

                case 'all':
                default :
                    $query = 'CREATE DATABASE IF NOT EXISTS `'.$_ENV['DB_NAME'].'`'.
                             '; USE `'.$_ENV['DB_NAME'].'`'.
                             '; CREATE TABLE IF NOT EXISTS users (id binary(16) PRIMARY KEY, firstname varchar(255), lastname varchar(255))';
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

             $this->fatalErrors = true;
        }

        // Just in case
        catch(Exception $e)
        {
            $this->logger->emergency('Caught unhandled Exception : '.$e->getMessage());
            $this->fatalErrors = true;
        }

        // Never too sure
        catch(Error $e)
        {
            $this->logger->emergency('Caught unhandled Error : '.$e->getMessage());
            $this->fatalErrors = true;
        }

        finally
        {
            if(true === $this->fatalErrors)
            {
                View::serverError(true);
            }
        }
    }
}