<?php

use PHPUnit\Framework\TestCase;
use API\Logger;

class LogTest extends TestCase
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

    public function testIsInstanceOfLogger(): void
    {
        $this->assertInstanceOf(Monolog\Logger::class, Logger::logger());
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