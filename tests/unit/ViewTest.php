<?php

use PHPUnit\Framework\TestCase;
use API\View;

class ViewTest extends TestCase
{
    public function testHasErrorMethod(): void
    {
        $this->assertTrue(method_exists(View::class, 'error'));
    }

    public function testHasRenderMethod(): void
    {
        $this->assertTrue(method_exists(View::class, 'render'));
    }
}