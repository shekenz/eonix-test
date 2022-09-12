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

    public function testHasMethodGet()
    {
        $this->assertTrue(method_exists(Users::class, 'get'));
    }

    public function testGetReturnsArray()
    {
        $this->assertIsArray($this->users->get());
    }

    public function testHasMethodCreate()
    {
        $this->assertTrue(method_exists(Users::class, 'create'));
    }

    public function testHasMethodUpdate()
    {
        $this->assertTrue(method_exists(Users::class, 'delete'));
    }

    public function testHasMethodDelete()
    {
        $this->assertTrue(method_exists(Users::class, 'delete'));
    }

    public function testIDNotFound()
    {
        $this->users->get('notAnID');
        $this->expectExceptionMessage('User not found.');
    }
}