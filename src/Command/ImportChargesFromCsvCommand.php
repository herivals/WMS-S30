<?php

namespace App\Command;

use App\Entity\Charge;
use App\Entity\Client;
use App\Entity\Location;
use App\Entity\Product;
use App\Entity\Reception;
use App\Enum\EtatUL;
use App\Enum\StatutUL;
use App\Enum\TypeFlux;
use App\Enum\TypeReception;
use App\Enum\TypeUnite;
use App\Repository\ChargeRepository;
use App\Repository\ClientRepository;
use App\Repository\LocationRepository;
use App\Repository\ProductRepository;
use App\Repository\ReceptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:charges:import-csv',
    description: 'Importe les charges (unités de stock) depuis un CSV charge_entete_sele'
)]
class ImportChargesFromCsvCommand extends Command
{
    private const DEFAULT_BATCH_SIZE = 50;

    /** @var array<string, int> */
    private array $productIdByRef = [];
    /** @var array<string, int> */
    private array $receptionIdByRef = [];
    /** @var array<string, int> */
    private array $clientIdByDeposant = [];
    /** @var array<string, int> */
    private array $locationIdByCode = [];

    private int $skipped = 0;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ChargeRepository $chargeRepository,
        private readonly ProductRepository $productRepository,
        private readonly ReceptionRepository $receptionRepository,
        private readonly ClientRepository $clientRepository,
        private readonly LocationRepository $locationRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'Chemin du CSV (absolu, relatif au répertoire courant ou à la racine du projet)', 'charge_entete_sele.csv')
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
            $io->note('Pour de gros imports : php bin/console app:charges:import-csv --env=prod (désactive le profiler Doctrine).');
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

            $code = $this->textMax($data['Charge'] ?? null, 50);
            $productRef = $this->text($data['Référence'] ?? null);
            if ($code === null || $productRef === null) {
                $this->skipped++;
                $progressBar->advance();
                continue;
            }

            $productId = $this->resolveProductId($productRef);
            if ($productId === null) {
                $progressBar->clear();
                $io->warning(sprintf('Ligne %d ignorée : produit "%s" introuvable.', $rowNum, $productRef));
                $progressBar->display();
                $this->skipped++;
                $progressBar->advance();
                continue;
            }

            $charge = $this->chargeRepository->findOneBy(['codeCharge' => $code]);
            if (!$charge) {
                $charge = new Charge();
                $charge->setCodeCharge($code);
                $created++;
                $this->em->persist($charge);
            } else {
                $updated++;
            }

            $charge->setProduct($this->em->getReference(Product::class, $productId));

            $charge->setSerialNumber($this->textMax($data['N° Série'] ?? null, 100));
            $charge->setDesignation($this->textMax($data['Désignation'] ?? null, 255));
            $charge->setLot($this->textMax($data['Lot'] ?? null, 100));
            $charge->setLotFabrication($this->textMax($data['Lot de Fabrication'] ?? null, 100));
            $charge->setCreatedBy($this->textMax($data['Utilisateur'] ?? null, 100));
            $charge->setFamilleLogistique($this->textMax($data['Famille Log'] ?? null, 100));
            $charge->setTechnicien($this->textMax($data['Nom technicien'] ?? null, 100));
            $charge->setEtatFinal($this->textMax($data['Etat Final'] ?? null, 100));
            $charge->setUniteFacturation($this->textMax($data['Code UM'] ?? null, 20));

            $charge->setStatut($this->mapStatut($data['Statut'] ?? null));
            $charge->setEtat($this->mapEtat($data['Stock'] ?? null));
            $charge->setTypeUnite($this->mapTypeUnite($data['UL'] ?? $data['Quart'] ?? null));
            $charge->setTypeReception($this->mapTypeReception($data['Type Edition'] ?? null));
            $charge->setTypeFlux($this->mapTypeFlux($data['Type réception'] ?? null));

            $dateCreation = $this->date($data['Date Création'] ?? null);
            if ($dateCreation !== null) {
                $charge->setDateCreation($dateCreation);
            }
            $charge->setDateFabrication($this->date($data['Date Fab'] ?? null));
            $charge->setDluo($this->date($data['DLUO'] ?? null));
            $charge->setDateDernierMouvement($this->date($data['Date Dernier mvt'] ?? ($data['Dern. Mvt'] ?? null)));
            $charge->setDateDernierPrelevement($this->date($data['Date Dernier Prelvt'] ?? null));

            $charge->setAllee($this->textMax($data['Allée'] ?? null, 10));
            $charge->setRack($this->textMax($data['Rng'] ?? null, 10));
            $charge->setNiveau($this->textMax($data['Niv'] ?? null, 10));
            $charge->setPosition($this->textMax($data['Pos'] ?? null, 10));

            $charge->setPoids($this->floatStrip($data['Poids'] ?? null));
            $charge->setLargeur($this->floatStrip($data['Largeur'] ?? null));
            $charge->setHauteur($this->floatStrip($data['Hauteur'] ?? null));
            $charge->setProfondeur($this->floatStrip($data['Profondeur'] ?? null));

            $charge->setQuantite($this->float($data['Qté UL contenu'] ?? null) ?? 0.0);
            $charge->setQuantiteReservee($this->float($data['Qté Rés'] ?? null) ?? 0.0);
            $charge->setQuantiteARegrouper($this->float($data['Qté à Regrouper'] ?? null) ?? 0.0);

            $charge->setPrixAchat($this->floatStrip($data["Prix Unit d'achat."] ?? null));
            $charge->setTempsAtelier($this->float($data['Temps passé en atelier'] ?? null));

            $charge->setMultiReference($this->bool($data['Multi Réf?'] ?? null));
            $charge->setAInventorier($this->bool($data['A Inventorier?'] ?? null));
            if ($this->bool($data['Rebut ?'] ?? null)) {
                $charge->marquerRebut();
            }

            $receptionRef = $this->text($data['Réception'] ?? null);
            if ($receptionRef !== null) {
                $recId = $this->resolveReceptionId($receptionRef);
                if ($recId !== null) {
                    $charge->setReception($this->em->getReference(Reception::class, $recId));
                }
            }

            $deposant = $this->text($data['Déposant'] ?? null);
            if ($deposant !== null) {
                $clientId = $this->resolveClientId($deposant);
                if ($clientId !== null) {
                    $charge->setOwner($this->em->getReference(Client::class, $clientId));
                }
            }

            $adresse = $this->text($data['Adresse'] ?? null);
            if ($adresse !== null) {
                $locId = $this->resolveLocationId(
                    $adresse,
                    $this->text($data['Allée'] ?? null),
                    $this->text($data['Rng'] ?? null),
                    $this->text($data['Niv'] ?? null),
                    $this->text($data['Pos'] ?? null),
                );
                if ($locId !== null) {
                    $charge->setEmplacement($this->em->getReference(Location::class, $locId));
                }
            }

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
            'Import terminé : %d créés, %d mis à jour, %d ignorés. Pic mémoire : %s MB.',
            $created,
            $updated,
            $this->skipped,
            number_format(memory_get_peak_usage(true) / 1024 / 1024, 1),
        ));

        return Command::SUCCESS;
    }

    /**
     * Résout l'id du Product à partir de sa référence.
     * Retourne null si le produit n'existe pas (pas de création auto, Product est requis).
     */
    private function resolveProductId(string $ref): ?int
    {
        if (isset($this->productIdByRef[$ref])) {
            return $this->productIdByRef[$ref];
        }
        $result = $this->productRepository->createQueryBuilder('p')
            ->select('p.id')
            ->where('p.reference = :ref')
            ->setParameter('ref', $ref)
            ->getQuery()
            ->getOneOrNullResult();
        if (is_array($result)) {
            $id = (int) $result['id'];
            $this->productIdByRef[$ref] = $id;
            return $id;
        }
        return null;
    }

    /**
     * Résout l'id de la Reception, la crée à la volée si nécessaire.
     */
    private function resolveReceptionId(string $ref): ?int
    {
        if (isset($this->receptionIdByRef[$ref])) {
            return $this->receptionIdByRef[$ref];
        }
        $result = $this->receptionRepository->createQueryBuilder('r')
            ->select('r.id')
            ->where('r.reference = :ref')
            ->setParameter('ref', $ref)
            ->getQuery()
            ->getOneOrNullResult();
        if (is_array($result)) {
            $this->receptionIdByRef[$ref] = (int) $result['id'];
            return (int) $result['id'];
        }

        $reception = new Reception();
        $reception->setReference(mb_substr($ref, 0, 100));
        $reception->setDate(new \DateTimeImmutable());
        $reception->setTypeReception(TypeReception::STANDARD);
        $this->em->persist($reception);
        $this->em->flush();
        $id = (int) $reception->getId();
        $this->receptionIdByRef[$ref] = $id;
        return $id;
    }

    private function resolveClientId(string $deposant): ?int
    {
        if (isset($this->clientIdByDeposant[$deposant])) {
            return $this->clientIdByDeposant[$deposant];
        }
        $result = $this->clientRepository->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.deposant = :d')
            ->setParameter('d', $deposant)
            ->getQuery()
            ->getOneOrNullResult();
        if (is_array($result)) {
            $this->clientIdByDeposant[$deposant] = (int) $result['id'];
            return (int) $result['id'];
        }

        $client = new Client();
        $client->setDeposant(mb_substr($deposant, 0, 50));
        $client->setNomDeposant(mb_substr($deposant, 0, 150));
        $this->em->persist($client);
        $this->em->flush();
        $id = (int) $client->getId();
        $this->clientIdByDeposant[$deposant] = $id;
        return $id;
    }

    private function resolveLocationId(string $code, ?string $allee, ?string $rack, ?string $niveau, ?string $position): ?int
    {
        if (isset($this->locationIdByCode[$code])) {
            return $this->locationIdByCode[$code];
        }
        $result = $this->locationRepository->createQueryBuilder('l')
            ->select('l.id')
            ->where('l.code = :c')
            ->setParameter('c', $code)
            ->getQuery()
            ->getOneOrNullResult();
        if (is_array($result)) {
            $this->locationIdByCode[$code] = (int) $result['id'];
            return (int) $result['id'];
        }

        $location = new Location();
        $location->setCode(mb_substr($code, 0, 50));
        if ($allee !== null)    $location->setAllee(mb_substr($allee, 0, 10));
        if ($rack !== null)     $location->setRack(mb_substr($rack, 0, 10));
        if ($niveau !== null)   $location->setNiveau(mb_substr($niveau, 0, 10));
        if ($position !== null) $location->setPosition(mb_substr($position, 0, 10));
        $this->em->persist($location);
        $this->em->flush();
        $id = (int) $location->getId();
        $this->locationIdByCode[$code] = $id;
        return $id;
    }

    private function mapStatut(?string $value): StatutUL
    {
        $v = mb_strtolower(trim((string) ($value ?? '')));
        return match (true) {
            in_array($v, ['disponible', 'dispo'], true)                                    => StatutUL::DISPONIBLE,
            in_array($v, ['réservé', 'réservée', 'reserve', 'reservee'], true)             => StatutUL::RESERVE,
            in_array($v, ['bloqué', 'bloquée', 'bloque'], true)                            => StatutUL::BLOQUE,
            in_array($v, ['rebut', 'rebuté', 'rebute'], true)                              => StatutUL::REBUT,
            default                                                                        => StatutUL::DISPONIBLE,
        };
    }

    private function mapEtat(?string $value): EtatUL
    {
        $v = mb_strtoupper(trim((string) ($value ?? '')));
        return match (true) {
            $v === 'GOOD' || $v === '' => EtatUL::GOOD,
            default                    => EtatUL::HS,
        };
    }

    private function mapTypeUnite(?string $value): TypeUnite
    {
        $v = mb_strtoupper(trim((string) ($value ?? '')));
        return match ($v) {
            'PAL', 'PALETTE' => TypeUnite::PALETTE,
            'COLIS', 'COL'   => TypeUnite::COLIS,
            default          => TypeUnite::UNITE,
        };
    }

    private function mapTypeReception(?string $value): ?TypeReception
    {
        $v = mb_strtolower(trim((string) ($value ?? '')));
        return match ($v) {
            'standard'  => TypeReception::STANDARD,
            'retour'    => TypeReception::RETOUR,
            'transfert' => TypeReception::TRANSFERT,
            default     => null,
        };
    }

    private function mapTypeFlux(?string $value): ?TypeFlux
    {
        $v = mb_strtoupper(trim((string) ($value ?? '')));
        return match ($v) {
            'CF'    => TypeFlux::CF,
            'RT'    => TypeFlux::RT,
            default => null,
        };
    }

    private function flushAndClear(): void
    {
        $this->em->flush();
        $this->em->clear();
        $this->resetDoctrineProfiler();
        gc_collect_cycles();
    }

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

    private function resetDoctrineProfiler(): void
    {
        try {
            $config = $this->em->getConnection()->getConfiguration();
            if (!method_exists($config, 'getMiddlewares')) {
                return;
            }
            foreach ((array) $config->getMiddlewares() as $mw) {
                if (is_object($mw) && method_exists($mw, 'reset')) {
                    $mw->reset();
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
        if ($v === '' || $v === '-') {
            return null;
        }
        return $v;
    }

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
        return in_array($v, ['oui', 'true', '1', 'yes', 'o'], true);
    }

    private function float(?string $value): ?float
    {
        $v = $this->text($value);
        if ($v === null) {
            return null;
        }
        $v = str_replace([' ', ','], ['', '.'], $v);
        if (!is_numeric($v)) {
            return null;
        }
        return (float) $v;
    }

    /**
     * Parse une valeur avec unité type "0,00 m" / "15 Kg" / "3.5 cm" → float.
     */
    private function floatStrip(?string $value): ?float
    {
        $v = $this->text($value);
        if ($v === null) {
            return null;
        }
        $v = preg_replace('/[^\d,.\-+eE]/', '', $v);
        if ($v === null || $v === '') {
            return null;
        }
        $v = str_replace(',', '.', $v);
        if (!is_numeric($v)) {
            return null;
        }
        return (float) $v;
    }

    private function date(?string $value): ?\DateTimeImmutable
    {
        $v = $this->text($value);
        if ($v === null) {
            return null;
        }
        foreach (['d/m/Y H:i', 'd/m/Y', 'd-m-Y', 'Y-m-d'] as $fmt) {
            $d = \DateTimeImmutable::createFromFormat($fmt, $v);
            if ($d instanceof \DateTimeImmutable) {
                return $d;
            }
        }
        return null;
    }
}
