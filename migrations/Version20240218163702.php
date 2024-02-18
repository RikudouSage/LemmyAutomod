<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240218163702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE banned_images ADD COLUMN description CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__banned_images AS SELECT id, image_hash, similarity_percent, remove_all, reason FROM banned_images');
        $this->addSql('DROP TABLE banned_images');
        $this->addSql('CREATE TABLE banned_images (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, image_hash VARCHAR(180) NOT NULL, similarity_percent DOUBLE PRECISION DEFAULT \'100\' NOT NULL, remove_all BOOLEAN DEFAULT 0 NOT NULL, reason CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO banned_images (id, image_hash, similarity_percent, remove_all, reason) SELECT id, image_hash, similarity_percent, remove_all, reason FROM __temp__banned_images');
        $this->addSql('DROP TABLE __temp__banned_images');
    }
}
