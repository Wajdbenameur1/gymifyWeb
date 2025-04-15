<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407133953 extends AbstractMigration
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
        $this->addSql('DROP INDEX IDX_38737FB3A76ED395 ON reactions');
        $this->addSql('DROP INDEX IDX_38737FB34B89032C ON reactions');
        $this->addSql('ALTER TABLE reactions ADD id_User INT DEFAULT NULL, ADD postId INT DEFAULT NULL, DROP user_id, DROP post_id');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3A6816575 FOREIGN KEY (id_User) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3E094D20D FOREIGN KEY (postId) REFERENCES post (id)');
        $this->addSql('CREATE INDEX IDX_38737FB3A6816575 ON reactions (id_User)');
        $this->addSql('CREATE INDEX IDX_38737FB3E094D20D ON reactions (postId)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activité CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cours CHANGE objectif objectif VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3A6816575');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3E094D20D');
        $this->addSql('DROP INDEX IDX_38737FB3A6816575 ON reactions');
        $this->addSql('DROP INDEX IDX_38737FB3E094D20D ON reactions');
        $this->addSql('ALTER TABLE reactions ADD user_id INT DEFAULT NULL, ADD post_id INT DEFAULT NULL, DROP id_User, DROP postId');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB34B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('CREATE INDEX IDX_38737FB3A76ED395 ON reactions (user_id)');
        $this->addSql('CREATE INDEX IDX_38737FB34B89032C ON reactions (post_id)');
    }
}
