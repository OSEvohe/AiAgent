<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250716203741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE context ADD COLUMN agent_id VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__context AS
        SELECT
          id,
          role,
          data,
          created_at,
          discussion_id
        FROM
          context');
        $this->addSql('DROP TABLE context');
        $this->addSql('CREATE TABLE context (
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
          role VARCHAR(255) NOT NULL,
          data CLOB NOT NULL,
          created_at DATETIME NOT NULL,
          discussion_id INTEGER NOT NULL,
          CONSTRAINT FK_E25D857E1ADED311 FOREIGN KEY (discussion_id) REFERENCES discussion (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('INSERT INTO context (
          id, role, data, created_at, discussion_id
        )
        SELECT
          id,
          role,
          data,
          created_at,
          discussion_id
        FROM
          __temp__context');
        $this->addSql('DROP TABLE __temp__context');
        $this->addSql('CREATE INDEX IDX_E25D857E1ADED311 ON context (discussion_id)');
    }
}
