<?php

use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testRootIsWritable(): void
    {
        $this->assertDirectoryIsWritable(__DIR__.'/../');
    }
}