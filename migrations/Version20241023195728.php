<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023195728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report_regexes ADD COLUMN private BOOLEAN DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__report_regexes AS SELECT id, regex, message, enabled FROM report_regexes');
        $this->addSql('DROP TABLE report_regexes');
        $this->addSql('CREATE TABLE report_regexes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, regex CLOB NOT NULL, message CLOB NOT NULL, enabled BOOLEAN DEFAULT 1 NOT NULL)');
        $this->addSql('INSERT INTO report_regexes (id, regex, message, enabled) SELECT id, regex, message, enabled FROM __temp__report_regexes');
        $this->addSql('DROP TABLE __temp__report_regexes');
    }
}
