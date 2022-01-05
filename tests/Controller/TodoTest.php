<?php

namespace App\Tests\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TodoTest extends WebTestCase
{
    private Connection $connection;
    private KernelBrowser $kernelBrowser;

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
    }

    public function testItRendersAllTasks(): void
    {
        $this->connection->executeStatement("
            INSERT INTO task (name, description) 
            VALUES ('some name', 'some description'), ('some other name', 'some other description')
        ");

        $this->kernelBrowser->request('GET', '/view-all');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('some description', $this->kernelBrowser->getResponse()->getContent());
    }

    public function testItRendersAddTaskForm(): void
    {
        $this->kernelBrowser->request('GET', '/new');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Add Task', $this->kernelBrowser->getResponse()->getContent());
    }

    public function testItAddsTask(): void
    {
        $this->kernelBrowser->request(
            'POST',
            '/create', ['name' => 'some name', 'description' => 'some description']
        );

        $this->assertSame(
            [['id' => '1', 'name' => 'some name', 'description' => 'some description']],
            $this->connection->fetchAllAssociative('SELECT * FROM task')
        );
        $this->assertResponseRedirects('/view-all');
    }

    public function testItRendersEditTaskForm(): void
    {
        $this->connection->executeStatement("
            INSERT INTO task (name, description) 
            VALUES ('some name', 'some description')
        ");

        $this->kernelBrowser->request('GET', '/edit/1');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('some description', $this->kernelBrowser->getResponse()->getContent());
    }

    public function testItUpdatesTask(): void
    {
        $this->connection->executeStatement("
            INSERT INTO task (name, description) 
            VALUES ('some name', 'some description')
        ");

        $this->kernelBrowser->request(
            'POST',
            '/update/1', ['name' => 'some other name', 'description' => 'some other description']
        );

        $this->assertSame(
            [['id' => '1', 'name' => 'some other name', 'description' => 'some other description']],
            $this->connection->fetchAllAssociative('SELECT * FROM task')
        );
        $this->assertResponseRedirects('/view-all');
    }

    public function testItDeletesTask(): void
    {
        $this->connection->executeStatement("
            INSERT INTO task (name, description) 
            VALUES ('some name', 'some description')
        ");

        $this->kernelBrowser->request('GET', '/delete/1');

        $this->assertSame([], $this->connection->fetchAllAssociative('SELECT * FROM task'));
        $this->assertResponseRedirects('/view-all');
    }
}