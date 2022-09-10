<?php

use PHPUnit\Framework\TestCase;
use Monolog\Logger;

class LogTest extends TestCase
{
    public function testRootIsWritable(): void
    {
        $this->assertDirectoryIsWritable(__DIR__.'/../');
    }

    public function testLoggerExists(): void
    {
        $this->assertArrayHasKey('log', $GLOBALS);
        $this->assertInstanceOf(Logger::class, $GLOBALS['log']);
    }
}