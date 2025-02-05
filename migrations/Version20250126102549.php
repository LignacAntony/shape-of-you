<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250126102549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'update cloth';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outfit_item DROP CONSTRAINT FK_98142D2AA13B545');
        $this->addSql('ALTER TABLE outfit_item ADD CONSTRAINT FK_98142D2AA13B545 FOREIGN KEY (clothing_item_id) REFERENCES clothing_item (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE outfit_item DROP CONSTRAINT fk_98142d2aa13b545');
        $this->addSql('ALTER TABLE outfit_item ADD CONSTRAINT fk_98142d2aa13b545 FOREIGN KEY (clothing_item_id) REFERENCES clothing_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
