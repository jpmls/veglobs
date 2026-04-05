<?php

namespace App\Command;

use App\Entity\News;
use App\Repository\NewsRepository;
use App\Service\PrimService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-prim')]
class ImportPrimCommand extends Command
{
    private PrimService $primService;
    private EntityManagerInterface $em;

    public function __construct(PrimService $primService, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->primService = $primService;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = $this->primService->getDisruptions();

        if (!isset($data['disruptions'])) {
            $output->writeln('Aucune donnée');
            return Command::FAILURE;
        }

        $admin = $this->em->getRepository(\App\Entity\User::class)->find(1);
        $count = 0;

        foreach ($data['disruptions'] as $d) {

            $title   = $d['title']                   ?? 'Perturbation';
            $message = $d['messages'][0]['text']      ?? 'Aucune info';

            // ── Extraction du réseau et de la ligne ──────────────────────
            $network = 'metro';
            $line    = null;

            // Les lignes impactées sont dans impacted_objects[]
            $impacted = $d['impacted_objects'] ?? [];

            foreach ($impacted as $obj) {
                $ptObj = $obj['pt_object'] ?? null;
                if (!$ptObj) continue;

                $embType = $ptObj['embedded_type'] ?? '';

                // Cas : la perturbation touche directement une ligne
                if ($embType === 'line' && isset($ptObj['line'])) {
                    [$network, $line] = $this->parseLineObject($ptObj['line']);
                    break;
                }

                // Cas : route (direction) → on remonte à la ligne parente
                if ($embType === 'route' && isset($ptObj['route']['line'])) {
                    [$network, $line] = $this->parseLineObject($ptObj['route']['line']);
                    break;
                }

                // Cas : stop_point → on récupère les routes associées
                if ($embType === 'stop_point') {
                    $routes = $ptObj['stop_point']['routes'] ?? [];
                    foreach ($routes as $route) {
                        if (isset($route['line'])) {
                            [$network, $line] = $this->parseLineObject($route['line']);
                            break 2;
                        }
                    }
                }
            }

            // Si toujours null, on essaie de déduire depuis le titre / message
            if ($line === null) {
                [$network, $line] = $this->guessLineFromText($title . ' ' . $message);
            }

            // ── Création de la news ──────────────────────────────────────
            $news = new News();
            $news->setTitle($title);
            $news->setContent($message);
            $news->setNetwork($network);
            $news->setLine($line ?? '');          // chaîne vide si vraiment inconnue
            $news->setType('perturbation');
            $news->setSource('official');
            $news->setPublishedAt(new \DateTimeImmutable());
            $news->setViews(0);
            $news->setAuthor($admin);

            $this->em->persist($news);
            $count++;
        }

        $this->em->flush();
        $output->writeln("✅ Import terminé — {$count} perturbations importées");

        return Command::SUCCESS;
    }

    // ────────────────────────────────────────────────────────────────────
    // Extrait [network, line] depuis un objet "line" IDFM
    // Ex: { "id": "line:IDFM:C01371", "code": "13", "commercial_mode": { "id": "commercial_mode:IDFM:metro" } }
    // ────────────────────────────────────────────────────────────────────
    private function parseLineObject(array $lineObj): array
    {
        $code = trim($lineObj['code'] ?? '');
        $modeId = $lineObj['commercial_mode']['id'] ?? '';
        $modeName = strtolower($lineObj['commercial_mode']['name'] ?? '');

        $network = $this->normalizeNetwork($modeId, $modeName);
        $line    = $this->normalizeLine($code, $network);

        return [$network, $line];
    }

    // ────────────────────────────────────────────────────────────────────
    // Normalise le réseau depuis l'ID ou le nom du mode commercial IDFM
    // ────────────────────────────────────────────────────────────────────
    private function normalizeNetwork(string $modeId, string $modeName): string
    {
        $id = strtolower($modeId);

        if (str_contains($id, 'metro')      || str_contains($modeName, 'métro'))    return 'metro';
        if (str_contains($id, 'rer')        || str_contains($modeName, 'rer'))      return 'rer';
        if (str_contains($id, 'tram')       || str_contains($modeName, 'tram'))     return 'tram';
        if (str_contains($id, 'bus')        || str_contains($modeName, 'bus'))      return 'bus';
        if (str_contains($id, 'transilien') || str_contains($modeName, 'transilien')) return 'transilien';
        if (str_contains($id, 'noctilien') || str_contains($modeName, 'noctilien')) return 'bus';

        return 'metro';
    }

    // ────────────────────────────────────────────────────────────────────
    // Normalise le code de ligne pour correspondre aux pastilles CSS
    // Ex: "13" → "13", "A" → "rer a", "T3a" → "t3a"
    // ────────────────────────────────────────────────────────────────────
    private function normalizeLine(string $code, string $network): string
    {
        if ($code === '') return '';

        // RER : code "A","B","C","D","E" → "rer a", "rer b", etc.
        if ($network === 'rer' && preg_match('/^[A-Ea-e]$/', $code)) {
            return 'rer ' . strtolower($code);
        }

        // Tram : code "T1","T3a","T3b"... → "t1","t3a","t3b"...
        if ($network === 'tram' || preg_match('/^T\d/i', $code)) {
            return strtolower($code);
        }

        // Métro : code "1"..."14","3b","7b" → inchangé
        if (preg_match('/^\d{1,2}[b]?$/', $code)) {
            return $code;
        }

        // Transilien : lettre seule H,J,K,L,N,P,R,U
        if (preg_match('/^[HJKLNPRU]$/i', $code)) {
            return strtolower($code);
        }

        // Sinon retour brut en minuscule
        return strtolower($code);
    }

    // ────────────────────────────────────────────────────────────────────
    // Devine réseau + ligne depuis le texte si les impacted_objects sont vides
    // Cherche des patterns comme "Ligne 13", "RER A", "T3a", "Bus 137"
    // ────────────────────────────────────────────────────────────────────
    private function guessLineFromText(string $text): array
    {
        $t = $text;

        // RER A-E
        if (preg_match('/\bRER\s*([A-E])\b/i', $t, $m)) {
            return ['rer', 'rer ' . strtolower($m[1])];
        }

        // Tramway T1-T13
        if (preg_match('/\b(T\d{1,2}[ab]?)\b/i', $t, $m)) {
            return ['tram', strtolower($m[1])];
        }

        // Métro "Ligne 9", "ligne 13", "M9"
        if (preg_match('/\b(?:ligne\s*|M)(\d{1,2}[b]?)\b/i', $t, $m)) {
            return ['metro', $m[1]];
        }

        // Bus "Bus 137", "bus 303"
        if (preg_match('/\bBus\s*(\d{1,3}[A-Z]?)\b/i', $t, $m)) {
            return ['bus', strtolower($m[1])];
        }

        // Transilien lettre seule après "ligne " ou début
        if (preg_match('/\bligne\s+([HJKLNPRU])\b/i', $t, $m)) {
            return ['transilien', strtolower($m[1])];
        }

        return ['metro', ''];
    }
}