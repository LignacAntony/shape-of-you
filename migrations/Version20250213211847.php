<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250213211847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ajout de la colonne images dans la table clothing_item';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE image (id SERIAL NOT NULL, clothing_item_id INT NOT NULL, path VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C53D045FAA13B545 ON image (clothing_item_id)');
        $this->addSql('COMMENT ON COLUMN image.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FAA13B545 FOREIGN KEY (clothing_item_id) REFERENCES clothing_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clothing_item ADD images JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE image DROP CONSTRAINT FK_C53D045FAA13B545');
        $this->addSql('DROP TABLE image');
        $this->addSql('ALTER TABLE clothing_item DROP images');
    }
}
