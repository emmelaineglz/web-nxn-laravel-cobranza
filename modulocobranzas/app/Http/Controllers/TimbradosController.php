<?php

namespace App\Http\Controllers;

use App\Services\TimbradosApi; // <-- servicio que llama a /timbrados
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class TimbradosController extends Controller
{
    public function __construct(private readonly TimbradosApi $api) {}

    /**
     * API interna JSON
     * GET /api/timbres?empresa=###&anio=YYYY&mes=MM
     */
    public function index(Request $request)
    {
        // Validación igual que en empleados
        $data = $request->validate([
            'empresa' => ['required', 'integer', 'between:700,720'],
            'anio'    => ['required', 'integer', 'digits:4', 'min:2000', 'max:2100'],
            'mes'     => ['required', 'regex:/^(0[1-9]|1[0-2])$/'],
        ], [
            'mes.regex' => 'El mes debe tener formato MM (01-12).',
        ]);

        try {
            $apiResult = $this->api->consultar(
                empresa: (int) $data['empresa'],
                anio:    (int) $data['anio'],
                mes:     $data['mes']
            );

            [$timbres, $totales] = $this->normalizeTimbres($apiResult);

            // bandera + mensaje si viene vacío
            $empty = empty($timbres);
            $msg   = $empty
                ? "La empresa {$data['empresa']} no tiene timbres para {$data['anio']}-{$data['mes']}."
                : null;

            return response()->json([
                'ok'      => true,
                'empty'   => $empty,
                'message' => $msg,
                'timbres' => $timbres,   // array normalizado
                'totales' => $totales,   // totales normalizados
            ]);

        } catch (RequestException $e) {
            Log::warning('Timbrados API error', [
                'status' => optional($e->response)->status(),
                'body'   => optional($e->response)->body(),
            ]);

            $status  = optional($e->response)->status() ?? 500;
            $body    = optional($e->response)->json();

            $message = match ($status) {
                400 => $body['error'] ?? 'Parámetros faltantes o inválidos.',
                401 => 'API Key inválida o ausente.',
                500 => 'Error interno del servidor externo.',
                default => 'Error al consultar la API de timbrados.',
            };

            return response()->json(['ok' => false, 'message' => $message], $status);
        }
    }

    /**
     * Vista Blade (SSR)
     * GET /timbrados?empresa=###&anio=YYYY&mes=MM
     * (La vista usa JS para pegarle a /api/timbres)
     */
    public function vista(Request $request)
    {
        $result  = null;
        $error   = null;

        $filtros = [
            'empresa' => $request->integer('empresa'),
            'anio'    => $request->integer('anio'),
            'mes'     => $request->get('mes'),
        ];

        if ($request->filled(['empresa', 'anio', 'mes'])) {
            $data = $request->validate([
                'empresa' => ['required', 'integer', 'between:700,720'],
                'anio'    => ['required', 'integer', 'digits:4', 'min:2000', 'max:2100'],
                'mes'     => ['required', 'regex:/^(0[1-9]|1[0-2])$/'],
            ], [
                'mes.regex' => 'El mes debe tener formato MM (01-12).',
            ]);

            try {
                $apiResult = $this->api->consultar(
                    (int) $data['empresa'],
                    (int) $data['anio'],
                    $data['mes']
                );

                [$timbres, $totales] = $this->normalizeTimbres($apiResult);

                if (empty($timbres)) {
                    $error = "La empresa {$data['empresa']} no tiene timbres para {$data['anio']}-{$data['mes']}.";
                }

                $result  = ['timbres' => $timbres, 'totales' => $totales];
                $filtros = $data;

            } catch (RequestException $e) {
                $status = optional($e->response)->status() ?? 500;
                $body   = optional($e->response)->json();
                $error  = match ($status) {
                    400 => $body['error'] ?? 'Parámetros faltantes o inválidos.',
                    401 => 'API Key inválida o ausente.',
                    500 => 'Error interno del servidor externo.',
                    default => 'Error al consultar la API de timbrados.',
                };
            }
        }

        return view('timbres.index', compact('filtros', 'result', 'error'));
    }

    /**
     * Normaliza la respuesta del backend de timbrados a:
     *  timbres: [
     *    noEmpleado, tNomina, periodo, proceso, ejercicio,
     *    fecFinPeriodo, TimbreUUID, TimbreUUIDA, TimbreUUIDPPP
     *  ]
     *  totales: { total_general, timbres_fiscales, timbres_asimilados, timbres_pensionados }
     */
    private function normalizeTimbres(array $apiResult): array
    {
        // Localiza arreglo de timbrados y totales en la respuesta
        $rawItems = $apiResult['timbrados']
            ?? ($apiResult['data']['timbrados'] ?? $apiResult['items'] ?? $apiResult['results'] ?? []);

        $rawTotals = $apiResult['totales'] ?? [];

        // Normalización de items
        $timbres = collect(is_array($rawItems) ? $rawItems : [])->map(function ($t) {
            return [
                'noEmpleado'     => $t['noEmpleado']     ?? null,
                'tNomina'        => $t['tNomina']        ?? null,
                'periodo'        => $t['periodo']        ?? null,
                'proceso'        => $t['proceso']        ?? null,
                'ejercicio'      => $t['ejercicio']      ?? null,
                'fecFinPeriodo'  => $t['fecFinPeriodo']  ?? null,
                'TimbreUUID'     => $t['TimbreUUID']     ?? null,
                'TimbreUUIDA'    => $t['TimbreUUIDA']    ?? null,
                'TimbreUUIDPPP'  => $t['TimbreUUIDPPP']  ?? null,
            ];
        })->all();

        // Normalización de totales (default en 0)
        $totales = [
            'total_general'       => $rawTotals['total_general']       ?? count($timbres),
            'timbres_fiscales'    => $rawTotals['timbres_fiscales']    ?? 0,
            'timbres_asimilados'  => $rawTotals['timbres_asimilados']  ?? 0,
            'timbres_pensionados' => $rawTotals['timbres_pensionados'] ?? 0,
        ];

        return [$timbres, $totales];
    }
}
