<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DbController extends AbstractController
{
    public function __construct(
        private readonly ParameterBagInterface $params,
    ) {
    }

    #[Route('/su-admin/db/export/98u325_sav@tkl56138', name: 'app_db_export', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function savedb(): BinaryFileResponse
    {
        $projectDir = (string) $this->params->get('kernel.project_dir');
        $backupDir  = $projectDir . '/var/db-backups';

        if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
            throw new \RuntimeException(sprintf('Impossible de créer le dossier "%s".', $backupDir));
        }

        foreach (['*.dump', '*.sql', '*.sql.gz'] as $pattern) {
            foreach (glob($backupDir . '/' . $pattern) ?: [] as $oldFile) {
                @unlink($oldFile);
            }
        }

        $host      = $_ENV['DB_HOST']            ?? '127.0.0.1';
        $port      = $_ENV['DB_PORT']            ?? '5432';
        $dbName    = $_ENV['DB_NAME']            ?? 'wms';
        $user      = $_ENV['DB_USER']            ?? 'postgres';
        $password  = $_ENV['DB_PASSWORD']        ?? '';
        $container = $_ENV['DB_DUMP_CONTAINER']  ?? 'wms-s30-database-1';

        $fileName = sprintf('wms_%s.dump', (new \DateTimeImmutable())->format('YmdHis'));
        $filePath = $backupDir . '/' . $fileName;

        $fh = fopen($filePath, 'wb');
        if ($fh === false) {
            throw new \RuntimeException('Impossible de créer le fichier de sortie : ' . $filePath);
        }

        try {
            [$process, $mode] = $this->buildDumpProcess(
                $container,
                $host,
                $port,
                $user,
                $password,
                $dbName,
                $projectDir,
            );

            $process->setTimeout(600);
            $process->run(function (string $type, string $buffer) use ($fh): void {
                if ($type === Process::OUT) {
                    fwrite($fh, $buffer);
                }
            });
        } finally {
            fclose($fh);
        }

        if (!$process->isSuccessful()) {
            $stderr = trim($process->getErrorOutput());
            @unlink($filePath);
            throw new \RuntimeException(sprintf(
                "Échec du dump PostgreSQL (mode=%s).\nErreur : %s",
                $mode,
                $stderr !== '' ? $stderr : 'exit code ' . $process->getExitCode(),
            ));
        }

        if (!is_file($filePath) || filesize($filePath) === 0) {
            @unlink($filePath);
            throw new \RuntimeException('Le fichier de dump est vide ou introuvable.');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName,
        );
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->deleteFileAfterSend(false);

        return $response;
    }

    /**
     * Construit le Process pg_dump : conteneur Docker si dispo, sinon binaire local.
     *
     * @return array{0: Process, 1: string}
     */
    private function buildDumpProcess(
        string $container,
        string $host,
        string $port,
        string $user,
        string $password,
        string $dbName,
        string $cwd,
    ): array {
        if ($container !== '' && $this->isDockerContainerRunning($container)) {
            $process = new Process([
                'docker', 'exec',
                '-e', 'PGPASSWORD=' . $password,
                $container,
                'pg_dump',
                '-U', $user,
                '-d', $dbName,
                '--no-owner',
                '--no-privileges',
                '--format=custom',
                '--compress=9',
            ], $cwd);

            return [$process, 'docker'];
        }

        $pgDumpBin = $this->findBinary('pg_dump');
        if ($pgDumpBin === 'pg_dump' && !$this->commandExists('pg_dump')) {
            throw new \RuntimeException(
                "pg_dump n'est pas disponible : ni dans le PATH du système, ni dans un conteneur Docker nommé \"$container\". "
                . "Démarre le conteneur PostgreSQL (docker compose up -d database) ou installe PostgreSQL client localement."
            );
        }

        $process = new Process([
            $pgDumpBin,
            '--host=' . $host,
            '--port=' . $port,
            '--username=' . $user,
            '--dbname=' . $dbName,
            '--no-owner',
            '--no-privileges',
            '--format=custom',
            '--compress=9',
        ], $cwd, [
            'PGPASSWORD' => $password,
            'PATH'       => $_ENV['PATH'] ?? getenv('PATH') ?: '',
        ]);

        return [$process, 'local'];
    }

    private function isDockerContainerRunning(string $container): bool
    {
        if (!$this->commandExists('docker')) {
            return false;
        }
        $process = new Process(['docker', 'inspect', '-f', '{{.State.Running}}', $container]);
        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) === 'true';
    }

    private function commandExists(string $name): bool
    {
        $finder = \PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        $process = new Process([$finder, $name]);
        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) !== '';
    }

    private function findBinary(string $name): string
    {
        $finder = \PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        $process = new Process([$finder, $name]);
        $process->run();

        if ($process->isSuccessful()) {
            $path = trim(strtok($process->getOutput(), "\r\n") ?: '');
            if ($path !== '') {
                return $path;
            }
        }

        return $name;
    }
}
