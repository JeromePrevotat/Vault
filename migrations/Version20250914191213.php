<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250914191213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, slug VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, salt VARCHAR(255) NOT NULL, INDEX IDX_8C9F36107E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_category (file_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_B71C965C93CB796C (file_id), INDEX IDX_B71C965C12469DE2 (category_id), PRIMARY KEY(file_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36107E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE file_category ADD CONSTRAINT FK_B71C965C93CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE file_category ADD CONSTRAINT FK_B71C965C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36107E3C61F9');
        $this->addSql('ALTER TABLE file_category DROP FOREIGN KEY FK_B71C965C93CB796C');
        $this->addSql('ALTER TABLE file_category DROP FOREIGN KEY FK_B71C965C12469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE file_category');
    }
}
