<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250213214737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ajout de la colonne images dans la table outfit et wardrobe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outfit ADD images JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE wardrobe ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE outfit DROP images');
        $this->addSql('ALTER TABLE wardrobe DROP image');
    }
}
