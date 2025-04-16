<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416012809 extends AbstractMigration
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
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activité CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cours CHANGE objectif objectif VARCHAR(255) DEFAULT NULL');
    }
}
