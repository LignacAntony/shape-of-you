<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250126105304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'modify outfit_id in like table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT FK_AC6340B3AE96E385');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT FK_AC6340B3AE96E385 FOREIGN KEY (outfit_id) REFERENCES outfit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT fk_ac6340b3ae96e385');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT fk_ac6340b3ae96e385 FOREIGN KEY (outfit_id) REFERENCES outfit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
