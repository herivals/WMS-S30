<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260425120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout user_name sur stock_movement';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stock_movement ADD user_name VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stock_movement DROP COLUMN user_name');
    }
}
