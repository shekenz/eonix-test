<?php

use PHPUnit\Framework\TestCase;
use API\Users;

class UsersTest extends TestCase
{
    private $users;

    public function setUp(): void
    {
        $this->users = new Users();
    }
    
    /**
     * testHasMethodGet
     *
     * @return void
     */
    public function testHasMethodGet(): void
    {
        $this->assertTrue(method_exists(Users::class, 'get'));
    }

    public function testHasMethodGetCallback(): void
    {
        $this->assertTrue(method_exists(Users::class, 'getCallback'));
        $reflexion = new ReflectionMethod(Users::class, 'getCallback');
        $this->assertTrue($reflexion->isPrivate(), 'getCallback should be private');
    }

    public function testGetReturnsArray(): void
    {
        $this->assertIsArray($this->users->get());
    }

    public function testIdIsNotIntException(): void
    {
        $this->expectError(TypeError::class);
        $this->users->get('notAnID');
    }
    
    /**
     * testHasMethodCreate
     *
     * @return void
     */
    public function testHasMethodCreate(): void
    {
        $this->assertTrue(method_exists(Users::class, 'create'));
    }

    public function testHasMethodCreateCallback(): void
    {
        $this->assertTrue(method_exists(Users::class, 'createCallback'));
        $reflexion = new ReflectionMethod(Users::class, 'createCallback');
        $this->assertTrue($reflexion->isPrivate(), 'createCallback should be private');
    }
    
    /**
     * testHasMethodUpdate
     *
     * @return void
     */
    public function testHasMethodUpdate(): void
    {
        $this->assertTrue(method_exists(Users::class, 'update'));
    }

    public function testHasMethodUpdateCallback(): void
    {
        $this->assertTrue(method_exists(Users::class, 'updateCallback'));
        $reflexion = new ReflectionMethod(Users::class, 'updateCallback');
        $this->assertTrue($reflexion->isPrivate(), 'updateCallback should be private');
    }

    public function testUpdateEmptyIdException(): void
    {
        $this->expectException(Exception::class);
        $this->users->update();
    }
    
    /**
     * testHasMethodDelete
     *
     * @return void
     */
    public function testHasMethodDelete(): void
    {
        $this->assertTrue(method_exists(Users::class, 'delete'));
    }

    public function testHasMethodDeleteCallback(): void
    {
        $this->assertTrue(method_exists(Users::class, 'deleteCallback'));
        $reflexion = new ReflectionMethod(Users::class, 'deleteCallback');
        $this->assertTrue($reflexion->isPrivate(), 'deleteCallback should be private');
    }

    public function testDeleteEmptyIdException(): void
    {
        $this->expectException(Exception::class);
        $this->users->delete();
    }
}