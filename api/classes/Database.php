<?php

namespace API;

use PDOException;
use API\Logger;
use API\Environement;
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

        self::$handler = new \PDO(
            'mysql:host='.$_ENV['DB_HOST'].';
                    port='.$_ENV['DB_PORT'].';
                    database='.$_ENV['DB_NAME'].';
                    charset=utf8mb4',
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASSWORD']
        );        
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
    public function init(): void
    {
        try
        {
            self::$handler->exec(
                'CREATE DATABASE IF NOT EXISTS `'.$_ENV['DB_NAME'].'`'.
                '; USE `'.$_ENV['DB_NAME'].'`'.
                '; CREATE TABLE IF NOT EXISTS users (id binary(36), firstname varchar(255), lastname varchar(255))'
            );
            // More info on why I used binary(36) for GUID
            // https://stackoverflow.com/questions/2365132/uuid-performance-in-mysql/#answer-7578500
            // https://stitcher.io/blog/optimised-uuids-in-mysql
            $this->logger->info('Created database '.$_ENV['DB_NAME']);
        }

        catch(PDOException $e)
        {            
            if(isset($e->errorInfo) && $e->errorInfo[1] === 1044)
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
                header("HTTP/1.1 500 Internal Server Error");
                die(); // (8E
            }
        }
    }
}