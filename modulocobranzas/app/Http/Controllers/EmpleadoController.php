<?php

namespace App\Http\Controllers;

use App\Services\EmpleadosApi;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class EmpleadoController extends Controller
{
    public function __construct(private readonly EmpleadosApi $api) {}

    /**
     * API interna JSON
     * GET /api/empleados?empresa=###&anio=YYYY&mes=MM
     */
    public function index(Request $request)
{
    $data = $request->validate([
        'empresa' => ['required', 'integer'],
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

        $empleados = $this->normalizeEmpleados($apiResult);

        // >>> NUEVO: mensaje si viene vacío
        $empty  = empty($empleados);
        $msg    = $empty
            ? "La empresa {$data['empresa']} no tiene datos para {$data['anio']}-{$data['mes']}."
            : null;

        return response()->json([
            'ok'      => true,
            'empty'   => $empty,  // bandera útil para el frontend
            'message' => $msg,
            'data'    => ['empleados' => $empleados],
        ]);

    } catch (RequestException $e) {
        Log::warning('Empleados API error', [
            'status' => optional($e->response)->status(),
            'body'   => optional($e->response)->body(),
        ]);

        $status = optional($e->response)->status() ?? 500;
        $body   = optional($e->response)->json();

        $message = match ($status) {
            400 => $body['error'] ?? 'Parámetros faltantes o inválidos.',
            401 => 'API Key inválida o ausente.',
            404 => 'La empresa solicitada no existe.', // <<< NUEVO
            default => 'Error al consultar la API de empleados.',
        };

        return response()->json(['ok' => false, 'message' => $message], $status);
    }
}


    /**
     * Vista Blade (SSR)
     * GET /empleados?empresa=###&anio=YYYY&mes=MM
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
            'empresa' => ['required', 'integer'],
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

            $empleados = $this->normalizeEmpleados($apiResult);

            // >>> NUEVO: mensaje si viene vacío
            if (empty($empleados)) {
                $error = "La empresa {$data['empresa']} no tiene datos para {$data['anio']}-{$data['mes']}.";
            }

            $result  = ['empleados' => $empleados];
            $filtros = $data;

        } catch (RequestException $e) {
            $status = optional($e->response)->status() ?? 500;
            $body   = optional($e->response)->json();
            $error  = match ($status) {
                400 => $body['error'] ?? 'Parámetros faltantes o inválidos.',
                401 => 'API Key inválida o ausente.',
                404 => 'La empresa solicitada no existe.', // <<< NUEVO
                default => 'Error al consultar la API de empleados.',
            };
        }
    }

    return view('empleados.index', compact('filtros', 'result', 'error'));
}

    /**
     * Normaliza la respuesta del backend a:
     *  [
     *    'nombre'        => string,
     *    'gafete'        => string,
     *    'estatus'       => string,
     *    'fecAntiguedad' => string|null,
     *    'fecBaja'       => string|null,
     *  ]
     */
    private function normalizeEmpleados(array $apiResult): array
    {
        // Localiza el array de empleados en la respuesta
        $raw = $apiResult['empleados']
            ?? ($apiResult['data']['empleados'] ?? $apiResult['items'] ?? $apiResult['results'] ?? $apiResult);

        if (!is_array($raw)) {
            return [];
        }

        return collect($raw)->map(function ($e) {
            // La API entrega: gafete, nombres, parteno/paterno, materno, fecBaja, fecAntiguedad, estatus
            $paterno = $e['paterno'] ?? $e['parteno'] ?? null;

            $nombreCompleto = trim(collect([
                $e['nombres'] ?? null,
                $paterno,
                $e['materno'] ?? null,
            ])->filter()->implode(' '));


            return [
                'nombre'        => $nombreCompleto !== '' ? $nombreCompleto : '-',
                'gafete'        => $e['gafete'] ?? '-',
                'estatus'       => strtoupper((string)($e['estatus'] ?? 'DESCONOCIDO')),
                'fecAntiguedad' => $e['fecAntiguedad'] ?? null,
                'fecBaja'       => $e['fecBaja'] ?? null,
            ];
        })->all();
    }

}
