<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250214204447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ajout de la colonne outfit_id dans la table outfit_item';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outfit_item ALTER outfit_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE outfit_item ALTER outfit_id SET NOT NULL');
    }
}
