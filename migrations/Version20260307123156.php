<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260307123156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bike_station (id INT AUTO_INCREMENT NOT NULL, external_id VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, address LONGTEXT DEFAULT NULL, lat DOUBLE PRECISION DEFAULT NULL, lon DOUBLE PRECISION DEFAULT NULL, banking TINYINT DEFAULT NULL, bonus TINYINT DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, contract_name VARCHAR(100) DEFAULT NULL, bike_stands INT DEFAULT NULL, available_bike_stands INT DEFAULT NULL, available_bikes INT DEFAULT NULL, last_update DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_2585618C9F75D7B0 (external_id), INDEX idx_bike_station_external_id (external_id), INDEX idx_bike_station_status (status), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE transport_line (id INT AUTO_INCREMENT NOT NULL, external_id VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) DEFAULT NULL, transport_mode VARCHAR(50) DEFAULT NULL, transport_submode VARCHAR(100) DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, operator_ref VARCHAR(100) DEFAULT NULL, operator_name VARCHAR(255) DEFAULT NULL, additional_operators_ref VARCHAR(255) DEFAULT NULL, network_name VARCHAR(255) DEFAULT NULL, color_hex VARCHAR(20) DEFAULT NULL, text_color_hex VARCHAR(20) DEFAULT NULL, color_print_cmjn VARCHAR(50) DEFAULT NULL, text_color_print_hex VARCHAR(20) DEFAULT NULL, accessibility TINYINT DEFAULT NULL, audible_signs_available TINYINT DEFAULT NULL, visual_signs_available TINYINT DEFAULT NULL, group_external_id VARCHAR(50) DEFAULT NULL, group_short_name VARCHAR(255) DEFAULT NULL, notice_title VARCHAR(255) DEFAULT NULL, notice_text LONGTEXT DEFAULT NULL, picto VARCHAR(255) DEFAULT NULL, valid_from_date DATETIME DEFAULT NULL, valid_to_date DATETIME DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, private_code VARCHAR(100) DEFAULT NULL, air_conditioning VARCHAR(50) DEFAULT NULL, bus_contract_id VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_CC4E3739F75D7B0 (external_id), INDEX idx_transport_line_external_id (external_id), INDEX idx_transport_line_mode (transport_mode), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE transport_news_source (id INT AUTO_INCREMENT NOT NULL, external_id VARCHAR(255) NOT NULL, lang VARCHAR(20) DEFAULT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(50) DEFAULT NULL, description LONGTEXT DEFAULT NULL, link_type VARCHAR(50) DEFAULT NULL, link LONGTEXT DEFAULT NULL, title_page VARCHAR(255) DEFAULT NULL, text_page LONGTEXT DEFAULT NULL, button_text VARCHAR(255) DEFAULT NULL, created_source_at DATETIME DEFAULT NULL, updated_source_at DATETIME DEFAULT NULL, raw_html LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_454D5F009F75D7B0 (external_id), INDEX idx_transport_news_source_external_id (external_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE transport_stop (id INT AUTO_INCREMENT NOT NULL, external_id VARCHAR(50) NOT NULL, version VARCHAR(100) DEFAULT NULL, created_source_at DATETIME DEFAULT NULL, changed_source_at DATETIME DEFAULT NULL, name VARCHAR(255) NOT NULL, stop_type VARCHAR(50) DEFAULT NULL, x_epsg2154 VARCHAR(50) DEFAULT NULL, y_epsg2154 VARCHAR(50) DEFAULT NULL, town VARCHAR(255) DEFAULT NULL, postal_region VARCHAR(50) DEFAULT NULL, accessibility VARCHAR(50) DEFAULT NULL, audible_signals VARCHAR(50) DEFAULT NULL, visual_signs VARCHAR(50) DEFAULT NULL, fare_zone VARCHAR(20) DEFAULT NULL, zda_external_id VARCHAR(50) DEFAULT NULL, lat DOUBLE PRECISION DEFAULT NULL, lon DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_648641339F75D7B0 (external_id), INDEX idx_transport_stop_external_id (external_id), INDEX idx_transport_stop_name (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE transport_stop_relation (id INT AUTO_INCREMENT NOT NULL, pde_id VARCHAR(50) DEFAULT NULL, pde_version VARCHAR(100) DEFAULT NULL, zdc_id VARCHAR(50) DEFAULT NULL, zdc_version VARCHAR(100) DEFAULT NULL, zda_id VARCHAR(50) DEFAULT NULL, zda_version VARCHAR(100) DEFAULT NULL, arr_id VARCHAR(50) DEFAULT NULL, arr_version VARCHAR(100) DEFAULT NULL, art_id VARCHAR(50) DEFAULT NULL, art_version VARCHAR(100) DEFAULT NULL, arr_lat DOUBLE PRECISION DEFAULT NULL, arr_lon DOUBLE PRECISION DEFAULT NULL, art_lat DOUBLE PRECISION DEFAULT NULL, art_lon DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, INDEX idx_transport_stop_relation_zdc (zdc_id), INDEX idx_transport_stop_relation_zda (zda_id), INDEX idx_transport_stop_relation_arr (arr_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE bike_station');
        $this->addSql('DROP TABLE transport_line');
        $this->addSql('DROP TABLE transport_news_source');
        $this->addSql('DROP TABLE transport_stop');
        $this->addSql('DROP TABLE transport_stop_relation');
    }
}
