<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326231939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abonnement (id_abonnement INT AUTO_INCREMENT NOT NULL, id_activite_id INT DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, type_abonnement VARCHAR(255) NOT NULL, tarif DOUBLE PRECISION NOT NULL, INDEX IDX_351268BB831D4546 (id_activite_id), PRIMARY KEY(id_abonnement)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE activité (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, description VARCHAR(300) NOT NULL, url VARCHAR(200) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, activité_id_id INT DEFAULT NULL, planning_id_id INT DEFAULT NULL, entaineur_id_id INT DEFAULT NULL, salle_id_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, objectif VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, heur_debut TIME NOT NULL, heur_fin TIME NOT NULL, INDEX IDX_FDCA8C9CE7925747 (activité_id_id), INDEX IDX_FDCA8C9CEEE824C8 (planning_id_id), INDEX IDX_FDCA8C9C12CC158 (entaineur_id_id), INDEX IDX_FDCA8C9C92419D3E (salle_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, niveau VARCHAR(255) NOT NULL, nombre_membres INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, date DATE NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, description VARCHAR(255) NOT NULL, image_url VARCHAR(500) NOT NULL, type VARCHAR(255) NOT NULL, reward VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE infosportif (id INT AUTO_INCREMENT NOT NULL, poids DOUBLE PRECISION NOT NULL, taille DOUBLE PRECISION NOT NULL, age INT NOT NULL, sexe VARCHAR(300) NOT NULL, objectif VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE likes (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement (id_paiement INT AUTO_INCREMENT NOT NULL, amount DOUBLE PRECISION NOT NULL, status VARCHAR(255) NOT NULL, payment_intent_id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', currency VARCHAR(255) NOT NULL, PRIMARY KEY(id_paiement)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE planning (id INT AUTO_INCREMENT NOT NULL, entaineur_id_id INT DEFAULT NULL, date_debut DATE NOT NULL, description VARCHAR(300) NOT NULL, title VARCHAR(255) NOT NULL, date_fin DATE NOT NULL, INDEX IDX_D499BFF612CC158 (entaineur_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id_reclamation INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id_reclamation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, sujet VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, message VARCHAR(255) NOT NULL, date_reponse DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salle (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(200) NOT NULL, adresse VARCHAR(200) NOT NULL, details VARCHAR(500) NOT NULL, num_tel VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, url_photo VARCHAR(500) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, specialite VARCHAR(100) NOT NULL, image_url VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BB831D4546 FOREIGN KEY (id_activite_id) REFERENCES activité (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CE7925747 FOREIGN KEY (activité_id_id) REFERENCES activité (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CEEE824C8 FOREIGN KEY (planning_id_id) REFERENCES planning (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C12CC158 FOREIGN KEY (entaineur_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C92419D3E FOREIGN KEY (salle_id_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF612CC158 FOREIGN KEY (entaineur_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BB831D4546');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CE7925747');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CEEE824C8');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C12CC158');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C92419D3E');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF612CC158');
        $this->addSql('DROP TABLE abonnement');
        $this->addSql('DROP TABLE activité');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE infosportif');
        $this->addSql('DROP TABLE likes');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE planning');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE salle');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
