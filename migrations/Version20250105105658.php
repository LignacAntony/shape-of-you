<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250105105658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'modify user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD firstname VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD lastname VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD username VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD morphology_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD is_verified BOOLEAN DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP firstname');
        $this->addSql('ALTER TABLE "user" DROP lastname');
        $this->addSql('ALTER TABLE "user" DROP username');
        $this->addSql('ALTER TABLE "user" DROP created_at');
        $this->addSql('ALTER TABLE "user" DROP updated_at');
        $this->addSql('ALTER TABLE "user" DROP morphology_type');
        $this->addSql('ALTER TABLE "user" DROP is_verified');
    }
}
