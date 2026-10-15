<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015101453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE deadline (id UUID NOT NULL, title VARCHAR(255) NOT NULL, due_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(50) NOT NULL, risk_level VARCHAR(20) DEFAULT \'low\' NOT NULL, funding_project_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B74774F25BC2BFC3 ON deadline (funding_project_id)');
        $this->addSql('CREATE TABLE funding_projects (id UUID NOT NULL, title VARCHAR(255) NOT NULL, budget INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE "users" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, totp_secret VARCHAR(255) DEFAULT NULL, failed2fa_attempts INT NOT NULL, is_locked BOOLEAN NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON "users" (email)');
        $this->addSql('ALTER TABLE deadline ADD CONSTRAINT FK_B74774F25BC2BFC3 FOREIGN KEY (funding_project_id) REFERENCES funding_projects (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deadline DROP CONSTRAINT FK_B74774F25BC2BFC3');
        $this->addSql('DROP TABLE deadline');
        $this->addSql('DROP TABLE funding_projects');
        $this->addSql('DROP TABLE "users"');
    }
}
