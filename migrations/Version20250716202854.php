<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250716202854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE context (
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
          role VARCHAR(255) NOT NULL,
          data CLOB NOT NULL,
          created_at DATETIME NOT NULL,
          discussion_id INTEGER NOT NULL,
          CONSTRAINT FK_E25D857E1ADED311 FOREIGN KEY (discussion_id) REFERENCES discussion (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_E25D857E1ADED311 ON context (discussion_id)');
        $this->addSql('CREATE TABLE discussion (
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
          uid VARCHAR(255) NOT NULL,
          title VARCHAR(255) NOT NULL
        )');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE context');
        $this->addSql('DROP TABLE discussion');
    }
}
