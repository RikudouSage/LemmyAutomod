<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240218163218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE banned_images (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, image_hash VARCHAR(180) NOT NULL, similarity_percent DOUBLE PRECISION DEFAULT \'100\' NOT NULL, remove_all BOOLEAN DEFAULT 0 NOT NULL, reason CLOB DEFAULT NULL)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__banned_usernames AS SELECT id, username, reason, remove_all FROM banned_usernames');
        $this->addSql('DROP TABLE banned_usernames');
        $this->addSql('CREATE TABLE banned_usernames (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, reason VARCHAR(180) DEFAULT NULL, remove_all BOOLEAN DEFAULT 0 NOT NULL)');
        $this->addSql('INSERT INTO banned_usernames (id, username, reason, remove_all) SELECT id, username, reason, remove_all FROM __temp__banned_usernames');
        $this->addSql('DROP TABLE __temp__banned_usernames');
        $this->addSql('CREATE TEMPORARY TABLE __temp__instance_ban_regexes AS SELECT id, regex, reason, remove_all FROM instance_ban_regexes');
        $this->addSql('DROP TABLE instance_ban_regexes');
        $this->addSql('CREATE TABLE instance_ban_regexes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, regex CLOB NOT NULL, reason VARCHAR(255) DEFAULT NULL, remove_all BOOLEAN DEFAULT 0 NOT NULL)');
        $this->addSql('INSERT INTO instance_ban_regexes (id, regex, reason, remove_all) SELECT id, regex, reason, remove_all FROM __temp__instance_ban_regexes');
        $this->addSql('DROP TABLE __temp__instance_ban_regexes');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE banned_images');
        $this->addSql('CREATE TEMPORARY TABLE __temp__banned_usernames AS SELECT id, username, reason, remove_all FROM banned_usernames');
        $this->addSql('DROP TABLE banned_usernames');
        $this->addSql('CREATE TABLE banned_usernames (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, reason VARCHAR(180) DEFAULT NULL, remove_all BOOLEAN DEFAULT false NOT NULL)');
        $this->addSql('INSERT INTO banned_usernames (id, username, reason, remove_all) SELECT id, username, reason, remove_all FROM __temp__banned_usernames');
        $this->addSql('DROP TABLE __temp__banned_usernames');
        $this->addSql('CREATE TEMPORARY TABLE __temp__instance_ban_regexes AS SELECT id, regex, reason, remove_all FROM instance_ban_regexes');
        $this->addSql('DROP TABLE instance_ban_regexes');
        $this->addSql('CREATE TABLE instance_ban_regexes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, regex CLOB NOT NULL, reason VARCHAR(255) DEFAULT NULL, remove_all BOOLEAN DEFAULT false NOT NULL)');
        $this->addSql('INSERT INTO instance_ban_regexes (id, regex, reason, remove_all) SELECT id, regex, reason, remove_all FROM __temp__instance_ban_regexes');
        $this->addSql('DROP TABLE __temp__instance_ban_regexes');
    }
}
