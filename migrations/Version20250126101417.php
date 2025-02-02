<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250126101417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'augmenter la range des chiffres';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clothing_item ALTER price TYPE NUMERIC(10, 0)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE clothing_item ALTER price TYPE NUMERIC(5, 2)');
    }
}
