<?php

namespace App\Command;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clients:import-csv',
    description: 'Importe une liste de clients (déposants) depuis un CSV à une colonne.',
)]
/*
 * Exemple Usage
 * # CSV par défaut (deposant-1.csv à la racine du projet)
 * php bin/console app:clients:import-csv
 *
 * # CSV absolu
 * php bin/console app:clients:import-csv "C:\Le\Chemin\Complet\Vers\Votre\Fichier\deposant-1.csv"
 * php bin/console app:clients:import-csv "C:\Users\Heri\Documents\deposant-1.csv"
 *
 * # CSV avec en-tête et tabulation
 * php bin/console app:clients:import-csv ./data/deposants.tsv --delimiter="\t" --has-header
 *
 * # Vérifier sans écrire
 * php bin/console app:clients:import-csv deposant-1.csv --dry-run
 */
class ImportClientsFromCsvCommand extends Command
{
    private const BATCH_SIZE = 100;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClientRepository $clientRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'Chemin du fichier CSV', 'deposant-1.csv')
            ->addOption('delimiter', null, InputOption::VALUE_REQUIRED, 'Séparateur CSV', ';')
            ->addOption('has-header', null, InputOption::VALUE_NONE, 'Considère la 1re ligne comme en-tête')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Affiche ce qui serait importé sans écrire en base');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $file = (string) $input->getArgument('file');
        $delimiter = (string) $input->getOption('delimiter');
        $hasHeader = (bool) $input->getOption('has-header');
        $dryRun = (bool) $input->getOption('dry-run');

        $path = $this->resolvePath($file);
        if ($path === null) {
            $io->error(sprintf('Fichier introuvable : %s', $file));
            return Command::FAILURE;
        }

        $this->disableSqlLogger();

        $io->title('Import des clients (déposants)');
        $io->text([
            'Fichier      : ' . $path,
            'Séparateur   : "' . $delimiter . '"',
            'Entête       : ' . ($hasHeader ? 'oui' : 'non'),
            'Dry-run      : ' . ($dryRun ? 'OUI (aucune écriture)' : 'non'),
        ]);

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $io->error("Impossible d'ouvrir le fichier.");
            return Command::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $duplicates = 0;
        $lineNum = 0;
        $seen = [];

        if ($hasHeader) {
            fgetcsv($handle, 0, $delimiter);
            $lineNum++;
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNum++;

            if ($row === [null] || $row === false) {
                continue;
            }

            $code = $this->clean($row[0] ?? null);
            $nomDeposant = $this->clean($row[1] ?? null) ?? $code;
            $email = $this->clean($row[2] ?? null);

            if ($code === null) {
                $skipped++;
                continue;
            }

            if (isset($seen[$code])) {
                $duplicates++;
                continue;
            }
            $seen[$code] = true;

            $client = $this->clientRepository->findOneBy(['deposant' => $code]);

            if ($client === null) {
                $client = new Client();
                $client->setDeposant($code);
                $client->setNomDeposant($nomDeposant ?? $code);
                if ($email !== null) {
                    $client->setEmail($email);
                }
                if (!$dryRun) {
                    $this->em->persist($client);
                }
                $created++;
            } else {
                $hasChange = false;
                if ($nomDeposant !== null && $nomDeposant !== $code && $client->getNomDeposant() !== $nomDeposant) {
                    $client->setNomDeposant($nomDeposant);
                    $hasChange = true;
                }
                if ($email !== null && $client->getEmail() !== $email) {
                    $client->setEmail($email);
                    $hasChange = true;
                }
                if ($hasChange) {
                    $updated++;
                }
            }

            if (!$dryRun && ($created + $updated) > 0 && ($created + $updated) % self::BATCH_SIZE === 0) {
                $this->em->flush();
                $this->em->clear();
                gc_collect_cycles();
            }
        }

        fclose($handle);

        if (!$dryRun) {
            $this->em->flush();
            $this->em->clear();
            gc_collect_cycles();
        }

        $io->success(sprintf(
            'Terminé — %d lignes lues. Créés: %d · Mis à jour: %d · Doublons CSV: %d · Ignorés: %d',
            $lineNum,
            $created,
            $updated,
            $duplicates,
            $skipped,
        ));

        return Command::SUCCESS;
    }

    private function resolvePath(string $file): ?string
    {
        if (is_file($file)) {
            return realpath($file) ?: $file;
        }

        $projectRoot = dirname(__DIR__, 2);
        $candidate = $projectRoot . DIRECTORY_SEPARATOR . ltrim($file, '\/');
        if (is_file($candidate)) {
            return realpath($candidate) ?: $candidate;
        }

        return null;
    }

    private function disableSqlLogger(): void
    {
        $config = $this->em->getConnection()->getConfiguration();
        if (method_exists($config, 'setMiddlewares')) {
            $config->setMiddlewares([]);
        }
        if (method_exists($config, 'setSQLLogger')) {
            $config->setSQLLogger(null);
        }
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = trim((string) mb_convert_encoding($value, 'UTF-8', 'UTF-8, Windows-1252'));
        return $v === '' ? null : $v;
    }
}
