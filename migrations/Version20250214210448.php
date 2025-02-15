<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250214210448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE outfit_outfit_item (outfit_id INT NOT NULL, outfit_item_id INT NOT NULL, PRIMARY KEY(outfit_id, outfit_item_id))');
        $this->addSql('CREATE INDEX IDX_F909BF3DAE96E385 ON outfit_outfit_item (outfit_id)');
        $this->addSql('CREATE INDEX IDX_F909BF3DB0BCBA56 ON outfit_outfit_item (outfit_item_id)');
        $this->addSql('ALTER TABLE outfit_outfit_item ADD CONSTRAINT FK_F909BF3DAE96E385 FOREIGN KEY (outfit_id) REFERENCES outfit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE outfit_outfit_item ADD CONSTRAINT FK_F909BF3DB0BCBA56 FOREIGN KEY (outfit_item_id) REFERENCES outfit_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE outfit_item DROP CONSTRAINT fk_98142d2ae96e385');
        $this->addSql('DROP INDEX idx_98142d2ae96e385');
        $this->addSql('ALTER TABLE outfit_item DROP outfit_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE outfit_outfit_item DROP CONSTRAINT FK_F909BF3DAE96E385');
        $this->addSql('ALTER TABLE outfit_outfit_item DROP CONSTRAINT FK_F909BF3DB0BCBA56');
        $this->addSql('DROP TABLE outfit_outfit_item');
        $this->addSql('ALTER TABLE outfit_item ADD outfit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE outfit_item ADD CONSTRAINT fk_98142d2ae96e385 FOREIGN KEY (outfit_id) REFERENCES outfit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_98142d2ae96e385 ON outfit_item (outfit_id)');
    }
}
