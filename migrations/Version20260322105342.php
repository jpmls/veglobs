<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260322105342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_station CHANGE external_id external_id VARCHAR(50) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE address address LONGTEXT DEFAULT NULL, CHANGE lat lat DOUBLE PRECISION DEFAULT NULL, CHANGE lon lon DOUBLE PRECISION DEFAULT NULL, CHANGE banking banking TINYINT DEFAULT NULL, CHANGE bonus bonus TINYINT DEFAULT NULL, CHANGE status status VARCHAR(50) DEFAULT NULL, CHANGE contract_name contract_name VARCHAR(100) DEFAULT NULL, CHANGE last_update last_update DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2585618C9F75D7B0 ON bike_station (external_id)');
        $this->addSql('CREATE INDEX idx_bike_station_external_id ON bike_station (external_id)');
        $this->addSql('CREATE INDEX idx_bike_station_status ON bike_station (status)');
        $this->addSql('ALTER TABLE comment ADD votes_up INT DEFAULT NULL, ADD votes_down INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_2585618C9F75D7B0 ON bike_station');
        $this->addSql('DROP INDEX idx_bike_station_external_id ON bike_station');
        $this->addSql('DROP INDEX idx_bike_station_status ON bike_station');
        $this->addSql('ALTER TABLE bike_station CHANGE external_id external_id VARCHAR(10) DEFAULT NULL, CHANGE name name VARCHAR(50) DEFAULT NULL, CHANGE address address VARCHAR(123) DEFAULT NULL, CHANGE lat lat VARCHAR(18) DEFAULT NULL, CHANGE lon lon VARCHAR(18) DEFAULT NULL, CHANGE banking banking VARCHAR(5) DEFAULT NULL, CHANGE bonus bonus VARCHAR(5) DEFAULT NULL, CHANGE status status VARCHAR(6) DEFAULT NULL, CHANGE contract_name contract_name VARCHAR(18) DEFAULT NULL, CHANGE last_update last_update VARCHAR(25) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE comment DROP votes_up, DROP votes_down');
    }
}
