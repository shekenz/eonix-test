<?php

use PHPUnit\Framework\TestCase;
use API\Database;

class DatabaseTest extends TestCase
{
    public function testNotInstanciable()
    {
        $this->expectError();
        $database = new Database;
    }

    public function testHasStaticMethodGetInstance()
    {
        $this->assertTrue(method_exists(Database::class, 'getInstance'));
        $reflection = new ReflectionMethod(Database::class, 'getInstance');
        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    public function testHandlerIsPDO()
    {
        $database = Database::getInstance();
        $this->assertInstanceOf(PDO::class, $database->handler());
    }
}