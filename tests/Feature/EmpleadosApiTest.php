<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\EmpleadosApi;
use Illuminate\Http\Client\RequestException;
use PHPUnit\Framework\Attributes\Test;

class EmpleadosApiTest extends TestCase
{
    #[Test]
    public function puede_consultar_empleados_y_obtener_totales(): void
    {
        Http::fake([
            'https://580f8oe376.execute-api.us-east-1.amazonaws.com/dev/empleados*' => Http::response([
                'employees' => [
                    ['id'=>1,'nombre'=>'Juan Pérez','estatus'=>'ACTIVO','fecha_baja'=>null],
                    ['id'=>2,'nombre'=>'María López','estatus'=>'BAJA','fecha_baja'=>'2024-08-15'],
                ],
                'totales' => [
                    'total_general'=>2,'total_activos'=>1,'total_bajas'=>1,
                ],
            ], 200),
        ]);

        $api = new EmpleadosApi();
        $resultado = $api->consultar(700, 2024, '08');

        $this->assertArrayHasKey('employees', $resultado);
        $this->assertCount(2, $resultado['employees']);
        $this->assertEquals('Juan Pérez', $resultado['employees'][0]['nombre']);
        $this->assertEquals(1, $resultado['totales']['total_activos']);
        $this->assertEquals(1, $resultado['totales']['total_bajas']);
    }

    #[Test]
    public function lanza_excepcion_si_api_key_es_invalida(): void
    {
        Http::fake([
            'https://580f8oe376.execute-api.us-east-1.amazonaws.com/dev/empleados*' =>
                Http::response(['error' => 'API Key inválida o ausente'], 401),
        ]);

        $api = new EmpleadosApi();

        $this->expectException(RequestException::class);

        // Debe lanzar RequestException por el ->throw() en el servicio
        $api->consultar(700, 2024, '08');
    }
}
