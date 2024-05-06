<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240506220224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ignored_comments (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, comment_id INTEGER NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_19D211C8F8697D13 ON ignored_comments (comment_id)');
        $this->addSql('CREATE TABLE ignored_posts (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, post_id INTEGER NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_72962BCF4B89032C ON ignored_posts (post_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ignored_comments');
        $this->addSql('DROP TABLE ignored_posts');
    }
}
