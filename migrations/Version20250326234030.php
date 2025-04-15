<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326234030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C12CC158');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CEEE824C8');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C92419D3E');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CE7925747');
        $this->addSql('DROP INDEX IDX_FDCA8C9C92419D3E ON cours');
        $this->addSql('DROP INDEX IDX_FDCA8C9CE7925747 ON cours');
        $this->addSql('DROP INDEX IDX_FDCA8C9CEEE824C8 ON cours');
        $this->addSql('DROP INDEX IDX_FDCA8C9C12CC158 ON cours');
        $this->addSql('ALTER TABLE cours ADD activité_id INT DEFAULT NULL, ADD planning_id INT DEFAULT NULL, ADD entaineur_id INT DEFAULT NULL, ADD salle_id INT DEFAULT NULL, DROP activité_id_id, DROP planning_id_id, DROP entaineur_id_id, DROP salle_id_id');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CED02027C FOREIGN KEY (activité_id) REFERENCES activité (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C3D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C582CD907 FOREIGN KEY (entaineur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CDC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9CED02027C ON cours (activité_id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9C3D865311 ON cours (planning_id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9C582CD907 ON cours (entaineur_id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9CDC304035 ON cours (salle_id)');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF612CC158');
        $this->addSql('DROP INDEX IDX_D499BFF612CC158 ON planning');
        $this->addSql('ALTER TABLE planning CHANGE entaineur_id_id entaineur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6582CD907 FOREIGN KEY (entaineur_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D499BFF6582CD907 ON planning (entaineur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CED02027C');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C3D865311');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C582CD907');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CDC304035');
        $this->addSql('DROP INDEX IDX_FDCA8C9CED02027C ON cours');
        $this->addSql('DROP INDEX IDX_FDCA8C9C3D865311 ON cours');
        $this->addSql('DROP INDEX IDX_FDCA8C9C582CD907 ON cours');
        $this->addSql('DROP INDEX IDX_FDCA8C9CDC304035 ON cours');
        $this->addSql('ALTER TABLE cours ADD activité_id_id INT DEFAULT NULL, ADD planning_id_id INT DEFAULT NULL, ADD entaineur_id_id INT DEFAULT NULL, ADD salle_id_id INT DEFAULT NULL, DROP activité_id, DROP planning_id, DROP entaineur_id, DROP salle_id');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C12CC158 FOREIGN KEY (entaineur_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CEEE824C8 FOREIGN KEY (planning_id_id) REFERENCES planning (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C92419D3E FOREIGN KEY (salle_id_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CE7925747 FOREIGN KEY (activité_id_id) REFERENCES activité (id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9C92419D3E ON cours (salle_id_id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9CE7925747 ON cours (activité_id_id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9CEEE824C8 ON cours (planning_id_id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9C12CC158 ON cours (entaineur_id_id)');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6582CD907');
        $this->addSql('DROP INDEX IDX_D499BFF6582CD907 ON planning');
        $this->addSql('ALTER TABLE planning CHANGE entaineur_id entaineur_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF612CC158 FOREIGN KEY (entaineur_id_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D499BFF612CC158 ON planning (entaineur_id_id)');
    }
}
