<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240220162959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__removal_logs AS SELECT id, type, target_id, valid_until FROM removal_logs');
        $this->addSql('DROP TABLE removal_logs');
        $this->addSql('CREATE TABLE removal_logs (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(180) NOT NULL, target_id INTEGER NOT NULL, valid_until DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('INSERT INTO removal_logs (id, type, target_id, valid_until) SELECT id, type, target_id, valid_until FROM __temp__removal_logs');
        $this->addSql('DROP TABLE __temp__removal_logs');
        $this->addSql('CREATE INDEX IDX_66A473F68CDE5729158E0B66 ON removal_logs (type, target_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__removal_logs AS SELECT id, type, target_id, valid_until FROM removal_logs');
        $this->addSql('DROP TABLE removal_logs');
        $this->addSql('CREATE TABLE removal_logs (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(180) NOT NULL, target_id INTEGER NOT NULL, valid_until DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('INSERT INTO removal_logs (id, type, target_id, valid_until) SELECT id, type, target_id, valid_until FROM __temp__removal_logs');
        $this->addSql('DROP TABLE __temp__removal_logs');
    }
}
