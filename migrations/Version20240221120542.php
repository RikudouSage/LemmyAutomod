<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240221120542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ignored_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) DEFAULT NULL, instance VARCHAR(180) DEFAULT NULL, user_id INTEGER DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EE4834DCA76ED395 ON ignored_users (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EE4834DCF85E06774230B1DE ON ignored_users (username, instance)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ignored_users');
    }
}
