<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228221518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trip (id INT AUTO_INCREMENT NOT NULL, from_station VARCHAR(120) NOT NULL, to_station VARCHAR(120) NOT NULL, network VARCHAR(30) NOT NULL, line VARCHAR(30) NOT NULL, payload JSON DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_7656F53BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user CHANGE first_name first_name VARCHAR(80) DEFAULT NULL, CHANGE last_name last_name VARCHAR(80) DEFAULT NULL, CHANGE birth_date birth_date DATE DEFAULT NULL, CHANGE is_verified is_verified TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_identifier_email TO UNIQ_8D93D649E7927C74');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53BA76ED395');
        $this->addSql('DROP TABLE trip');
        $this->addSql('ALTER TABLE user CHANGE first_name first_name VARCHAR(80) NOT NULL, CHANGE last_name last_name VARCHAR(80) NOT NULL, CHANGE birth_date birth_date DATE NOT NULL, CHANGE is_verified is_verified TINYINT NOT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649e7927c74 TO UNIQ_IDENTIFIER_EMAIL');
    }
}
