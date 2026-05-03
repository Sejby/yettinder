<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the yetti_vote table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS yetti_vote (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            yetti_id INTEGER NOT NULL REFERENCES yetti(id),
            session_id VARCHAR(128) NOT NULL,
            vote INTEGER NOT NULL,
            voted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (yetti_id, session_id)
        )');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_yetti_vote_session ON yetti_vote (session_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS yetti_vote');
    }
}
