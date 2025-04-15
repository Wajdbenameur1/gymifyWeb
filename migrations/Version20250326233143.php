<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326233143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BB831D4546');
        $this->addSql('DROP INDEX IDX_351268BB831D4546 ON abonnement');
        $this->addSql('ALTER TABLE abonnement CHANGE id_activite_id activite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BB9B0F88B1 FOREIGN KEY (activite_id) REFERENCES activité (id)');
        $this->addSql('CREATE INDEX IDX_351268BB9B0F88B1 ON abonnement (activite_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BB9B0F88B1');
        $this->addSql('DROP INDEX IDX_351268BB9B0F88B1 ON abonnement');
        $this->addSql('ALTER TABLE abonnement CHANGE activite_id id_activite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BB831D4546 FOREIGN KEY (id_activite_id) REFERENCES activité (id)');
        $this->addSql('CREATE INDEX IDX_351268BB831D4546 ON abonnement (id_activite_id)');
    }
}
