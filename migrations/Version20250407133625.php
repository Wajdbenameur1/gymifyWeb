<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407133625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activité CHANGE type type ENUM(\'PERSONAL_TRAINING\', \'GROUP_ACTIVITY\', \'FITNESS_CONSULTATION\')');
        $this->addSql('ALTER TABLE cours CHANGE objectif objectif ENUM(\'PERTE_PROIDS\',\'PRISE_DE_MASSE\',\'ENDURANCE\',\'RELAXATION\')');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3A76ED395');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB34B89032C');
        $this->addSql('ALTER TABLE reactions CHANGE type type VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB34B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activité CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cours CHANGE objectif objectif VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3A76ED395');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB34B89032C');
        $this->addSql('ALTER TABLE reactions CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB34B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
    }
}
