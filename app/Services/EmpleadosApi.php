<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class EmpleadosApi
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $cfg = config('services.empleados_api');

        // Si algo no está en config, lanza una excepción clara
        if (empty($cfg['base_url']) || empty($cfg['api_key'])) {
            throw new \RuntimeException('Faltan EMP_API_BASE_URL o EMP_API_KEY en la configuración.');
        }

        $this->baseUrl = $cfg['base_url'];
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
            ->timeout($this->timeout)     // tiempo total de la petición
            ->connectTimeout(5)           // tiempo para abrir conexión
            ->retry($this->retries, 300)  
            ->get(rtrim($this->baseUrl, '/').'/empleados', [
                'empresa' => $empresa,
                'anio'    => $anio,
                'mes'     => $mes, // formato "MM"
            ]);

        $response->throw();               

        return $response->json();         // ['employees'=>[], 'totales'=>[]]
    }
}
