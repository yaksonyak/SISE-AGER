<?php

namespace App\Command;

use App\Service\PnerDataImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:validate-pner-import-files', description: 'Valide les fichiers CSV PNER dans data/import.')]
class ValidatePnerImportFilesCommand extends Command
{
    public function __construct(private readonly PnerDataImportService $importService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $this->importService->getImportDirectory();
        $validation = $this->importService->validateImportFiles($directory);

        $io->title('Validation des fichiers import PNER');
        $io->text(sprintf('Dossier contrôlé : %s', $directory));

        $rows = [];
        foreach ($validation['files'] as $fileName => $fileReport) {
            $rows[] = [$fileName, $fileReport['rows'], $fileReport['errors'] === [] ? 'OK' : implode("\n", $fileReport['errors'])];
        }
        $io->table(['Fichier', 'Lignes', 'Statut'], $rows);

        if ($validation['errors'] !== []) {
            $io->error('Des erreurs ont été détectées dans les fichiers import PNER.');

            return Command::FAILURE;
        }

        $io->success('Tous les fichiers import PNER sont valides.');

        return Command::SUCCESS;
    }
}
