<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260421190551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_c744045577153098');
        $this->addSql('ALTER TABLE client ADD bat_retour VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD ville_soc VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD adresse2_soc VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD pays_soc VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD commentaire_soc TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD releve_numero_parc BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE client ADD contact_soc VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD releve_numero_serie BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE client ADD code_pays_soc VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD adresse1_soc VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD cp_soc VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD fax_soc VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD quartier_retour VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD responsable_compte VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD nb_jours_travailles_sur3_mois INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client RENAME COLUMN nom TO nom_deposant');
        $this->addSql('ALTER TABLE client RENAME COLUMN code TO deposant');
        $this->addSql('ALTER TABLE client ADD tel_soc VARCHAR(50) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C744045564C4A25E ON client (deposant)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_C744045564C4A25E');
        $this->addSql('ALTER TABLE client DROP bat_retour');
        $this->addSql('ALTER TABLE client DROP ville_soc');
        $this->addSql('ALTER TABLE client DROP tel_soc');
        $this->addSql('ALTER TABLE client DROP adresse2_soc');
        $this->addSql('ALTER TABLE client DROP pays_soc');
        $this->addSql('ALTER TABLE client DROP commentaire_soc');
        $this->addSql('ALTER TABLE client DROP releve_numero_parc');
        $this->addSql('ALTER TABLE client DROP contact_soc');
        $this->addSql('ALTER TABLE client DROP releve_numero_serie');
        $this->addSql('ALTER TABLE client DROP code_pays_soc');
        $this->addSql('ALTER TABLE client DROP adresse1_soc');
        $this->addSql('ALTER TABLE client DROP cp_soc');
        $this->addSql('ALTER TABLE client DROP fax_soc');
        $this->addSql('ALTER TABLE client DROP quartier_retour');
        $this->addSql('ALTER TABLE client DROP responsable_compte');
        $this->addSql('ALTER TABLE client DROP nb_jours_travailles_sur3_mois');
        $this->addSql('ALTER TABLE client RENAME COLUMN deposant TO code');
        $this->addSql('ALTER TABLE client RENAME COLUMN nom_deposant TO nom');
        $this->addSql('CREATE UNIQUE INDEX uniq_c744045577153098 ON client (code)');
    }
}
