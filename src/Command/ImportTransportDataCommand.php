<?php

namespace App\Command;

use App\Entity\Transport\BikeStation;
use App\Entity\Transport\TransportLine;
use App\Entity\Transport\TransportNewsSource;
use App\Entity\Transport\TransportStop;
use App\Entity\Transport\TransportStopRelation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-transport-data',
    description: 'Import transport CSV data into database',
)]
class ImportTransportDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dataDir = $this->projectDir . '/data';

        $this->importLines($io, $dataDir . '/referentiel-des-lignes.csv');
        $this->importStops($io, $dataDir . '/arrets.csv');
        $this->importRelations($io, $dataDir . '/relations.csv');
        $this->importNews($io, $dataDir . '/actualites.csv');
        $this->importBikeStations($io, $dataDir . '/jcdecaux-bike-stations-data.csv');

        $io->success('Import terminé.');

        return Command::SUCCESS;
    }

    private function importLines(SymfonyStyle $io, string $file): void
    {
        if (!file_exists($file)) {
            $io->warning("Fichier introuvable : $file");
            return;
        }

        $io->section('Import des lignes');

        $this->em->createQuery('DELETE FROM App\Entity\Transport\TransportLine t')->execute();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 0, ';');
        $headerMap = $this->headerMap($header);

        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $line = new TransportLine();
            $line->setExternalId($this->csvValue($row, $headerMap, 'ID_Line') ?? uniqid('line_', true));
            $line->setName($this->csvValue($row, $headerMap, 'Name_Line') ?? 'Sans nom');
            $line->setShortName($this->csvValue($row, $headerMap, 'ShortName_Line'));
            $line->setTransportMode($this->csvValue($row, $headerMap, 'TransportMode'));
            $line->setTransportSubmode($this->csvValue($row, $headerMap, 'TransportSubmode'));
            $line->setType($this->csvValue($row, $headerMap, 'LineType'));
            $line->setOperatorRef($this->csvValue($row, $headerMap, 'OperatorRef'));
            $line->setOperatorName($this->csvValue($row, $headerMap, 'OperatorName'));
            $line->setAdditionalOperatorsRef($this->csvValue($row, $headerMap, 'AdditionalOperatorsRef'));
            $line->setNetworkName($this->csvValue($row, $headerMap, 'NetworkName'));
            $line->setColorHex($this->csvValue($row, $headerMap, 'ColourWeb_hexa'));
            $line->setTextColorHex($this->csvValue($row, $headerMap, 'TextColourWeb_hexa'));
            $line->setColorPrintCmjn($this->csvValue($row, $headerMap, 'ColourPrint_CMYK'));
            $line->setTextColorPrintHex($this->csvValue($row, $headerMap, 'TextColourPrint_hexa'));
            $line->setAccessibility($this->toBool($this->csvValue($row, $headerMap, 'Accessibility')));
            $line->setAudibleSignsAvailable($this->toBool($this->csvValue($row, $headerMap, 'AudibleSignsAvailable')));
            $line->setVisualSignsAvailable($this->toBool($this->csvValue($row, $headerMap, 'VisualSignsAvailable')));
            $line->setGroupExternalId($this->csvValue($row, $headerMap, 'ID_GroupOfLines'));
            $line->setGroupShortName($this->csvValue($row, $headerMap, 'ShortName_GroupOfLines'));
            $line->setNoticeTitle($this->csvValue($row, $headerMap, 'NoticeTitle'));
            $line->setNoticeText($this->csvValue($row, $headerMap, 'Notice'));
            $line->setPicto($this->csvValue($row, $headerMap, 'Picto'));
            $line->setValidFromDate($this->toDate($this->csvValue($row, $headerMap, 'ValidFromDate')));
            $line->setValidToDate($this->toDate($this->csvValue($row, $headerMap, 'ValidToDate')));
            $line->setStatus($this->csvValue($row, $headerMap, 'Status'));
            $line->setPrivateCode($this->csvValue($row, $headerMap, 'PrivateCode'));
            $line->setAirConditioning($this->csvValue($row, $headerMap, 'AirConditioned'));
            $line->setBusContractId($this->csvValue($row, $headerMap, 'BusContractId'));

            $this->em->persist($line);
            $count++;

            if ($count % 500 === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        fclose($handle);
        $this->em->flush();
        $this->em->clear();

        $io->success("$count lignes importées.");
    }

    private function importStops(SymfonyStyle $io, string $file): void
    {
        if (!file_exists($file)) {
            $io->warning("Fichier introuvable : $file");
            return;
        }

        $io->section('Import des arrêts');

        $this->em->createQuery('DELETE FROM App\Entity\Transport\TransportStop t')->execute();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 0, ';');
        $headerMap = $this->headerMap($header);

        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $stop = new TransportStop();
            $stop->setExternalId($this->csvValue($row, $headerMap, 'ArRId') ?? uniqid('stop_', true));
            $stop->setVersion($this->csvValue($row, $headerMap, 'ArRVersion'));
            $stop->setCreatedSourceAt($this->toDate($this->csvValue($row, $headerMap, 'ArRCreation')));
            $stop->setChangedSourceAt($this->toDate($this->csvValue($row, $headerMap, 'ArRChanged')));
            $stop->setName($this->csvValue($row, $headerMap, 'ArRName') ?? 'Sans nom');
            $stop->setStopType($this->csvValue($row, $headerMap, 'ArRType'));
            $stop->setXEpsg2154($this->csvValue($row, $headerMap, 'ArRXEpsg2154'));
            $stop->setYEpsg2154($this->csvValue($row, $headerMap, 'ArRYEpsg2154'));
            $stop->setTown($this->csvValue($row, $headerMap, 'ArRTown'));
            $stop->setPostalRegion($this->csvValue($row, $headerMap, 'ArRPostalRegion'));
            $stop->setAccessibility($this->csvValue($row, $headerMap, 'ArRAccessibility'));
            $stop->setAudibleSignals($this->csvValue($row, $headerMap, 'ArRAudibleSignals'));
            $stop->setVisualSigns($this->csvValue($row, $headerMap, 'ArRVisualSigns'));
            $stop->setFareZone($this->csvValue($row, $headerMap, 'ArRFareZone'));
            $stop->setZdaExternalId($this->csvValue($row, $headerMap, 'ZdAId'));

            $geo = $this->csvValue($row, $headerMap, 'ArRGeoPoint');
            if ($geo) {
                [$lat, $lon] = $this->parseGeoPoint($geo);
                $stop->setLat($lat);
                $stop->setLon($lon);
            }

            $this->em->persist($stop);
            $count++;

            if ($count % 500 === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        fclose($handle);
        $this->em->flush();
        $this->em->clear();

        $io->success("$count arrêts importés.");
    }

    private function importRelations(SymfonyStyle $io, string $file): void
    {
        if (!file_exists($file)) {
            $io->warning("Fichier introuvable : $file");
            return;
        }

        $io->section('Import des relations');

        $this->em->createQuery('DELETE FROM App\Entity\Transport\TransportStopRelation t')->execute();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 0, ';');
        $headerMap = $this->headerMap($header);

        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $relation = new TransportStopRelation();
            $relation->setPdeId($this->csvValue($row, $headerMap, 'PDE_ID'));
            $relation->setPdeVersion($this->csvValue($row, $headerMap, 'PDE_VERSION'));
            $relation->setZdcId($this->csvValue($row, $headerMap, 'ZDC_ID'));
            $relation->setZdcVersion($this->csvValue($row, $headerMap, 'ZDC_VERSION'));
            $relation->setZdaId($this->csvValue($row, $headerMap, 'ZDA_ID'));
            $relation->setZdaVersion($this->csvValue($row, $headerMap, 'ZDA_VERSION'));
            $relation->setArrId($this->csvValue($row, $headerMap, 'ARR_ID'));
            $relation->setArrVersion($this->csvValue($row, $headerMap, 'ARR_VERSION'));
            $relation->setArtId($this->csvValue($row, $headerMap, 'ART_ID'));
            $relation->setArtVersion($this->csvValue($row, $headerMap, 'ART_VERSION'));
            $relation->setArrLat($this->toFloat($this->csvValue($row, $headerMap, 'ARR_GEOPOINTLAT')));
            $relation->setArrLon($this->toFloat($this->csvValue($row, $headerMap, 'ARR_GEOPOINTLON')));
            $relation->setArtLat($this->toFloat($this->csvValue($row, $headerMap, 'ART_GEOPOINTLAT')));
            $relation->setArtLon($this->toFloat($this->csvValue($row, $headerMap, 'ART_GEOPOINTLON')));

            $this->em->persist($relation);
            $count++;

            if ($count % 1000 === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        fclose($handle);
        $this->em->flush();
        $this->em->clear();

        $io->success("$count relations importées.");
    }

    private function importNews(SymfonyStyle $io, string $file): void
    {
        if (!file_exists($file)) {
            $io->warning("Fichier introuvable : $file");
            return;
        }

        $io->section('Import des actualités');

        $this->em->createQuery('DELETE FROM App\Entity\Transport\TransportNewsSource t')->execute();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 0, ';');
        $headerMap = $this->headerMap($header);

        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $news = new TransportNewsSource();
            $news->setExternalId($this->csvValue($row, $headerMap, 'ID') ?? uniqid('news_', true));
            $news->setLang($this->csvValue($row, $headerMap, 'LANG'));
            $news->setTitle($this->csvValue($row, $headerMap, 'TITLE') ?? 'Sans titre');
            $news->setType($this->csvValue($row, $headerMap, 'TYPE'));
            $news->setDescription($this->csvValue($row, $headerMap, 'DESCRIPTION'));
            $news->setLinkType($this->csvValue($row, $headerMap, 'LINKTYPE'));
            $news->setLink($this->csvValue($row, $headerMap, 'LINK'));
            $news->setTitlePage($this->csvValue($row, $headerMap, 'TITLEPAGE'));
            $news->setTextPage($this->csvValue($row, $headerMap, 'TEXTPAGE'));
            $news->setButtonText($this->csvValue($row, $headerMap, 'BUTTONTEXT'));
            $news->setCreatedSourceAt($this->toDate($this->csvValue($row, $headerMap, 'CREATEDDATE')));
            $news->setUpdatedSourceAt($this->toDate($this->csvValue($row, $headerMap, 'UPDATEDDATE')));
            $news->setRawHtml($this->csvValue($row, $headerMap, 'HTML'));

            $this->em->persist($news);
            $count++;

            if ($count % 500 === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        fclose($handle);
        $this->em->flush();
        $this->em->clear();

        $io->success("$count actualités importées.");
    }

    private function importBikeStations(SymfonyStyle $io, string $file): void
    {
        if (!file_exists($file)) {
            $io->warning("Fichier introuvable : $file");
            return;
        }

        $io->section('Import des stations vélo');

        $this->em->createQuery('DELETE FROM App\Entity\Transport\BikeStation t')->execute();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 0, ';');
        $headerMap = $this->headerMap($header);

        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $station = new BikeStation();
            $station->setExternalId((string) ($this->csvValue($row, $headerMap, 'number') ?? uniqid('bike_', true)));
            $station->setName($this->csvValue($row, $headerMap, 'name') ?? 'Sans nom');
            $station->setAddress($this->csvValue($row, $headerMap, 'address'));
            $station->setLat($this->toFloat($this->csvValue($row, $headerMap, 'position.lat')));
            $station->setLon($this->toFloat($this->csvValue($row, $headerMap, 'position.lng')));
            $station->setBanking($this->toBool($this->csvValue($row, $headerMap, 'banking')));
            $station->setBonus($this->toBool($this->csvValue($row, $headerMap, 'bonus')));
            $station->setStatus($this->csvValue($row, $headerMap, 'status'));
            $station->setContractName($this->csvValue($row, $headerMap, 'contract_name'));
            $station->setBikeStands($this->toInt($this->csvValue($row, $headerMap, 'bike_stands')));
            $station->setAvailableBikeStands($this->toInt($this->csvValue($row, $headerMap, 'available_bike_stands')));
            $station->setAvailableBikes($this->toInt($this->csvValue($row, $headerMap, 'available_bikes')));
            $station->setLastUpdate($this->toTimestampMs($this->csvValue($row, $headerMap, 'last_update')));

            $this->em->persist($station);
            $count++;

            if ($count % 500 === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        fclose($handle);
        $this->em->flush();
        $this->em->clear();

        $io->success("$count stations vélo importées.");
    }

    private function headerMap(array $header): array
    {
        $map = [];
        foreach ($header as $index => $name) {
            $map[trim((string) $name)] = $index;
        }
        return $map;
    }

    private function csvValue(array $row, array $headerMap, string $column): ?string
    {
        if (!array_key_exists($column, $headerMap)) {
            return null;
        }

        $value = $row[$headerMap[$column]] ?? null;
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function toBool(?string $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        $v = strtolower(trim($value));

        return match ($v) {
            '1', 'true', 'yes', 'oui' => true,
            '0', 'false', 'no', 'non' => false,
            default => null,
        };
    }

    private function toInt(?string $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    private function toFloat(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(',', '.', $value);
    }

    private function toDate(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function toTimestampMs(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $seconds = (int) floor(((int) $value) / 1000);
            return (new \DateTimeImmutable())->setTimestamp($seconds);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{0:?float,1:?float}
     */
    private function parseGeoPoint(string $value): array
    {
        $parts = array_map('trim', explode(',', $value));

        if (count($parts) !== 2) {
            return [null, null];
        }

        return [
            $this->toFloat($parts[0]),
            $this->toFloat($parts[1]),
        ];
    }
}