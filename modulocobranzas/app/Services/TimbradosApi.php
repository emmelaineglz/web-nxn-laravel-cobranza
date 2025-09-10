<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TimbradosApi
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $cfg = config('services.timbrados_api');

        if (empty($cfg['base_url']) || empty($cfg['api_key'])) {
            throw new \RuntimeException('Faltan EMP_API_BASE_URL o EMP_API_KEY en la configuración.');
        }

        $this->baseUrl = rtrim($cfg['base_url'], '/');
        $this->apiKey  = $cfg['api_key'];
        $this->timeout = (int) ($cfg['timeout'] ?? 10);
        $this->retries = (int) ($cfg['retries'] ?? 2);
    }


    
    public function consultar(int $empresa, int $anio, string $mes): array
    {
        $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])
            ->acceptJson()
            ->timeout($this->timeout)
            ->connectTimeout(5)
            ->retry($this->retries, 300)
            ->get($this->baseUrl . '/timbrados', [
                'empresa' => $empresa,
                'anio'    => $anio,
                'mes'     => $mes,
            ]);

        $response->throw();

        return $response->json(); // ['timbrados'=>[], 'totales'=>[]]
    }
}
