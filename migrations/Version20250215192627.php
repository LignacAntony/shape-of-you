<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250215192627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ajout de la colonne wardrobe_id dans la table outfit';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outfit ADD wardrobe_id INT NOT NULL');
        $this->addSql('ALTER TABLE outfit ADD CONSTRAINT FK_32029601FC109F73 FOREIGN KEY (wardrobe_id) REFERENCES wardrobe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_32029601FC109F73 ON outfit (wardrobe_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE outfit DROP CONSTRAINT FK_32029601FC109F73');
        $this->addSql('DROP INDEX IDX_32029601FC109F73');
        $this->addSql('ALTER TABLE outfit DROP wardrobe_id');
    }
}
