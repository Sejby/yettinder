<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260430000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the yetti table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS yetti (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            gender VARCHAR(10) NOT NULL,
            height INTEGER NOT NULL,
            weight REAL NOT NULL,
            address VARCHAR(120) NOT NULL,
            rating REAL NOT NULL DEFAULT 0
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS yetti');
    }
}
