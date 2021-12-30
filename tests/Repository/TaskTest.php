<?php

namespace App\Tests\Repository;

use App\Repository\Task;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskTest extends WebTestCase
{
    private Connection $connection;
    private KernelBrowser $kernelBrowser;
    private Task $task;

    public function setUp(): void
    {
        $this->kernelBrowser = static::createClient();
        $container = $this->kernelBrowser->getContainer();
        $this->connection = $container->get('database_connection');

        $this->connection->executeStatement('
            CREATE TABLE task (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255),
                description TEXT
            )
        ');

        $this->task = new Task($this->connection);
    }

    public function testGetsEmptyArrayIfNoResults(): void
    {
        $this->assertSame([], $this->task->getAll());
    }

    public function testItGetsAllTasks(): void
    {
        $this->connection->executeStatement("
            INSERT INTO task (name, description) 
            VALUES ('some name', 'some description'), ('some other name', 'some other description')
        ");

        $this->assertSame(
            [
                ['id' => "1", 'name' => 'some name', 'description' => 'some description'],
                ['id' => "2", 'name' => 'some other name', 'description' => 'some other description']
            ],
            $this->task->getAll()
        );
    }

    public function testAddsTask(): void
    {
        $this->task->add('some name', 'some description');

        $this->assertSame(
            [['id' => "1", 'name' => 'some name', 'description' => 'some description']],
            $this->connection->fetchAllAssociative("SELECT * FROM task"),
        );
    }

    public function testGetsOneTask(): void
    {
        $this->connection->executeStatement("
            INSERT INTO task (name, description) 
            VALUES ('some name', 'some description')
        ");

        $actualTask = $this->task->getOne(1);

        $this->assertSame(
            ['id' => '1', 'name' => 'some name', 'description' => 'some description'],
            $actualTask
        );
    }

    public function testATaskGetsUpdated(): void
    {
        $this->connection->executeStatement("
            INSERT INTO task (name, description) 
            VALUES ('some name', 'some description')
        ");

        $task = [
            'name' => 'some other name',
            'description' => 'some other description'
        ];
        $this->task->update($task, 1);

        $this->assertSame(
            ['id' => "1", 'name' => 'some other name', 'description' => 'some other description'],
            $this->connection->fetchAssociative("SELECT * FROM task")
        );
    }

    public function testATaskDeletesFromTheDatabase(): void
    {
        $this->connection->executeStatement("
            INSERT INTO task (name, description) 
            VALUES ('some name', 'some description'), ('some other name', 'some other description')
        ");

        $this->task->delete(1);

        $this->assertSame(
            [['id' => "2", 'name' => 'some other name', 'description' => 'some other description']],
            $this->connection->fetchAllAssociative("SELECT * FROM task")
        );
    }
}