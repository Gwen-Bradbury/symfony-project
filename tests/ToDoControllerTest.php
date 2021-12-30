<?php

namespace App\Tests;

use App\Controller\ToDo;
use PHPUnit\Framework\TestCase;

class ToDoControllerTest extends TestCase
{
    public function testAllFunctionReturnsAllDatabaseEntries(): void
    {
        $this->assertSame([], (new ToDo())->all());
    }
}