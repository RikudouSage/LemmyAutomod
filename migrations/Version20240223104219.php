<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240223104219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE instance_defederation_rules (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, software VARCHAR(180) DEFAULT NULL, allow_open_registrations BOOLEAN DEFAULT NULL, allow_open_registrations_with_captcha BOOLEAN DEFAULT NULL, allow_open_registrations_with_email_verification BOOLEAN DEFAULT NULL, allow_open_registrations_with_application BOOLEAN DEFAULT NULL, treat_missing_data_as BOOLEAN DEFAULT NULL, minimum_version VARCHAR(180) DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE instance_defederation_rules');
    }
}
