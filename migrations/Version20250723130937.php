<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250723130937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE external_regex_lists (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url CLOB NOT NULL, delimiter VARCHAR(255) NOT NULL, prepend CLOB DEFAULT NULL, append CLOB DEFAULT NULL, name VARCHAR(180) NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_DE92D6F95E237E06 ON external_regex_lists (name)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE external_regex_lists
        SQL);
    }
}
