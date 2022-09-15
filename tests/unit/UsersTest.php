<?php

use PHPUnit\Framework\TestCase;
use API\Users;

class UsersTest extends TestCase
{
    private static $usersWithDataForCreation;
    private static $usersWithDataForUpdate;
    private static $mockUser;

    public static function setUpBeforeClass(): void
    {
        self::$usersWithDataForCreation = new Users(['firstname' => 'Test created firstname', 'lastname' => 'Test created lastname']);
        self::$usersWithDataForUpdate = new Users(['firstname' => 'Test updated firstname', 'lastname' => 'Test updated lastname']);
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

    public function testCreateReturnsArray(): void
    {
        self::$mockUser = self::$usersWithDataForCreation->create();
        $this->assertIsArray(self::$mockUser);
    }

    public function testUserCreated(): void
    {
        $this->assertMatchesRegularExpression('/[a-f0-9]{32}/', self::$mockUser['id']);
        $this->assertEquals('Test created firstname', self::$mockUser['firstname']);
        $this->assertEquals('Test created lastname', self::$mockUser['lastname']);
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
        $this->assertIsArray(self::$usersWithDataForCreation->get());
    }

    public function testDataIsNotAnArrayException(): void
    {
        $this->expectError(TypeError::class);
        self::$usersWithDataForCreation->get('notAnArray');
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
        $this->expectException(ArgumentCountError::class);
        self::$usersWithDataForUpdate->update();
    }

    public function testUpdateReturnsArray(): void
    {
        self::$mockUser = self::$usersWithDataForUpdate->update(['id' => self::$mockUser['id']]);
        $this->assertIsArray(self::$mockUser);
    }

    public function testUserUpdated(): void
    {
        $this->assertMatchesRegularExpression('/[a-f0-9]{32}/', self::$mockUser['id']);
        $this->assertEquals('Test updated firstname', self::$mockUser['firstname']);
        $this->assertEquals('Test updated lastname', self::$mockUser['lastname']);
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
        $this->expectException(ArgumentCountError::class);
        self::$usersWithDataForCreation->delete();
    }

    public function testDeleteUser(): void
    {
        self::$usersWithDataForCreation->delete(self::$mockUser);
        $this->assertEmpty(self::$usersWithDataForCreation->get(['id' => self::$mockUser['id']]));
    }
}