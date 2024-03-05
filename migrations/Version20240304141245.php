<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240304141245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auto_approval_regexes ADD COLUMN enabled BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE banned_emails ADD COLUMN enabled BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE banned_images ADD COLUMN enabled BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE banned_usernames ADD COLUMN enabled BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE ignored_users ADD COLUMN enabled BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE instance_ban_regexes ADD COLUMN enabled BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE instance_defederation_rules ADD COLUMN enabled BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE report_regexes ADD COLUMN enabled BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE trusted_users ADD COLUMN enabled BOOLEAN NOT NULL DEFAULT true');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__auto_approval_regexes AS SELECT id, regex FROM auto_approval_regexes');
        $this->addSql('DROP TABLE auto_approval_regexes');
        $this->addSql('CREATE TABLE auto_approval_regexes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, regex CLOB NOT NULL)');
        $this->addSql('INSERT INTO auto_approval_regexes (id, regex) SELECT id, regex FROM __temp__auto_approval_regexes');
        $this->addSql('DROP TABLE __temp__auto_approval_regexes');
        $this->addSql('CREATE TEMPORARY TABLE __temp__banned_emails AS SELECT id, regex, reason FROM banned_emails');
        $this->addSql('DROP TABLE banned_emails');
        $this->addSql('CREATE TABLE banned_emails (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, regex VARCHAR(180) NOT NULL, reason CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO banned_emails (id, regex, reason) SELECT id, regex, reason FROM __temp__banned_emails');
        $this->addSql('DROP TABLE __temp__banned_emails');
        $this->addSql('CREATE TEMPORARY TABLE __temp__banned_images AS SELECT id, image_hash, similarity_percent, remove_all, reason, description FROM banned_images');
        $this->addSql('DROP TABLE banned_images');
        $this->addSql('CREATE TABLE banned_images (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, image_hash VARCHAR(180) NOT NULL, similarity_percent DOUBLE PRECISION DEFAULT \'100\' NOT NULL, remove_all BOOLEAN DEFAULT 0 NOT NULL, reason CLOB DEFAULT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO banned_images (id, image_hash, similarity_percent, remove_all, reason, description) SELECT id, image_hash, similarity_percent, remove_all, reason, description FROM __temp__banned_images');
        $this->addSql('DROP TABLE __temp__banned_images');
        $this->addSql('CREATE TEMPORARY TABLE __temp__banned_usernames AS SELECT id, username, reason, remove_all FROM banned_usernames');
        $this->addSql('DROP TABLE banned_usernames');
        $this->addSql('CREATE TABLE banned_usernames (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, reason VARCHAR(180) DEFAULT NULL, remove_all BOOLEAN DEFAULT 0 NOT NULL)');
        $this->addSql('INSERT INTO banned_usernames (id, username, reason, remove_all) SELECT id, username, reason, remove_all FROM __temp__banned_usernames');
        $this->addSql('DROP TABLE __temp__banned_usernames');
        $this->addSql('CREATE TEMPORARY TABLE __temp__ignored_users AS SELECT id, username, instance, user_id FROM ignored_users');
        $this->addSql('DROP TABLE ignored_users');
        $this->addSql('CREATE TABLE ignored_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) DEFAULT NULL, instance VARCHAR(180) DEFAULT NULL, user_id INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO ignored_users (id, username, instance, user_id) SELECT id, username, instance, user_id FROM __temp__ignored_users');
        $this->addSql('DROP TABLE __temp__ignored_users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EE4834DCA76ED395 ON ignored_users (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EE4834DCF85E06774230B1DE ON ignored_users (username, instance)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__instance_ban_regexes AS SELECT id, regex, reason, remove_all FROM instance_ban_regexes');
        $this->addSql('DROP TABLE instance_ban_regexes');
        $this->addSql('CREATE TABLE instance_ban_regexes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, regex CLOB NOT NULL, reason VARCHAR(255) DEFAULT NULL, remove_all BOOLEAN DEFAULT 0 NOT NULL)');
        $this->addSql('INSERT INTO instance_ban_regexes (id, regex, reason, remove_all) SELECT id, regex, reason, remove_all FROM __temp__instance_ban_regexes');
        $this->addSql('DROP TABLE __temp__instance_ban_regexes');
        $this->addSql('CREATE TEMPORARY TABLE __temp__instance_defederation_rules AS SELECT id, software, allow_open_registrations, allow_open_registrations_with_captcha, allow_open_registrations_with_email_verification, allow_open_registrations_with_application, treat_missing_data_as, minimum_version, reason, evidence FROM instance_defederation_rules');
        $this->addSql('DROP TABLE instance_defederation_rules');
        $this->addSql('CREATE TABLE instance_defederation_rules (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, software VARCHAR(180) DEFAULT NULL, allow_open_registrations BOOLEAN DEFAULT NULL, allow_open_registrations_with_captcha BOOLEAN DEFAULT NULL, allow_open_registrations_with_email_verification BOOLEAN DEFAULT NULL, allow_open_registrations_with_application BOOLEAN DEFAULT NULL, treat_missing_data_as BOOLEAN DEFAULT NULL, minimum_version VARCHAR(180) DEFAULT NULL, reason CLOB DEFAULT NULL, evidence CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO instance_defederation_rules (id, software, allow_open_registrations, allow_open_registrations_with_captcha, allow_open_registrations_with_email_verification, allow_open_registrations_with_application, treat_missing_data_as, minimum_version, reason, evidence) SELECT id, software, allow_open_registrations, allow_open_registrations_with_captcha, allow_open_registrations_with_email_verification, allow_open_registrations_with_application, treat_missing_data_as, minimum_version, reason, evidence FROM __temp__instance_defederation_rules');
        $this->addSql('DROP TABLE __temp__instance_defederation_rules');
        $this->addSql('CREATE TEMPORARY TABLE __temp__report_regexes AS SELECT id, regex, message FROM report_regexes');
        $this->addSql('DROP TABLE report_regexes');
        $this->addSql('CREATE TABLE report_regexes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, regex CLOB NOT NULL, message CLOB NOT NULL)');
        $this->addSql('INSERT INTO report_regexes (id, regex, message) SELECT id, regex, message FROM __temp__report_regexes');
        $this->addSql('DROP TABLE __temp__report_regexes');
        $this->addSql('CREATE TEMPORARY TABLE __temp__trusted_users AS SELECT id, username, instance, user_id FROM trusted_users');
        $this->addSql('DROP TABLE trusted_users');
        $this->addSql('CREATE TABLE trusted_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) DEFAULT NULL, instance VARCHAR(180) DEFAULT NULL, user_id INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO trusted_users (id, username, instance, user_id) SELECT id, username, instance, user_id FROM __temp__trusted_users');
        $this->addSql('DROP TABLE __temp__trusted_users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A8733F7A76ED395 ON trusted_users (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A8733F7F85E06774230B1DE ON trusted_users (username, instance)');
    }
}
