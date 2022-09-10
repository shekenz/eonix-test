<?php

use PHPUnit\Framework\TestCase;
use API\Router;

class RouterTest extends TestCase
{
    public function testHasMethodStripQuery(): void
    {
        $this->assertTrue(method_exists(Router::class, 'stripQuery'));
    }

    public function testHasMethodDispatch(): void
    {
        $this->assertTrue(method_exists(Router::class, 'dispatch'));
    }

    public function testIsInstanciableEmpty(): void
    {
        $this->assertInstanceOf(Router::class, new Router());
    }

}