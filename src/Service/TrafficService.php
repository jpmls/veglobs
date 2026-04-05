<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrafficService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey
    ) {}

    public function getTraffic(): array
    {
        $response = $this->client->request(
            'GET',
            'https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/line_reports?count=100',
            [
                'headers' => [
                    'apikey' => $this->apiKey,
                    'Accept' => 'application/json',
                ],
            ]
        );

        $data = $response->toArray(false);

        if (!isset($data['disruptions']) || !is_array($data['disruptions'])) {
            return [];
        }

        $items = [];

        foreach ($data['disruptions'] as $disruption) {
            $message = $this->extractMessage($disruption);
            $cleanMessage = $this->cleanText($message);

            $items[] = [
                'title' => $this->extractTitle($disruption, $cleanMessage),
                'content' => $cleanMessage,
                'line' => $this->extractLine($disruption, $cleanMessage),
                'type' => $this->extractType($cleanMessage),
            ];
        }

        return $items;
    }

    private function extractMessage(array $disruption): string
    {
        if (isset($disruption['messages'][0]['text'])) {
            return (string) $disruption['messages'][0]['text'];
        }

        if (isset($disruption['message'])) {
            return (string) $disruption['message'];
        }

        return '';
    }

    private function cleanText(string $text): string
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function extractTitle(array $disruption, string $message): string
    {
        if (!empty($disruption['title'])) {
            return trim((string) $disruption['title']);
        }

        if ($message !== '') {
            $parts = preg_split('/[.!?]/', $message);
            if (!empty($parts[0])) {
                return mb_substr(trim($parts[0]), 0, 90);
            }
        }

        return 'Perturbation trafic';
    }

    private function extractLine(array $disruption, string $message): ?string
    {
        if (preg_match('/\bLigne\s+([A-Z0-9]+)/iu', $message, $matches)) {
            return strtoupper($matches[1]);
        }

        if (preg_match('/\bBus\s+([A-Z0-9]+)/iu', $message, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    private function extractType(string $message): string
    {
        $message = mb_strtolower($message);

        return match (true) {
            str_contains($message, 'travaux') => 'travaux',
            str_contains($message, 'manifestation') => 'manifestation',
            str_contains($message, 'ascenseur') => 'accessibilité',
            str_contains($message, 'panne') => 'incident',
            str_contains($message, 'déviée') || str_contains($message, 'dévié') => 'perturbation',
            default => 'info',
        };
    }
}