<?php

namespace API;

use Monolog\Logger as OriginalLogger;
use Monolog\Handler\StreamHandler;

/**
 * A singleton wrapper for Monolog\Logger
 * 
 */
class Logger
{
    private static $logger; // The original logger
    private static $instance;

    private function __construct()
    {
        self::$logger = new OriginalLogger('api');
        self::$logger->pushHandler(new StreamHandler(__DIR__.'/../../api.log'));
    }

    /**
     * Singleton instanciation
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if(null === self::$instance) {
            self::$instance = new Logger();
        }

        return self::$instance;
    }
    
    /**
     * Getter for the original Monolg/Logger object.
     *
     * @return OriginalLogger
     */
    public function logger(): OriginalLogger
    {
        return self::$logger;
    }
    
    /**
     * debug() method wrapper
     *
     * @param  string $message
     * @return void
     */
    public function debug(string $message): void
    {
        self::$logger->debug($message);
    }
    
    /**
     * info() method wrapper
     *
     * @param  string $message
     * @return void
     */
    public function info(string $message): void
    {
        self::$logger->info($message);
    }
    
    /**
     * notice() method wrapper
     *
     * @param  string $message
     * @return void
     */
    public function notice(string $message): void
    {
        self::$logger->notice($message);
    }
    
    /**
     * warning() method wrapper
     *
     * @param  string $message
     * @return void
     */
    public function warning(string $message): void
    {
        self::$logger->warning($message);
    }
    
    /**
     * error() method wrapper
     *
     * @param  string $message
     * @return void
     */
    public function error(string $message): void
    {
        self::$logger->error($message);
    }
    
    /**
     * critical() method wrapper
     *
     * @param  string $message
     * @return void
     */
    public function critical(string $message): void
    {
        self::$logger->critical($message);
    }
    
    /**
     * alert() method wrapper
     *
     * @param  string $message
     * @return void
     */
    public function alert(string $message): void
    {
        self::$logger->alert($message);
    }
    
    /**
     * emergency() method wrapper
     *
     * @param  string $message
     * @return void
     */
    public function emergency(string $message): void
    {
        self::$logger->emergency($message);
    }
}