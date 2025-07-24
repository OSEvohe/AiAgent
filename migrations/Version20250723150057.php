<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Random\RandomException;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250723150057 extends AbstractMigration
{
    /**
     * @throws RandomException
     * @throws Exception
     */
    public function getDescription(): string
    {
        // get all existing context entries
        $entries = $this->connection->executeQuery('SELECT id, role, data, created_at, discussion_id, agent_id, uid FROM context');

        // update each entry to ensure it has a unique ID
        foreach ($entries->fetchAllAssociative() as $entry) {
            $uniqueId = uniqid();
            $this->addSql(
                'UPDATE context SET uid = :uid WHERE id = :id',
                [
                    'uid' => $uniqueId,
                    'id' => $entry['id'],
                ]
            );
        }

        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__context AS SELECT id, role, data, created_at, discussion_id, agent_id, uid FROM context');
        $this->addSql('DROP TABLE context');
        $this->addSql(
            'CREATE TABLE context (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role VARCHAR(255) NOT NULL, data CLOB NOT NULL, created_at DATETIME NOT NULL, discussion_id INTEGER NOT NULL, agent_id VARCHAR(255) NOT NULL, uid VARCHAR(255) NOT NULL, CONSTRAINT FK_E25D857E1ADED311 FOREIGN KEY (discussion_id) REFERENCES discussion (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)'
        );
        $this->addSql('INSERT INTO context (id, role, data, created_at, discussion_id, agent_id, uid) SELECT id, role, data, created_at, discussion_id, agent_id, uid FROM __temp__context');
        $this->addSql('DROP TABLE __temp__context');
        $this->addSql('CREATE INDEX IDX_E25D857E1ADED311 ON context (discussion_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__context AS SELECT id, role, data, created_at, agent_id, uid, discussion_id FROM context');
        $this->addSql('DROP TABLE context');
        $this->addSql(
            'CREATE TABLE context (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role VARCHAR(255) NOT NULL, data CLOB NOT NULL, created_at DATETIME NOT NULL, agent_id VARCHAR(255) NOT NULL, uid VARCHAR(255) DEFAULT NULL, discussion_id INTEGER NOT NULL, CONSTRAINT FK_E25D857E1ADED311 FOREIGN KEY (discussion_id) REFERENCES discussion (id) NOT DEFERRABLE INITIALLY IMMEDIATE)'
        );
        $this->addSql('INSERT INTO context (id, role, data, created_at, agent_id, uid, discussion_id) SELECT id, role, data, created_at, agent_id, uid, discussion_id FROM __temp__context');
        $this->addSql('DROP TABLE __temp__context');
        $this->addSql('CREATE INDEX IDX_E25D857E1ADED311 ON context (discussion_id)');
    }
}
