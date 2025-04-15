<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407140346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE entraineur (id INT AUTO_INCREMENT NOT NULL, entraineur VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE abonnementdata');
        $this->addSql('ALTER TABLE equipe CHANGE type niveau VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD type VARCHAR(255) NOT NULL, CHANGE role role VARCHAR(255) NOT NULL, CHANGE specialite specialite VARCHAR(100) DEFAULT NULL, CHANGE image_url image_url VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abonnementdata (id_Abonnement INT NOT NULL, sportifId INT NOT NULL, Salle_Id INT NOT NULL, montant DOUBLE PRECISION NOT NULL, INDEX id_Abonnement (id_Abonnement), INDEX Salle_Id (Salle_Id), INDEX sportifId (sportifId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE entraineur');
        $this->addSql('ALTER TABLE equipe CHANGE niveau type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user DROP type, CHANGE role role VARCHAR(255) NOT NULL COLLATE `utf8mb4_bin`, CHANGE specialite specialite VARCHAR(100) NOT NULL, CHANGE image_url image_url VARCHAR(255) NOT NULL');
    }
}
