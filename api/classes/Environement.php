<?php

namespace API;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use API\Logger;
use API\View;

/**
 * Singleton wrapper for Dotenv
 * Loads .env variables
 * 
 */
class Environement
{
    private static $instance;
    private $logger;

    private function __construct()
    {
        $this->logger = Logger::getInstance();

        try
        {
            self::$instance = Dotenv::createImmutable(__DIR__.'/../../');
            self::$instance->load();
        }

        catch(InvalidPathException $e)
        {
            $this->logger->emergency($e->getMessage());
            $this->logger->info('Make sure you copied .env.example to .env and correctly edited it.');
            View::serverError();
        }
    }
    
    /**
     * Singleton instanciation
     *
     * @return void
     */
    public static function init(): void
    {
        if(null === self::$instance)
        {
            self::$instance = new Environement;
        }
    }
}