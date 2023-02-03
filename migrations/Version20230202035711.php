<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230202035711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE directory (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, size VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, date_created DATETIME NOT NULL, date_modified DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_dir (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_dir_directory (file_dir_id INT NOT NULL, directory_id INT NOT NULL, INDEX IDX_372B32D556A4AB59 (file_dir_id), INDEX IDX_372B32D52C94069F (directory_id), PRIMARY KEY(file_dir_id, directory_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_dir_file (file_dir_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_CF8FEA5F56A4AB59 (file_dir_id), INDEX IDX_CF8FEA5F93CB796C (file_id), PRIMARY KEY(file_dir_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE file_dir_directory ADD CONSTRAINT FK_372B32D556A4AB59 FOREIGN KEY (file_dir_id) REFERENCES file_dir (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE file_dir_directory ADD CONSTRAINT FK_372B32D52C94069F FOREIGN KEY (directory_id) REFERENCES directory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE file_dir_file ADD CONSTRAINT FK_CF8FEA5F56A4AB59 FOREIGN KEY (file_dir_id) REFERENCES file_dir (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE file_dir_file ADD CONSTRAINT FK_CF8FEA5F93CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file_dir_directory DROP FOREIGN KEY FK_372B32D556A4AB59');
        $this->addSql('ALTER TABLE file_dir_directory DROP FOREIGN KEY FK_372B32D52C94069F');
        $this->addSql('ALTER TABLE file_dir_file DROP FOREIGN KEY FK_CF8FEA5F56A4AB59');
        $this->addSql('ALTER TABLE file_dir_file DROP FOREIGN KEY FK_CF8FEA5F93CB796C');
        $this->addSql('DROP TABLE directory');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE file_dir');
        $this->addSql('DROP TABLE file_dir_directory');
        $this->addSql('DROP TABLE file_dir_file');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
