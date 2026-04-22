<?php

namespace App\Command;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:products:import-csv', description: 'Importe les produits depuis un CSV article')]
class ImportProductsFromCsvCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::OPTIONAL, 'Chemin du CSV', 'article.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string) $input->getArgument('file');
        $path = is_file($file) ? $file : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $file;

        if (!is_file($path)) {
            $io->error(sprintf('Fichier introuvable: %s', $path));
            return Command::FAILURE;
        }

        $handle = fopen($path, 'rb');
        if (!$handle) {
            $io->error('Impossible d\'ouvrir le fichier CSV.');
            return Command::FAILURE;
        }

        $headers = fgetcsv($handle, 0, ';');
        if (!is_array($headers)) {
            fclose($handle);
            $io->error('En-têtes CSV invalides.');
            return Command::FAILURE;
        }
        $headers = array_map([$this, 'decode'], $headers);

        $created = 0;
        $updated = 0;
        $rowNum = 1;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNum++;
            if (!is_array($row)) {
                continue;
            }
            $row = array_map([$this, 'decode'], $row);
            $headerCount = count($headers);
            $rowCount = count($row);
            if ($rowCount < $headerCount) {
                $row = array_pad($row, $headerCount, '');
            } elseif ($rowCount > $headerCount) {
                $row = array_slice($row, 0, $headerCount);
            }
            $data = array_combine($headers, $row);
            if (!is_array($data)) {
                continue;
            }

            $reference = $this->text($data['Référence'] ?? null);
            $designation = $this->text($data['Désignation'] ?? null);
            if ($reference === null || $designation === null) {
                $io->warning(sprintf('Ligne %d ignorée (Référence/Désignation manquante).', $rowNum));
                continue;
            }

            $product = $this->productRepository->findOneBy(['reference' => $reference]);
            if (!$product) {
                $product = new Product();
                $product->setReference($reference);
                $created++;
                $this->em->persist($product);
            } else {
                $updated++;
            }

            $product->setDesignation($designation);
            $product->setRefClient($this->text($data['Ref Client'] ?? null));
            $product->setFamille($this->text($data['Famille'] ?? null));
            $product->setDeposant($this->text($data['Déposant'] ?? null));
            $product->setChoixLotEnPrep($this->bool($data['Choix lot en prep'] ?? null));
            $product->setEan13($this->truncate($this->text($data['Codebarres'] ?? null), 13));
            $product->setCondit($this->text($data['Condit'] ?? null));
            $product->setConsommable($this->bool($data['Consommable ?'] ?? null));
            $product->setControleUnicite($this->text($data["Contrôle d'unicité"] ?? null));
            $product->setDateCreation($this->date($data['Date Création'] ?? null));
            $product->setDatePrevueInvent($this->date($data['Date prévue invent'] ?? null));
            $product->setDelaiDluo($this->decimal($data['Délai DLUO'] ?? null));
            $product->setDelaiFournisseur($this->decimal($data['Délai Fournisseur'] ?? null));
            $product->setDelaiReappro($this->decimal($data['Délai Réappro'] ?? null));
            $product->setDernierInvent($this->date($data['Dernier Invent'] ?? null));
            $product->setDernierePrep($this->date($data['Dernière Prep'] ?? null));
            $product->setDerniereRecep($this->date($data['Dernière Recep'] ?? null));
            $product->setDesigLongue($this->text($data['Désig. Longue'] ?? null));
            $product->setDotation($this->bool($data['Dotation'] ?? null));
            $product->setEncoursExpedition001($this->int($data['Encours Expédition 001'] ?? null));
            $product->setEncoursExpedition002($this->int($data['Encours Expédition 002'] ?? null));
            $product->setEncoursExpedition003($this->int($data['Encours Expédition 003'] ?? null));
            $product->setEncoursExpedition004($this->int($data['Encours Expédition 004'] ?? null));
            $product->setEncoursReception001($this->int($data['Encours Réception 001'] ?? null));
            $product->setEncoursReception002($this->int($data['Encours Réception 002'] ?? null));
            $product->setEncoursReception003($this->int($data['Encours Réception 003'] ?? null));
            $product->setEncoursReception004($this->int($data['Encours Réception 004'] ?? null));
            $product->setEstUnKit($this->bool($data['Est un Kit?'] ?? null));
            $product->setEtat($this->text($data['Etat'] ?? null));
            $product->setEtiqArticle($this->text($data['Etiq Article'] ?? null));
            $product->setFournisseur($this->text($data['Fournisseur'] ?? null));
            $product->setFrequenceInvent($this->int($data['Fréquence invent'] ?? null));
            $product->setGestionDluo($this->bool($data['Gestion DLUO'] ?? null));
            $product->setGestionLot($this->bool($data['Gestion LOT'] ?? null));
            $product->setInfoArticle1($this->text($data['Info Article 1'] ?? null));
            $product->setInfoArticle2($this->text($data['Info Article 2'] ?? null));
            $product->setInfoArticle3($this->text($data['Info Article 3'] ?? null));
            $product->setInfoArticle4($this->text($data['Info Article 4'] ?? null));
            $product->setInfoArticle5($this->text($data['Info Article 5'] ?? null));
            $product->setInfoArticle6($this->text($data['Info Article 6'] ?? null));
            $product->setInfoArticle7($this->text($data['Info Article 7'] ?? null));
            $product->setInfoArticle8($this->text($data['Info Article 8'] ?? null));
            $product->setInfoArticle9($this->text($data['Info Article 9'] ?? null));
            $product->setInfoArticle10($this->text($data['Info Article 10'] ?? null));
            $product->setInstructionPersonnalisation($this->text($data['Instrucion personnalisation'] ?? null));
            $product->setInventEnCours($this->bool($data['Invent en cours?'] ?? null));
            $product->setKitALaVolee($this->bool($data['Kit à la volée?'] ?? null));
            $product->setNbComposants($this->decimal($data['Nb Composants'] ?? null, 3));
            $product->setNbInvent($this->int($data['Nb invent'] ?? null));
            $product->setObsolete($this->bool($data['Obsolète'] ?? null));
            $product->setParametre($this->bool($data['Paramétré?'] ?? null));
            $product->setPcb($this->int($data['PCB'] ?? null));
            $product->setPersonnalisation($this->bool($data['Personnalisation ?'] ?? null));
            $product->setPlusDe30Kg($this->bool($data['Plus de 30 KG'] ?? null));
            $product->setPrixUnitHt($this->decimal($data['Prix Unit HT'] ?? null));
            $product->setQteDispoBad($this->int($data['Qté Dispo Bad'] ?? null));
            $product->setQteDispoGood($this->int($data['Qté Dispo Good'] ?? null));
            $product->setQteEnQuarantaine001($this->int($data['Qté en Quarantaine 001'] ?? null));
            $product->setQteEnQuarantaine002($this->int($data['Qté en Quarantaine 002'] ?? null));
            $product->setQteEnQuarantaine003($this->int($data['Qté en Quarantaine 003'] ?? null));
            $product->setQteEnQuarantaine004($this->int($data['Qté en Quarantaine 004'] ?? null));
            $product->setQtePreconiseeCommande($this->decimal($data['Qté préconisée à la commande'] ?? null));
            $product->setQteReservee001($this->int($data['Qté Réservée 001'] ?? null));
            $product->setQteReservee002($this->int($data['Qté Réservée 002'] ?? null));
            $product->setQteReservee003($this->int($data['Qté Réservée 003'] ?? null));
            $product->setQteReservee004($this->int($data['Qté Réservée 004'] ?? null));
            $product->setQteStockee001($this->int($data['Qté Stockée 001'] ?? null));
            $product->setQteStockee002($this->int($data['Qté Stockée 002'] ?? null));
            $product->setQteStockee003($this->int($data['Qté Stockée 003'] ?? null));
            $product->setQteStockee004($this->int($data['Qté Stockée 004'] ?? null));
            $product->setReassortAtelier($this->bool($data['Réassort Atelier'] ?? null));
            $product->setRefFourn($this->text($data['Réf Fourn.'] ?? null));
            $product->setRefModele($this->text($data['Réf Modèle'] ?? null));
            $product->setReleveNumeroParc($this->bool($data['Relevé numéro de parc ?'] ?? null));
            $product->setReleveNumeroSerie($this->bool($data['Relevé numéro de série ?'] ?? null));
            $product->setRelveNpEnPrepa($this->bool($data['Relvé NP en Prépa ?'] ?? null));
            $product->setRelveNsEnPrepa($this->bool($data['Relvé NS en Prépa ?'] ?? null));
            $product->setReparateur($this->text($data['Réparateur'] ?? null));
            $product->setReparateurS30($this->bool($data['RéparateurS30'] ?? null));
            $product->setRot($this->int($data['Rot'] ?? null));
            $product->setScreenable($this->bool($data['Screenable ?'] ?? null));
            $product->setSeuilAlerte($this->int($data["Seuil d'Alerte"] ?? null));
            $product->setSpcb($this->int($data['SPCB'] ?? null));
            $product->setStatut($this->text($data['Statut'] ?? null));
            $product->setTendance($this->text($data['Tendance'] ?? null));
            $product->setTendanceCoefficient($this->decimal($data['Tendance Coefficient'] ?? null));
            $product->setTendanceMax($this->decimal($data['Tendance Max'] ?? null));
            $product->setTendanceMin($this->decimal($data['Tendance Min'] ?? null));
            $product->setTypeEdition($this->text($data['Type Edition'] ?? null));
            $product->setUlReception001($this->text($data['UL de Reception 001'] ?? null));
            $product->setUlReception002($this->text($data['UL Reception 002'] ?? null));
            $product->setUlReception003($this->text($data['UL Reception 003'] ?? null));
            $product->setUlReception004($this->text($data['UL Reception 004'] ?? null));
            $product->setUniteDeMesure($this->text($data['Unité de Mesure'] ?? null));

            $gtin = $this->truncate($this->text($data['Ref Client'] ?? null), 14);
            $product->setGtin($gtin);
        }

        fclose($handle);
        $this->em->flush();

        $io->success(sprintf('Import terminé: %d créés, %d mis à jour.', $created, $updated));
        return Command::SUCCESS;
    }

    private function decode(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) mb_convert_encoding($value, 'UTF-8', 'Windows-1252'));
    }

    private function text(?string $value): ?string
    {
        $v = trim((string) ($value ?? ''));
        return $v === '' ? null : $v;
    }

    private function bool(?string $value): bool
    {
        $v = mb_strtolower(trim((string) ($value ?? '')));
        return in_array($v, ['oui', 'true', '1', 'yes'], true);
    }

    private function int(?string $value): ?int
    {
        $v = $this->text($value);
        if ($v === null) {
            return null;
        }
        $v = str_replace([' ', ','], ['', '.'], $v);
        if (!is_numeric($v)) {
            return null;
        }
        return (int) round((float) $v);
    }

    private function decimal(?string $value, int $scale = 2): ?string
    {
        $v = $this->text($value);
        if ($v === null) {
            return null;
        }
        $v = str_replace([' ', ','], ['', '.'], $v);
        if (!is_numeric($v)) {
            return null;
        }
        return number_format((float) $v, $scale, '.', '');
    }

    private function date(?string $value): ?\DateTimeImmutable
    {
        $v = $this->text($value);
        if ($v === null) {
            return null;
        }
        $date = \DateTimeImmutable::createFromFormat('d/m/Y', $v);
        return $date ?: null;
    }

    private function truncate(?string $value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }
        return mb_substr($value, 0, $length);
    }
}
