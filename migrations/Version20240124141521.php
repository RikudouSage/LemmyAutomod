<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240124141521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trusted_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) DEFAULT NULL, instance VARCHAR(180) DEFAULT NULL, user_id INTEGER DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A8733F7A76ED395 ON trusted_users (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A8733F7F85E06774230B1DE ON trusted_users (username, instance)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE trusted_users');
    }
}
