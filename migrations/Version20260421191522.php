<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260421191522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ALTER deposant SET NOT NULL');
        $this->addSql('ALTER TABLE product ADD ref_client VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD famille VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD deposant VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD choix_lot_en_prep BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD condit VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD consommable BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD controle_unicite VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD date_creation DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD date_prevue_invent DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD delai_dluo NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD delai_fournisseur NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD delai_reappro NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD dernier_invent DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD derniere_prep DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD derniere_recep DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD desig_longue TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD dotation BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD encours_expedition001 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD encours_expedition002 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD encours_expedition003 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD encours_expedition004 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD encours_reception001 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD encours_reception002 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD encours_reception003 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD encours_reception004 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD est_un_kit BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD etat VARCHAR(60) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD etiq_article VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD fournisseur VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD frequence_invent INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD gestion_dluo BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD gestion_lot BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD info_article1 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD info_article2 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD info_article3 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD info_article4 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD info_article5 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD info_article6 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD info_article7 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD info_article8 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD info_article9 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD info_article10 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD instruction_personnalisation TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD invent_en_cours BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD kit_ala_volee BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD nb_composants NUMERIC(10, 3) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD nb_invent INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD obsolete BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD parametre BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD pcb INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD personnalisation BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD plus_de30_kg BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD prix_unit_ht NUMERIC(12, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_dispo_bad INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_dispo_good INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_en_quarantaine001 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_en_quarantaine002 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_en_quarantaine003 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_en_quarantaine004 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_preconisee_commande NUMERIC(12, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_reservee001 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_reservee002 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_reservee003 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_reservee004 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_stockee001 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_stockee002 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_stockee003 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD qte_stockee004 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD reassort_atelier BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD ref_fourn VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD ref_modele VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD releve_numero_parc BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD releve_numero_serie BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD relve_np_en_prepa BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD relve_ns_en_prepa BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD reparateur VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD reparateur_s30 BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD rot INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD screenable BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product ADD seuil_alerte INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD spcb INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD statut VARCHAR(60) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD tendance VARCHAR(60) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD tendance_coefficient NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD tendance_max NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD tendance_min NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD type_edition VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD ul_reception001 VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD ul_reception002 VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD ul_reception003 VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD ul_reception004 VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD unite_de_mesure VARCHAR(60) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ALTER deposant DROP NOT NULL');
        $this->addSql('ALTER TABLE product DROP ref_client');
        $this->addSql('ALTER TABLE product DROP famille');
        $this->addSql('ALTER TABLE product DROP deposant');
        $this->addSql('ALTER TABLE product DROP choix_lot_en_prep');
        $this->addSql('ALTER TABLE product DROP condit');
        $this->addSql('ALTER TABLE product DROP consommable');
        $this->addSql('ALTER TABLE product DROP controle_unicite');
        $this->addSql('ALTER TABLE product DROP date_creation');
        $this->addSql('ALTER TABLE product DROP date_prevue_invent');
        $this->addSql('ALTER TABLE product DROP delai_dluo');
        $this->addSql('ALTER TABLE product DROP delai_fournisseur');
        $this->addSql('ALTER TABLE product DROP delai_reappro');
        $this->addSql('ALTER TABLE product DROP dernier_invent');
        $this->addSql('ALTER TABLE product DROP derniere_prep');
        $this->addSql('ALTER TABLE product DROP derniere_recep');
        $this->addSql('ALTER TABLE product DROP desig_longue');
        $this->addSql('ALTER TABLE product DROP dotation');
        $this->addSql('ALTER TABLE product DROP encours_expedition001');
        $this->addSql('ALTER TABLE product DROP encours_expedition002');
        $this->addSql('ALTER TABLE product DROP encours_expedition003');
        $this->addSql('ALTER TABLE product DROP encours_expedition004');
        $this->addSql('ALTER TABLE product DROP encours_reception001');
        $this->addSql('ALTER TABLE product DROP encours_reception002');
        $this->addSql('ALTER TABLE product DROP encours_reception003');
        $this->addSql('ALTER TABLE product DROP encours_reception004');
        $this->addSql('ALTER TABLE product DROP est_un_kit');
        $this->addSql('ALTER TABLE product DROP etat');
        $this->addSql('ALTER TABLE product DROP etiq_article');
        $this->addSql('ALTER TABLE product DROP fournisseur');
        $this->addSql('ALTER TABLE product DROP frequence_invent');
        $this->addSql('ALTER TABLE product DROP gestion_dluo');
        $this->addSql('ALTER TABLE product DROP gestion_lot');
        $this->addSql('ALTER TABLE product DROP info_article1');
        $this->addSql('ALTER TABLE product DROP info_article2');
        $this->addSql('ALTER TABLE product DROP info_article3');
        $this->addSql('ALTER TABLE product DROP info_article4');
        $this->addSql('ALTER TABLE product DROP info_article5');
        $this->addSql('ALTER TABLE product DROP info_article6');
        $this->addSql('ALTER TABLE product DROP info_article7');
        $this->addSql('ALTER TABLE product DROP info_article8');
        $this->addSql('ALTER TABLE product DROP info_article9');
        $this->addSql('ALTER TABLE product DROP info_article10');
        $this->addSql('ALTER TABLE product DROP instruction_personnalisation');
        $this->addSql('ALTER TABLE product DROP invent_en_cours');
        $this->addSql('ALTER TABLE product DROP kit_ala_volee');
        $this->addSql('ALTER TABLE product DROP nb_composants');
        $this->addSql('ALTER TABLE product DROP nb_invent');
        $this->addSql('ALTER TABLE product DROP obsolete');
        $this->addSql('ALTER TABLE product DROP parametre');
        $this->addSql('ALTER TABLE product DROP pcb');
        $this->addSql('ALTER TABLE product DROP personnalisation');
        $this->addSql('ALTER TABLE product DROP plus_de30_kg');
        $this->addSql('ALTER TABLE product DROP prix_unit_ht');
        $this->addSql('ALTER TABLE product DROP qte_dispo_bad');
        $this->addSql('ALTER TABLE product DROP qte_dispo_good');
        $this->addSql('ALTER TABLE product DROP qte_en_quarantaine001');
        $this->addSql('ALTER TABLE product DROP qte_en_quarantaine002');
        $this->addSql('ALTER TABLE product DROP qte_en_quarantaine003');
        $this->addSql('ALTER TABLE product DROP qte_en_quarantaine004');
        $this->addSql('ALTER TABLE product DROP qte_preconisee_commande');
        $this->addSql('ALTER TABLE product DROP qte_reservee001');
        $this->addSql('ALTER TABLE product DROP qte_reservee002');
        $this->addSql('ALTER TABLE product DROP qte_reservee003');
        $this->addSql('ALTER TABLE product DROP qte_reservee004');
        $this->addSql('ALTER TABLE product DROP qte_stockee001');
        $this->addSql('ALTER TABLE product DROP qte_stockee002');
        $this->addSql('ALTER TABLE product DROP qte_stockee003');
        $this->addSql('ALTER TABLE product DROP qte_stockee004');
        $this->addSql('ALTER TABLE product DROP reassort_atelier');
        $this->addSql('ALTER TABLE product DROP ref_fourn');
        $this->addSql('ALTER TABLE product DROP ref_modele');
        $this->addSql('ALTER TABLE product DROP releve_numero_parc');
        $this->addSql('ALTER TABLE product DROP releve_numero_serie');
        $this->addSql('ALTER TABLE product DROP relve_np_en_prepa');
        $this->addSql('ALTER TABLE product DROP relve_ns_en_prepa');
        $this->addSql('ALTER TABLE product DROP reparateur');
        $this->addSql('ALTER TABLE product DROP reparateur_s30');
        $this->addSql('ALTER TABLE product DROP rot');
        $this->addSql('ALTER TABLE product DROP screenable');
        $this->addSql('ALTER TABLE product DROP seuil_alerte');
        $this->addSql('ALTER TABLE product DROP spcb');
        $this->addSql('ALTER TABLE product DROP statut');
        $this->addSql('ALTER TABLE product DROP tendance');
        $this->addSql('ALTER TABLE product DROP tendance_coefficient');
        $this->addSql('ALTER TABLE product DROP tendance_max');
        $this->addSql('ALTER TABLE product DROP tendance_min');
        $this->addSql('ALTER TABLE product DROP type_edition');
        $this->addSql('ALTER TABLE product DROP ul_reception001');
        $this->addSql('ALTER TABLE product DROP ul_reception002');
        $this->addSql('ALTER TABLE product DROP ul_reception003');
        $this->addSql('ALTER TABLE product DROP ul_reception004');
        $this->addSql('ALTER TABLE product DROP unite_de_mesure');
    }
}
