<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012073753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE deadline (id UUID NOT NULL, title VARCHAR(255) NOT NULL, due_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(50) NOT NULL, funding_project_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B74774F25BC2BFC3 ON deadline (funding_project_id)');
        $this->addSql('ALTER TABLE deadline ADD CONSTRAINT FK_B74774F25BC2BFC3 FOREIGN KEY (funding_project_id) REFERENCES funding_projects (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deadline DROP CONSTRAINT FK_B74774F25BC2BFC3');
        $this->addSql('DROP TABLE deadline');
    }
}
