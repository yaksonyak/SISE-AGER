<?php

namespace App\Command;

use App\Service\PnerDataImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:import-pner-data', description: 'Importe massivement les données PNER depuis data/import.')]
class ImportPnerDataCommand extends Command
{
    public function __construct(private readonly PnerDataImportService $importService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $this->importService->getImportDirectory();

        $io->title('Import massif des données PNER');
        $io->text(sprintf('Dossier source : %s', $directory));

        $validation = $this->importService->validateImportFiles($directory);
        if ($validation['errors'] !== []) {
            $io->error('Import annulé : des erreurs de validation ont été détectées.');
            $io->listing($validation['errors']);

            return Command::FAILURE;
        }

        $summary = $this->importService->import($directory);
        foreach ($summary['files'] as $fileName => $count) {
            $io->text(sprintf('Import %s : %d lignes créées/mises à jour', $fileName, $count));
        }
        $io->success('Import PNER terminé.');
        $io->table(['Indicateur', 'Nombre importé / mis à jour'], [
            ['Programmes', $summary['programmes']],
            ['ZER', $summary['zers']],
            ['Localités', $summary['localites']],
            ['Projets', $summary['projets']],
            ['Indicateurs', $summary['indicateurs']],
            ['Actions genre', $summary['actions_genre']],
            ['Financements', $summary['financements']],
        ]);

        return Command::SUCCESS;
    }
}
