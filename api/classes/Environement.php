<?php

namespace API;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use API\Logger;

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

            header("HTTP/1.1 500 Internal Server Error");
            die();
        }
    }
    
    public static function init(): void
    {
        if(null === self::$instance)
        {
            self::$instance = new Environement;
        }
    }
}