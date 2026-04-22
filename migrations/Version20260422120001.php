<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422120001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix user_preference unique index name to match Doctrine mapping';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER INDEX uniq_11765022a76ed395 RENAME TO UNIQ_FA0E76BFA76ED395');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER INDEX UNIQ_FA0E76BFA76ED395 RENAME TO uniq_11765022a76ed395');
    }
}
