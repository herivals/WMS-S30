<?php

namespace App\Command;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:products:import-csv', description: 'Importe les produits depuis un CSV article')]
class ImportProductsFromCsvCommand extends Command
{
    private const DEFAULT_BATCH_SIZE = 50;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'Chemin du CSV (absolu, relatif au répertoire courant ou à la racine du projet)', 'article.csv')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Nombre de lignes par palier de flush', (string) self::DEFAULT_BATCH_SIZE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string) $input->getArgument('file');
        $batchSize = max(1, (int) $input->getOption('batch-size'));

        $path = $this->resolveCsvPath($file);
        if ($path === null) {
            $io->error(sprintf(
                "Fichier CSV introuvable : \"%s\".\nChemins essayés :\n  - %s\n  - %s\n  - %s",
                $file,
                $file,
                getcwd() . DIRECTORY_SEPARATOR . $file,
                dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $file,
            ));
            return Command::FAILURE;
        }
        $io->writeln(sprintf('<info>Fichier :</info> %s', $path));

        @ini_set('memory_limit', '-1');

        $env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
        if ($env !== 'prod') {
            $io->note('Pour de gros imports, préférer : php bin/console app:products:import-csv --env=prod (désactive le profiler Doctrine).');
        }

        $this->disableSqlLogger();

        $totalLines = max(0, $this->countLines($path) - 1);
        $progressBar = $io->createProgressBar($totalLines);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%%  %elapsed:6s% / %estimated:-6s%  mem: %memory:6s%');
        $progressBar->start();

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

            $reference = $this->textMax($data['Référence'] ?? null, 100);
            $designation = $this->textMax($data['Désignation'] ?? null, 255);
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

            $product->setDesignation($this->textMax($data['Désignation'] ?? null, 255) ?? $designation);
            $product->setRefClient($this->textMax($data['Ref Client'] ?? null, 120));
            $product->setFamille($this->textMax($data['Famille'] ?? null, 120));
            $product->setDeposant($this->textMax($data['Déposant'] ?? null, 120));
            $product->setChoixLotEnPrep($this->bool($data['Choix lot en prep'] ?? null));
            $product->setEan13($this->textMax($data['Codebarres'] ?? null, 13));
            $product->setCondit($this->textMax($data['Condit'] ?? null, 20));
            $product->setConsommable($this->bool($data['Consommable ?'] ?? null));
            $product->setControleUnicite($this->textMax($data["Contrôle d'unicité"] ?? null, 80));
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
            $product->setEtat($this->textMax($data['Etat'] ?? null, 60));
            $product->setEtiqArticle($this->textMax($data['Etiq Article'] ?? null, 120));
            $product->setFournisseur($this->textMax($data['Fournisseur'] ?? null, 120));
            $product->setFrequenceInvent($this->int($data['Fréquence invent'] ?? null));
            $product->setGestionDluo($this->bool($data['Gestion DLUO'] ?? null));
            $product->setGestionLot($this->bool($data['Gestion LOT'] ?? null));
            $product->setInfoArticle1($this->textMax($data['Info Article 1'] ?? null, 255));
            $product->setInfoArticle2($this->textMax($data['Info Article 2'] ?? null, 255));
            $product->setInfoArticle3($this->textMax($data['Info Article 3'] ?? null, 255));
            $product->setInfoArticle4($this->textMax($data['Info Article 4'] ?? null, 255));
            $product->setInfoArticle5($this->textMax($data['Info Article 5'] ?? null, 255));
            $product->setInfoArticle6($this->textMax($data['Info Article 6'] ?? null, 255));
            $product->setInfoArticle7($this->textMax($data['Info Article 7'] ?? null, 255));
            $product->setInfoArticle8($this->textMax($data['Info Article 8'] ?? null, 255));
            $product->setInfoArticle9($this->textMax($data['Info Article 9'] ?? null, 255));
            $product->setInfoArticle10($this->textMax($data['Info Article 10'] ?? null, 255));
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
            $product->setRefFourn($this->textMax($data['Réf Fourn.'] ?? null, 120));
            $product->setRefModele($this->textMax($data['Réf Modèle'] ?? null, 120));
            $product->setReleveNumeroParc($this->bool($data['Relevé numéro de parc ?'] ?? null));
            $product->setReleveNumeroSerie($this->bool($data['Relevé numéro de série ?'] ?? null));
            $product->setRelveNpEnPrepa($this->bool($data['Relvé NP en Prépa ?'] ?? null));
            $product->setRelveNsEnPrepa($this->bool($data['Relvé NS en Prépa ?'] ?? null));
            $product->setReparateur($this->textMax($data['Réparateur'] ?? null, 120));
            $product->setReparateurS30($this->bool($data['RéparateurS30'] ?? null));
            $product->setRot($this->int($data['Rot'] ?? null));
            $product->setScreenable($this->bool($data['Screenable ?'] ?? null));
            $product->setSeuilAlerte($this->int($data["Seuil d'Alerte"] ?? null));
            $product->setSpcb($this->int($data['SPCB'] ?? null));
            $product->setStatut($this->textMax($data['Statut'] ?? null, 60));
            $product->setTendance($this->textMax($data['Tendance'] ?? null, 60));
            $product->setTendanceCoefficient($this->decimal($data['Tendance Coefficient'] ?? null));
            $product->setTendanceMax($this->decimal($data['Tendance Max'] ?? null));
            $product->setTendanceMin($this->decimal($data['Tendance Min'] ?? null));
            $product->setTypeEdition($this->textMax($data['Type Edition'] ?? null, 80));
            $product->setUlReception001($this->textMax($data['UL de Reception 001'] ?? null, 80));
            $product->setUlReception002($this->textMax($data['UL Reception 002'] ?? null, 80));
            $product->setUlReception003($this->textMax($data['UL Reception 003'] ?? null, 80));
            $product->setUlReception004($this->textMax($data['UL Reception 004'] ?? null, 80));
            $product->setUniteDeMesure($this->textMax($data['Unité de Mesure'] ?? null, 60));

            $product->setGtin($this->textMax($data['Ref Client'] ?? null, 14));

            $progressBar->advance();

            if (($created + $updated) > 0 && ($created + $updated) % $batchSize === 0) {
                $this->flushAndClear();
            }
        }

        fclose($handle);
        $this->flushAndClear();
        $progressBar->finish();
        $io->newLine(2);

        $io->success(sprintf(
            'Import terminé : %d créés, %d mis à jour. Pic mémoire : %s MB.',
            $created,
            $updated,
            number_format(memory_get_peak_usage(true) / 1024 / 1024, 1),
        ));
        return Command::SUCCESS;
    }

    /**
     * Flush le batch courant et libère les entités gérées pour maîtriser la RAM.
     */
    private function flushAndClear(): void
    {
        $this->em->flush();
        $this->em->clear();
        $this->resetDoctrineProfiler();
        gc_collect_cycles();
    }

    /**
     * Désactive les middlewares Doctrine qui gardent chaque requête SQL + backtrace en mémoire
     * (problème bloquant en dev sur des imports > quelques milliers de lignes).
     *
     * La Configuration ne sert qu'aux *nouvelles* connexions : on force un close()
     * pour que la prochaine requête reconstruise une Connection sans les wrappers de debug.
     */
    private function disableSqlLogger(): void
    {
        $connection = $this->em->getConnection();
        $config = $connection->getConfiguration();
        if (method_exists($config, 'setMiddlewares')) {
            $config->setMiddlewares([]);
        }
        if (method_exists($config, 'setSQLLogger')) {
            $config->setSQLLogger(null);
        }

        try {
            $connection->close();
        } catch (\Throwable) {
        }
    }

    /**
     * Purge les données accumulées par les middlewares de debug Doctrine
     * (BacktraceDebugDataHolder / DebugStack) en mode dev.
     */
    private function resetDoctrineProfiler(): void
    {
        try {
            $config = $this->em->getConnection()->getConfiguration();
            if (!method_exists($config, 'getMiddlewares')) {
                return;
            }
            foreach ((array) $config->getMiddlewares() as $middleware) {
                if (is_object($middleware) && method_exists($middleware, 'reset')) {
                    $middleware->reset();
                }
            }
        } catch (\Throwable) {
        }
    }

    /**
     * Résout le chemin du CSV : absolu, relatif au cwd, ou relatif à la racine du projet.
     */
    private function resolveCsvPath(string $file): ?string
    {
        if ($file === '') {
            return null;
        }
        $candidates = [];
        $candidates[] = $file;
        $cwd = getcwd();
        if ($cwd !== false) {
            $candidates[] = $cwd . DIRECTORY_SEPARATOR . $file;
        }
        $candidates[] = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $file;

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return realpath($candidate) ?: $candidate;
            }
        }
        return null;
    }

    private function countLines(string $path): int
    {
        $count = 0;
        $fh = @fopen($path, 'rb');
        if ($fh === false) {
            return 0;
        }
        while (!feof($fh)) {
            $buf = fread($fh, 8192);
            if ($buf === false) {
                break;
            }
            $count += substr_count($buf, "\n");
        }
        fclose($fh);
        return $count;
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

    /**
     * Nettoie + tronque à la longueur max de la colonne SQL pour éviter SQLSTATE[22001].
     */
    private function textMax(?string $value, int $max): ?string
    {
        $v = $this->text($value);
        if ($v === null) {
            return null;
        }
        return mb_substr($v, 0, $max);
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
