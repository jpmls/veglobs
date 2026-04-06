<?php

namespace App\Controller;

use App\Service\PrimService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/journey')]
class JourneyApiController extends AbstractController
{
    private PrimService $prim;

    public function __construct(PrimService $prim)
    {
        $this->prim = $prim;
    }

    /**
     * Recherche de lieux/arrêts via Navitia
     * GET /api/journey/places?q=chatelet
     */
    #[Route('/places', name: 'api_journey_places', methods: ['GET'])]
    public function places(Request $request): JsonResponse
    {
        $q = $request->query->get('q', '');

        if (strlen(trim($q)) < 2) {
            return $this->json([]);
        }

        try {
            $data   = $this->prim->searchPlace($q);
            $places = $data['places'] ?? [];

            $result = array_map(function ($place) {
                $coords = $place['stop_area']['coord']
                    ?? $place['address']['coord']
                    ?? $place['administrative_region']['coord']
                    ?? null;

                return [
                    'id'   => $place['id'],
                    'name' => $place['name'],
                    'type' => $place['embedded_type'] ?? 'unknown',
                    'lat'  => $coords['lat'] ?? null,
                    'lon'  => $coords['lon'] ?? null,
                ];
            }, $places);

            return $this->json($result);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Calcul d'itinéraire
     * GET /api/journey/compute?from=...&to=...
     */
    #[Route('/compute', name: 'api_journey_compute', methods: ['GET'])]
    public function compute(Request $request): JsonResponse
    {
        $from     = $request->query->get('from', '');
        $to       = $request->query->get('to', '');
        $datetime = $request->query->get('datetime', '');

        if (!$from || !$to) {
            return $this->json(['error' => 'Paramètres from et to requis'], 400);
        }

        try {
            $data     = $this->prim->getJourney($from, $to, $datetime);
            $journeys = $data['journeys'] ?? [];

            $result = array_map(function ($journey) {
                $sections = array_map(function ($section) {
                    $lines = [];
                    if (isset($section['display_informations'])) {
                        $di = $section['display_informations'];
                        $lines[] = [
                            'code'    => $di['code']    ?? '',
                            'label'   => $di['label']   ?? '',
                            'color'   => '#' . ($di['color'] ?? '888888'),
                            'network' => $di['network'] ?? '',
                        ];
                    }

                    return [
                        'type'      => $section['type']                  ?? 'unknown',
                        'mode'      => $section['mode']                  ?? null,
                        'from'      => $section['from']['name']          ?? null,
                        'to'        => $section['to']['name']            ?? null,
                        'duration'  => $section['duration']              ?? 0,
                        'departure' => $section['departure_date_time']   ?? null,
                        'arrival'   => $section['arrival_date_time']     ?? null,
                        'lines'     => $lines,
                        'geojson'   => $section['geojson']               ?? null,
                    ];
                }, $journey['sections'] ?? []);

                return [
                    'duration'     => $journey['duration']              ?? 0,
                    'departure'    => $journey['departure_date_time']   ?? null,
                    'arrival'      => $journey['arrival_date_time']     ?? null,
                    'nb_transfers' => $journey['nb_transfers']          ?? 0,
                    'sections'     => $sections,
                    'co2_emission' => $journey['co2_emission']['value'] ?? null,
                ];
            }, $journeys);

            return $this->json($result);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}