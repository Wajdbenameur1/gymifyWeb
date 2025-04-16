<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416233834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD roles JSON NOT NULL COMMENT '(DC2Type:json)', DROP role, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE prenom prenom VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(180) NOT NULL, CHANGE image_url image_url VARCHAR(255) DEFAULT NULL, CHANGE date_naissance date_naissance DATETIME NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` ADD role VARCHAR(255) NOT NULL, DROP roles, CHANGE email email VARCHAR(100) NOT NULL, CHANGE nom nom VARCHAR(50) NOT NULL, CHANGE prenom prenom VARCHAR(50) NOT NULL, CHANGE image_url image_url VARCHAR(100) DEFAULT NULL, CHANGE date_naissance date_naissance DATE NOT NULL
        SQL);
    }
}
