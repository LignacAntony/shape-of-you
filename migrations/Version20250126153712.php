<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250126153712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'rename desription column to description';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category_item RENAME COLUMN desription TO description');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE category_item RENAME COLUMN description TO desription');
    }
}
