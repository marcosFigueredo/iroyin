<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ClimaController extends Controller
{
    public function index(): JsonResponse
    {
        $cfg    = Configuracao::current();
        $apiKey = $cfg->weather_api_key;
        $city   = $cfg->cidade_clima;

        if (!$apiKey || !$city) {
            return response()->json(['erro' => 'clima_nao_configurado'], 204);
        }

        $cacheKey = 'clima_' . md5($city);

        $data = Cache::remember($cacheKey, 600, function () use ($apiKey, $city) {
            $response = Http::timeout(8)->get('https://api.openweathermap.org/data/2.5/weather', [
                'q'     => $city,
                'appid' => $apiKey,
                'units' => 'metric',
                'lang'  => 'pt_br',
            ]);

            if (!$response->successful()) {
                return null;
            }

            $json = $response->json();

            return [
                'temp'      => round($json['main']['temp']),
                'descricao' => $json['weather'][0]['description'] ?? '',
                'icone'     => $json['weather'][0]['icon'] ?? '',
                'umidade'   => $json['main']['humidity'],
                'vento'     => $json['wind']['speed'],
                'cidade'    => $city,
            ];
        });

        if (!$data) {
            return response()->json(['erro' => 'falha_api_clima'], 502);
        }

        return response()->json($data);
    }
}
