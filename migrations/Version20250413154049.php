<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250413154049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE banned_qr_codes ADD COLUMN enabled BOOLEAN DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__banned_qr_codes AS SELECT id, regex, remove_all, reason FROM banned_qr_codes');
        $this->addSql('DROP TABLE banned_qr_codes');
        $this->addSql('CREATE TABLE banned_qr_codes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, regex CLOB NOT NULL, remove_all BOOLEAN NOT NULL, reason VARCHAR(180) DEFAULT NULL)');
        $this->addSql('INSERT INTO banned_qr_codes (id, regex, remove_all, reason) SELECT id, regex, remove_all, reason FROM __temp__banned_qr_codes');
        $this->addSql('DROP TABLE __temp__banned_qr_codes');
    }
}
