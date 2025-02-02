<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250126105811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'modify outfit_id in review table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C6AE96E385');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6AE96E385 FOREIGN KEY (outfit_id) REFERENCES outfit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT fk_794381c6ae96e385');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_794381c6ae96e385 FOREIGN KEY (outfit_id) REFERENCES outfit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
