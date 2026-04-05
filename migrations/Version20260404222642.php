<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260404222642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add source and views fields to news only';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE news ADD source VARCHAR(20) NOT NULL DEFAULT 'official'");
        $this->addSql("ALTER TABLE news ADD views INT NOT NULL DEFAULT 0");
        $this->addSql("ALTER TABLE news CHANGE type type VARCHAR(30) NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE news DROP source, DROP views, CHANGE type type VARCHAR(20) NOT NULL');
    }
}