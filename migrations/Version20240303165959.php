<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240303165959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE instance_defederation_rules ADD COLUMN reason CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE instance_defederation_rules ADD COLUMN evidence CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__instance_defederation_rules AS SELECT id, software, allow_open_registrations, allow_open_registrations_with_captcha, allow_open_registrations_with_email_verification, allow_open_registrations_with_application, treat_missing_data_as, minimum_version FROM instance_defederation_rules');
        $this->addSql('DROP TABLE instance_defederation_rules');
        $this->addSql('CREATE TABLE instance_defederation_rules (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, software VARCHAR(180) DEFAULT NULL, allow_open_registrations BOOLEAN DEFAULT NULL, allow_open_registrations_with_captcha BOOLEAN DEFAULT NULL, allow_open_registrations_with_email_verification BOOLEAN DEFAULT NULL, allow_open_registrations_with_application BOOLEAN DEFAULT NULL, treat_missing_data_as BOOLEAN DEFAULT NULL, minimum_version VARCHAR(180) DEFAULT NULL)');
        $this->addSql('INSERT INTO instance_defederation_rules (id, software, allow_open_registrations, allow_open_registrations_with_captcha, allow_open_registrations_with_email_verification, allow_open_registrations_with_application, treat_missing_data_as, minimum_version) SELECT id, software, allow_open_registrations, allow_open_registrations_with_captcha, allow_open_registrations_with_email_verification, allow_open_registrations_with_application, treat_missing_data_as, minimum_version FROM __temp__instance_defederation_rules');
        $this->addSql('DROP TABLE __temp__instance_defederation_rules');
    }
}
