<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230405135809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE directory (id INT IDENTITY NOT NULL, parent_id INT, path NVARCHAR(255) NOT NULL, name NVARCHAR(255) NOT NULL, notes VARCHAR(MAX), date_trashed DATETIME2(6), PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_467844DA727ACA70 ON directory (parent_id)');
        $this->addSql('CREATE TABLE doc (id INT IDENTITY NOT NULL, directory_id INT NOT NULL, name NVARCHAR(255) NOT NULL, size NVARCHAR(255) NOT NULL, notes VARCHAR(MAX), date_created DATETIME2(6) NOT NULL, date_modified DATETIME2(6) NOT NULL, date_trashed DATETIME2(6), file_name NVARCHAR(255) NOT NULL, mime_type NVARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_8641FD642C94069F ON doc (directory_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT IDENTITY NOT NULL, body VARCHAR(MAX) NOT NULL, headers VARCHAR(MAX) NOT NULL, queue_name NVARCHAR(190) NOT NULL, created_at DATETIME2(6) NOT NULL, available_at DATETIME2(6) NOT NULL, delivered_at DATETIME2(6), PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_467844DA727ACA70 FOREIGN KEY (parent_id) REFERENCES directory (id)');
        $this->addSql('ALTER TABLE doc ADD CONSTRAINT FK_8641FD642C94069F FOREIGN KEY (directory_id) REFERENCES directory (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA db_accessadmin');
        $this->addSql('CREATE SCHEMA db_backupoperator');
        $this->addSql('CREATE SCHEMA db_datareader');
        $this->addSql('CREATE SCHEMA db_datawriter');
        $this->addSql('CREATE SCHEMA db_ddladmin');
        $this->addSql('CREATE SCHEMA db_denydatareader');
        $this->addSql('CREATE SCHEMA db_denydatawriter');
        $this->addSql('CREATE SCHEMA db_owner');
        $this->addSql('CREATE SCHEMA db_securityadmin');
        $this->addSql('CREATE SCHEMA dbo');
        $this->addSql('ALTER TABLE directory DROP CONSTRAINT FK_467844DA727ACA70');
        $this->addSql('ALTER TABLE doc DROP CONSTRAINT FK_8641FD642C94069F');
        $this->addSql('DROP TABLE directory');
        $this->addSql('DROP TABLE doc');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
