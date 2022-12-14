<?php

use PHPUnit\Framework\TestCase;
use API\Logger;

class LoggerTest extends TestCase
{
    public function testRootIsWritable(): void
    {
        $this->assertDirectoryIsWritable(__DIR__.'/../');
    }

    public function testHasStaticMethodGetInstance(): void
    {
        $this->assertTrue(method_exists(Logger::class, 'getInstance'));
        $reflection = new ReflectionMethod(Logger::class, 'getInstance');
        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    public function testNotInstanciable(): void
    {
        $this->expectError();
        $database = new Logger;
    }

    public function testHasMethodLogger(): void
    {
        $this->assertTrue(method_exists(Logger::class, 'logger'));
        $reflection = new ReflectionMethod(Logger::class, 'logger');
        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    public function testIsInstanceOfLogger(): void
    {
        $logger = Logger::getInstance();
        $this->assertInstanceOf(Monolog\Logger::class, $logger->logger());
    }

    public function testHasMethodDebug(): void
    {
        $this->assertTrue(method_exists(Logger::class, 'debug'));
    }

    public function testHasMethodInfo(): void
    {
        $this->assertTrue(method_exists(Logger::class, 'info'));
    }

    public function testHasMethodNotice(): void
    {
        $this->assertTrue(method_exists(Logger::class, 'notice'));
    }

    public function testHasMethodWarning(): void
    {
        $this->assertTrue(method_exists(Logger::class, 'warning'));
    }

    public function testHasMethodError(): void
    {
        $this->assertTrue(method_exists(Logger::class, 'debug'));
    }

    public function testHasMethodCritical(): void
    {
        $this->assertTrue(method_exists(Logger::class, 'critical'));
    }

    public function testHasMethodAlert(): void
    {
        $this->assertTrue(method_exists(Logger::class, 'alert'));
    }

    public function testHasMethodEmergency(): void
    {
        $this->assertTrue(method_exists(Logger::class, 'emergency'));
    }
}