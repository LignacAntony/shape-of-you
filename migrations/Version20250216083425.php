<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250216083425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ajout de la colonne outfit_id dans la table outfit_item';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outfit_item DROP CONSTRAINT fk_98142d2ae96e385');
        $this->addSql('DROP INDEX idx_98142d2ae96e385');
        $this->addSql('ALTER TABLE outfit_item DROP outfit_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE outfit_item ADD outfit_id INT NOT NULL');
        $this->addSql('ALTER TABLE outfit_item ADD CONSTRAINT fk_98142d2ae96e385 FOREIGN KEY (outfit_id) REFERENCES outfit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_98142d2ae96e385 ON outfit_item (outfit_id)');
    }
}
