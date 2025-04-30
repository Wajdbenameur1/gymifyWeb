<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240412194735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add phone_number column to commande table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande ADD phone_number VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande DROP phone_number');
    }
} 