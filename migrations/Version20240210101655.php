<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240210101655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE banned_usernames ADD COLUMN remove_all BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE instance_ban_regexes ADD COLUMN remove_all BOOLEAN NOT NULL DEFAULT false');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__banned_usernames AS SELECT id, username, reason FROM banned_usernames');
        $this->addSql('DROP TABLE banned_usernames');
        $this->addSql('CREATE TABLE banned_usernames (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, reason VARCHAR(180) DEFAULT NULL)');
        $this->addSql('INSERT INTO banned_usernames (id, username, reason) SELECT id, username, reason FROM __temp__banned_usernames');
        $this->addSql('DROP TABLE __temp__banned_usernames');
        $this->addSql('CREATE TEMPORARY TABLE __temp__instance_ban_regexes AS SELECT id, regex, reason FROM instance_ban_regexes');
        $this->addSql('DROP TABLE instance_ban_regexes');
        $this->addSql('CREATE TABLE instance_ban_regexes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, regex CLOB NOT NULL, reason VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO instance_ban_regexes (id, regex, reason) SELECT id, regex, reason FROM __temp__instance_ban_regexes');
        $this->addSql('DROP TABLE __temp__instance_ban_regexes');
    }
}
