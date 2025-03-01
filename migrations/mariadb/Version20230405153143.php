<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230405153143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag (id INT IDENTITY NOT NULL, name NVARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE doc_tags (tag_id INT NOT NULL, doc_id INT NOT NULL, PRIMARY KEY (tag_id, doc_id))');
        $this->addSql('CREATE INDEX IDX_36A623F0BAD26311 ON doc_tags (tag_id)');
        $this->addSql('CREATE INDEX IDX_36A623F0895648BC ON doc_tags (doc_id)');
        $this->addSql('CREATE TABLE dir_tags (tag_id INT NOT NULL, directory_id INT NOT NULL, PRIMARY KEY (tag_id, directory_id))');
        $this->addSql('CREATE INDEX IDX_9DFCC7F4BAD26311 ON dir_tags (tag_id)');
        $this->addSql('CREATE INDEX IDX_9DFCC7F42C94069F ON dir_tags (directory_id)');
        $this->addSql('ALTER TABLE doc_tags ADD CONSTRAINT FK_36A623F0BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_tags ADD CONSTRAINT FK_36A623F0895648BC FOREIGN KEY (doc_id) REFERENCES doc (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dir_tags ADD CONSTRAINT FK_9DFCC7F4BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dir_tags ADD CONSTRAINT FK_9DFCC7F42C94069F FOREIGN KEY (directory_id) REFERENCES directory (id) ON DELETE CASCADE');
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
        $this->addSql('ALTER TABLE doc_tags DROP CONSTRAINT FK_36A623F0BAD26311');
        $this->addSql('ALTER TABLE doc_tags DROP CONSTRAINT FK_36A623F0895648BC');
        $this->addSql('ALTER TABLE dir_tags DROP CONSTRAINT FK_9DFCC7F4BAD26311');
        $this->addSql('ALTER TABLE dir_tags DROP CONSTRAINT FK_9DFCC7F42C94069F');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE doc_tags');
        $this->addSql('DROP TABLE dir_tags');
    }
}
