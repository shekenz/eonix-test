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

    public function testHasMethodGet(): void
    {
        $this->assertTrue(method_exists(Users::class, 'get'));
    }

    public function testGetReturnsArray(): void
    {
        $this->assertIsArray($this->users->get());
    }

    public function testHasMethodCreate(): void
    {
        $this->assertTrue(method_exists(Users::class, 'create'));
    }

    public function testHasMethodUpdate(): void
    {
        $this->assertTrue(method_exists(Users::class, 'delete'));
    }

    public function testHasMethodDelete(): void
    {
        $this->assertTrue(method_exists(Users::class, 'delete'));
    }

    public function testIdNotFound(): void
    {
        $this->expectExceptionMessage('User not found.');
        $this->users->get('notAnID');
    }
}