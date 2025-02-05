<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250125212722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fix';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE category_item (id SERIAL NOT NULL, category_parent_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, desription VARCHAR(1000) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_94805F59B51A1840 ON category_item (category_parent_id)');
        $this->addSql('CREATE TABLE clothing_item (id SERIAL NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1000) DEFAULT NULL, brand VARCHAR(100) DEFAULT NULL, color VARCHAR(100) NOT NULL, price NUMERIC(5, 2) DEFAULT NULL, ai_tags JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CFE0A4E912469DE2 ON clothing_item (category_id)');
        $this->addSql('COMMENT ON COLUMN clothing_item.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE outfit_item (id SERIAL NOT NULL, outfit_id INT DEFAULT NULL, clothing_item_id INT DEFAULT NULL, wardrobe_id INT NOT NULL, size VARCHAR(10) NOT NULL, purchase_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_98142D2AE96E385 ON outfit_item (outfit_id)');
        $this->addSql('CREATE INDEX IDX_98142D2AA13B545 ON outfit_item (clothing_item_id)');
        $this->addSql('CREATE INDEX IDX_98142D2FC109F73 ON outfit_item (wardrobe_id)');
        $this->addSql('COMMENT ON COLUMN outfit_item.purchase_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE wardrobe (id SERIAL NOT NULL, author_id INT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(1000) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2C80050EF675F31B ON wardrobe (author_id)');
        $this->addSql('COMMENT ON COLUMN wardrobe.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE category_item ADD CONSTRAINT FK_94805F59B51A1840 FOREIGN KEY (category_parent_id) REFERENCES category_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clothing_item ADD CONSTRAINT FK_CFE0A4E912469DE2 FOREIGN KEY (category_id) REFERENCES category_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE outfit_item ADD CONSTRAINT FK_98142D2AE96E385 FOREIGN KEY (outfit_id) REFERENCES outfit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE outfit_item ADD CONSTRAINT FK_98142D2AA13B545 FOREIGN KEY (clothing_item_id) REFERENCES clothing_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE outfit_item ADD CONSTRAINT FK_98142D2FC109F73 FOREIGN KEY (wardrobe_id) REFERENCES wardrobe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wardrobe ADD CONSTRAINT FK_2C80050EF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE category_item DROP CONSTRAINT FK_94805F59B51A1840');
        $this->addSql('ALTER TABLE clothing_item DROP CONSTRAINT FK_CFE0A4E912469DE2');
        $this->addSql('ALTER TABLE outfit_item DROP CONSTRAINT FK_98142D2AE96E385');
        $this->addSql('ALTER TABLE outfit_item DROP CONSTRAINT FK_98142D2AA13B545');
        $this->addSql('ALTER TABLE outfit_item DROP CONSTRAINT FK_98142D2FC109F73');
        $this->addSql('ALTER TABLE wardrobe DROP CONSTRAINT FK_2C80050EF675F31B');
        $this->addSql('DROP TABLE category_item');
        $this->addSql('DROP TABLE clothing_item');
        $this->addSql('DROP TABLE outfit_item');
        $this->addSql('DROP TABLE wardrobe');
    }
}
