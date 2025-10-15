<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MeteoService
{
    public function __construct(private readonly HttpClientInterface $client){}


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getForecast(float $lat, float $lon,  \DateTimeImmutable $dateHeureDebut) :?array
    {
        $url = 'https://api.open-meteo.com/v1/forecast';

        $params = [
            'latitude' => $lat,
            'longitude' => $lon,
            'timezone' => 'auto',
            'hourly' => 'temperature_2m,weather_code,precipitation,wind_speed_10m',
            'forecast_days' => 7,
        ];

        $response = $this->client->request('GET', $url, [
            'query' => $params
        ]);

        if ($response->getStatusCode() !== 200) {
            return null;
        }


        $data = $response->toArray();

        if (!isset($data['hourly']['time'])) {
            return null;
        }

        $targetTime = $dateHeureDebut->format('Y-m-d\TH:00'); // Format API: "2025-10-20T14:00"

        // Recherche de l'index de l'heure exacte
        $index = array_search($targetTime, $data['hourly']['time']);

        if ($index === false) {
            return null; // Donnée non disponible pour cette heure
        }

        // Retourne la météo à cette heure
        return [
            'datetime' => $targetTime,
            'temperature' => $data['hourly']['temperature_2m'][$index],
            'precipitation' => $data['hourly']['precipitation'][$index],
            'wind_speed' => $data['hourly']['wind_speed_10m'][$index],
            'weather_code' => $data['hourly']['weather_code'][$index],
        ];
    }


    public function interpretWeatherCode(int $code): string
    {
        return match ($code) {
            0 => 'Ciel clair',
            1 => 'Principalement clair',
            2 => 'Partiellement nuageux',
            3 => 'Nuageux',
            45, 48 => 'Brouillard',
            51, 53, 55 => 'Bruine',
            56, 57 => 'Bruine verglaçante',
            61, 63, 65 => 'Pluie',
            66, 67 => 'Pluie verglaçante',
            71, 73, 75 => 'Neige',
            77 => 'Grains de neige',
            80, 81, 82 => 'Averses',
            85, 86 => 'Averses de neige',
            95 => 'Orage',
            96, 99 => 'Orage avec grêle',
            default => 'Inconnu',
        };
    }
}