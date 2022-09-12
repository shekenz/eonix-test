<?php

namespace API;

use Monolog\Logger as OriginalLogger;
use Monolog\Handler\StreamHandler;

/**
 * Logger
 * 
 * A singleton wrapper for Monolog\Logger
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

    public static function getInstance(): self
    {
        if(null === self::$instance) {
            self::$instance = new Logger();
        }

        return self::$instance;
    }

    public function logger(): OriginalLogger
    {
        return self::$logger;
    }

    public function debug($message): void
    {
        self::$logger->debug($message);
    }

    public function info($message): void
    {
        self::$logger->info($message);
    }

    public function notice($message): void
    {
        self::$logger->notice($message);
    }

    public function warning($message): void
    {
        self::$logger->warning($message);
    }

    public function error($message): void
    {
        self::$logger->error($message);
    }

    public function critical($message): void
    {
        self::$logger->critical($message);
    }

    public function alert($message): void
    {
        self::$logger->alert($message);
    }

    public function emergency($message): void
    {
        self::$logger->emergency($message);
    }
}