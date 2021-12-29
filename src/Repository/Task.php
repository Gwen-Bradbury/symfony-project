<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class Task
{
    public function __construct(private Connection $connection)
    {
    }

    public function getAll(): array
    {
        return $this->connection->fetchAllAssociative('SELECT * FROM task');
    }

    public function add(string $taskName, string $taskDescription): void
    {
            $this->connection->executeStatement(
            'INSERT INTO task (name, description) VALUES (:name, :description)',
            ['name' => $taskName, 'description' => $taskDescription]
            );
    }

    public function getOne(int $id): array
    {
        return $this->connection->fetchAssociative('SELECT * FROM task WHERE id = :id', ['id' => $id]);
    }

    public function update(array $task, int $id): void
    {
            $this->connection->executeStatement(
            'UPDATE task SET name = :name, description = :description WHERE id = :id',
            ['id' => $id, 'name' => $task['name'], 'description'  => $task['description']]
            );
    }

    public function delete(int $id): void
    {
        $this->connection->executeStatement('DELETE FROM task WHERE task.id = :id', ['id' => $id]);
    }
}