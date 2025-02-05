<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250126165722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_item DROP CONSTRAINT FK_94805F59B51A1840');
        $this->addSql('ALTER TABLE category_item ADD CONSTRAINT FK_94805F59B51A1840 FOREIGN KEY (category_parent_id) REFERENCES category_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE category_item DROP CONSTRAINT fk_94805f59b51a1840');
        $this->addSql('ALTER TABLE category_item ADD CONSTRAINT fk_94805f59b51a1840 FOREIGN KEY (category_parent_id) REFERENCES category_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
