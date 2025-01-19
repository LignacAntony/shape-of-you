<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250119035314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return ' Add profile table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE profile (id SERIAL NOT NULL, app_user_id INT DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, bio TEXT DEFAULT NULL, preferences JSON DEFAULT NULL, measurements JSON DEFAULT NULL, last_login_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8157AA0F4A3353D8 ON profile (app_user_id)');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F4A3353D8 FOREIGN KEY (app_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE profile DROP CONSTRAINT FK_8157AA0F4A3353D8');
        $this->addSql('DROP TABLE profile');
    }
}
