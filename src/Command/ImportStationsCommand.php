<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-stations')]
class ImportStationsCommand extends Command
{
    public function __construct(private Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = __DIR__ . '/../../data/emplacement-des-gares-idf.csv';

        if (!file_exists($filePath)) {
            $output->writeln('Fichier introuvable : ' . $filePath);
            return Command::FAILURE;
        }

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            $output->writeln('Impossible d’ouvrir le fichier CSV');
            return Command::FAILURE;
        }

        $header = fgetcsv($handle, 0, ';');

        if (!$header) {
            $output->writeln('Header CSV introuvable');
            fclose($handle);
            return Command::FAILURE;
        }

        $output->writeln('Colonnes détectées : ' . implode(' | ', $header));

        $count = 0;
        $skipped = 0;

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            if (count($data) < 3) {
                $skipped++;
                continue;
            }

            $name = trim($data[0] ?? '');
            $lat = isset($data[1]) ? (float) str_replace(',', '.', $data[1]) : null;
            $lon = isset($data[2]) ? (float) str_replace(',', '.', $data[2]) : null;

            if ($name === '' || empty($lat) || empty($lon)) {
                $skipped++;
                continue;
            }

            $type = 'rail';

            $lowerName = mb_strtolower($name);

            if (str_contains($lowerName, 'rer')) {
                $type = 'rer';
            } elseif (str_contains($lowerName, 'métro') || str_contains($lowerName, 'metro')) {
                $type = 'metro';
            }

            $this->connection->insert('transport_stop', [
                'name' => $name,
                'lat' => $lat,
                'lon' => $lon,
                'stop_type' => $type,
            ]);

            $count++;
        }

        fclose($handle);

        $output->writeln("Import terminé : $count lignes ajoutées");
        $output->writeln("Lignes ignorées : $skipped");

        return Command::SUCCESS;
    }
}