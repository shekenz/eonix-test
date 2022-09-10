<?php

use PHPUnit\Framework\TestCase;

class EnvTest extends TestCase
{
    public function testEnvFileExists(): void
    {
        $this->assertFileExists(__DIR__.'/../../.env');
        $this->assertFileIsReadable(__DIR__.'/../../.env');
    }

    public function testEnvDbHostExists(): void
    {
        $this->assertArrayHasKey('DB_HOST', $_ENV);
        $this->assertNotEmpty($_ENV['DB_HOST']);
        $this->assertIsString($_ENV['DB_HOST']);
    }

    public function testEnvDbPortExists(): void
    {
        $this->assertArrayHasKey('DB_PORT', $_ENV);
        $this->assertNotEmpty($_ENV['DB_PORT']);
        $this->assertIsString($_ENV['DB_PORT']);
    }

    public function testEnvDbNameExists(): void
    {
        $this->assertArrayHasKey('DB_NAME', $_ENV);
        $this->assertNotEmpty($_ENV['DB_NAME']);
        $this->assertIsString($_ENV['DB_NAME']);
    }

    public function testEnvDbUserExists(): void
    {
        $this->assertArrayHasKey('DB_USER', $_ENV);
        $this->assertNotEmpty($_ENV['DB_USER']);
        $this->assertIsString($_ENV['DB_USER']);
    }

    public function testEnvDbPasswordExists(): void
    {
        $this->assertArrayHasKey('DB_PASSWORD', $_ENV);
        $this->assertIsString($_ENV['DB_PASSWORD']);
    }
}