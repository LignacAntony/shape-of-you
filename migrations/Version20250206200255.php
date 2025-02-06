<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250206200255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ajout de la colonne updated_at dans la table profile';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE profile ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE profile DROP updated_at');
    }
}
