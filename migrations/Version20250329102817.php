<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329102817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipe_event (id INT AUTO_INCREMENT NOT NULL, equipe_id INT DEFAULT NULL, event_id INT DEFAULT NULL, INDEX IDX_61A29C2D6D861B89 (equipe_id), INDEX IDX_61A29C2D71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipe_event ADD CONSTRAINT FK_61A29C2D6D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE equipe_event ADD CONSTRAINT FK_61A29C2D71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE events ADD salle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574ADC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('CREATE INDEX IDX_5387574ADC304035 ON events (salle_id)');
        $this->addSql('ALTER TABLE user ADD equipe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6496D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6496D861B89 ON user (equipe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipe_event DROP FOREIGN KEY FK_61A29C2D6D861B89');
        $this->addSql('ALTER TABLE equipe_event DROP FOREIGN KEY FK_61A29C2D71F7E88B');
        $this->addSql('DROP TABLE equipe_event');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574ADC304035');
        $this->addSql('DROP INDEX IDX_5387574ADC304035 ON events');
        $this->addSql('ALTER TABLE events DROP salle_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6496D861B89');
        $this->addSql('DROP INDEX IDX_8D93D6496D861B89 ON user');
        $this->addSql('ALTER TABLE user DROP equipe_id');
    }
}
