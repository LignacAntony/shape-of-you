<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250126110228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'modify outfit_id in outfit_item table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outfit_item DROP CONSTRAINT FK_98142D2AE96E385');
        $this->addSql('ALTER TABLE outfit_item ALTER outfit_id SET NOT NULL');
        $this->addSql('ALTER TABLE outfit_item ADD CONSTRAINT FK_98142D2AE96E385 FOREIGN KEY (outfit_id) REFERENCES outfit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE outfit_item DROP CONSTRAINT fk_98142d2ae96e385');
        $this->addSql('ALTER TABLE outfit_item ALTER outfit_id DROP NOT NULL');
        $this->addSql('ALTER TABLE outfit_item ADD CONSTRAINT fk_98142d2ae96e385 FOREIGN KEY (outfit_id) REFERENCES outfit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
