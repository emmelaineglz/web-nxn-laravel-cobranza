<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Server;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Listado con búsqueda y paginación (JSON).
     */
    public function index(Request $request)
    {
        $q    = trim(strtolower($request->query('q', '')));
        $page = max((int)$request->query('page', 1), 1);
        $size = max(min((int)$request->query('size', 10), 100), 1);

        $query = Cliente::query()->with('server'); // 👈 cargar servidor

        if ($q !== '') {
            $query->where(function($w) use ($q){
                $w->whereRaw('LOWER(nombre) LIKE ?', ["%$q%"])
                  ->orWhereRaw('LOWER(razon_social) LIKE ?', ["%$q%"])
                  ->orWhereRaw('LOWER(rfc) LIKE ?', ["%$q%"])
                  ->orWhereRaw('LOWER(domicilio_fiscal) LIKE ?', ["%$q%"])
                  ->orWhereRaw('LOWER(contacto1) LIKE ?', ["%$q%"])
                  ->orWhereRaw('LOWER(contacto2) LIKE ?', ["%$q%"])
                  ->orWhereRaw('LOWER(contacto3) LIKE ?', ["%$q%"])
                  ->orWhereRaw('LOWER(datos_bancarios) LIKE ?', ["%$q%"]);
            });
        }

        $total = $query->count();
        $rows  = $query->orderBy('id','desc')
                       ->skip(($page-1)*$size)
                       ->take($size)
                       ->get();

        // (Opcional) añadir server_name plano
        $rows->transform(function($c){
            $c->server_name = optional($c->server)->name;
            return $c;
        });

        return response()->json([
            'data'  => $rows,
            'page'  => $page,
            'size'  => $size,
            'total' => $total,
            'pages' => max(1, (int)ceil($total / $size)),
        ]);
    }

    /**
     * Crear cliente.
     */
    public function store(Request $request)
    {
        try {
            $rfcRule = Rule::unique('clientes', 'rfc')->whereNull('deleted_at'); // Validar que no haya RFC duplicado, excluyendo eliminados

            $data = $request->validate([
                'nombre'           => ['required','string','max:255'],
                'rfc'              => ['required','string','max:13', $rfcRule],
                'razon_social'     => ['nullable','string','max:255'],
                'domicilio_fiscal' => ['nullable','string','max:255'],
                'contacto1'        => ['nullable','string','max:255'],
                'contacto2'        => ['nullable','string','max:255'],
                'contacto3'        => ['nullable','string','max:255'],
                'datos_bancarios'  => ['nullable','string','max:255'],
                'factura'          => ['boolean'],
                'server_id'        => ['nullable','exists:servers,id'],
                'grupo_empresarial'=> ['nullable','string','max:255'],
                'empresa'          => ['nullable','string','max:255'],
                // JSONs
                'servicios_demanda'                => ['required','array'],
                'servicios_demanda.*.tipo'         => ['required','in:nomina,imss,timbrado,mail,reloj,otro'],
                'servicios_demanda.*.activo'       => ['required','boolean'],
                'servicios_demanda.*.costo'        => ['nullable','numeric','min:0'],
                'servicios_demanda.*.descripcion'  => ['nullable','string','max:255'],
                'servicios_demanda.*.periodicidad' => ['nullable','in:semanal,mensual,bimestral,semestral,anual'],
                'servicios_fijos'                  => ['nullable','array'],
                'servicios_fijos.*.nombre'         => ['required','string','max:255'],
                'servicios_fijos.*.precio'         => ['required','numeric','min:0'],
                'servicios_fijos.*.descripcion'    => ['nullable','string','max:255'],
                'caracteristicas'                  => ['nullable','array'],
                'caracteristicas.*.nombre'         => ['required','string','max:255'],
                'caracteristicas.*.precio'         => ['required','numeric','min:0'],
            ]);

            // Normaliza datos para manejar campos nulos
            $data = $this->normalizeNullable($data);
            $data['factura'] = $request->boolean('factura');
            $data['server_id'] = $request->filled('server_id') ? (int)$request->input('server_id') : null;

            // Crear cliente
            $cliente = Cliente::create($data)->load('server');
            $cliente->server_name = optional($cliente->server)->name;

            return response()->json([
                'message' => 'Cliente creado correctamente',
                'data'    => $cliente,
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error al guardar cliente: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al guardar el cliente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar un cliente específico (JSON con { data }).
     */
    public function show(Cliente $cliente)
    {
        $cliente->load('server');                 // Cargar servidor
        $cliente->server_name = optional($cliente->server)->name; // Asignar nombre del servidor
        return response()->json(['data' => $cliente]);
    }

    /**
     * Actualizar cliente.
     */
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $rfcRule = Rule::unique('clientes','rfc')
            ->whereNull('deleted_at')
            ->ignore($cliente->id);

        $data = $request->validate([
            'nombre'           => ['sometimes','required','string','max:255'],
            'rfc'              => ['sometimes','required','string','max:13', $rfcRule],
            'razon_social'     => ['nullable','string','max:255'],
            'domicilio_fiscal' => ['nullable','string','max:255'],
            'contacto1'        => ['nullable','string','max:255'],
            'contacto2'        => ['nullable','string','max:255'],
            'contacto3'        => ['nullable','string','max:255'],
            'datos_bancarios'  => ['nullable','string','max:255'],
            'factura'          => ['sometimes','boolean'],
            'server_id'        => ['nullable','exists:servers,id'],
            'grupo_empresarial'=> ['nullable','string','max:255'],
            'empresa'          => ['nullable','string','max:255'],
            'servicios_demanda'                => ['sometimes','array'],
            'servicios_demanda.*.tipo'         => ['required','in:nomina,imss,timbrado,mail,reloj,otro'],
            'servicios_demanda.*.activo'       => ['required','boolean'],
            'servicios_demanda.*.costo'        => ['nullable','numeric','min:0'],
            'servicios_demanda.*.descripcion'  => ['nullable','string','max:255'],
            'servicios_demanda.*.periodicidad' => ['nullable','in:semanal,mensual,bimestral,semestral,anual'],
            'servicios_fijos'                  => ['sometimes','nullable','array'],
            'servicios_fijos.*.nombre'         => ['required','string','max:255'],
            'servicios_fijos.*.precio'         => ['required','numeric','min:0'],
            'servicios_fijos.*.descripcion'    => ['nullable','string','max:255'],
            'caracteristicas'                  => ['sometimes','nullable','array'],
            'caracteristicas.*.nombre'         => ['required','string','max:255'],
            'caracteristicas.*.precio'         => ['required','numeric','min:0'],
        ]);

        $data = $this->normalizeNullable($data);
        if ($request->has('factura'))   $data['factura']   = $request->boolean('factura');
        if ($request->has('server_id')) $data['server_id'] = $request->filled('server_id') ? (int)$request->input('server_id') : null;

        $cliente->update($data);
        $cliente->load('server');
        $cliente->server_name = optional($cliente->server)->name;

        return response()->json([
            'message' => 'Cliente actualizado correctamente',
            'data'    => $cliente,
        ]);
    }

    /**
     * Borrado PERMANENTE
     */
    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);

        try {
            // Si el cliente tiene registros relacionados (empleados, timbres, facturas), no se puede eliminar
            if (
                method_exists($cliente, 'empleados') && $cliente->empleados()->exists() ||
                method_exists($cliente, 'timbres')   && $cliente->timbres()->exists()   ||
                method_exists($cliente, 'facturas')  && $cliente->facturas()->exists()
            ) {
                return response()->json([
                    'message' => 'No se puede eliminar: el cliente tiene registros relacionados.'
                ], 409);
            }

            // Eliminar cliente permanentemente
            $cliente->forceDelete(); // Usamos forceDelete() para eliminarlo completamente de la base de datos
            return response()->json(['message' => 'Cliente eliminado permanentemente'], 200);

        } catch (QueryException $e) {
            // En caso de error de integridad referencial
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'No se puede eliminar por restricciones de integridad (FK).'
                ], 409);
            }
            throw $e; // Otros errores → 500
        }
    }

    private function normalizeNullable(array $data): array
    {
        foreach ([
            'razon_social', 'domicilio_fiscal', 'contacto1', 'contacto2', 'contacto3', 'datos_bancarios'
        ] as $k) {
            if (array_key_exists($k, $data)) {
                $data[$k] = (isset($data[$k]) && trim((string)$data[$k]) !== '') ? $data[$k] : null;
            }
        }
        return $data;
    }

    /**
     * Vista para Kardex (con los servidores disponibles).
     */
    public function kardexView()
    {
        $servers = Server::orderBy('name')->get(['id', 'name', 'host']);
        return view('front.kardex', compact('servers'));
    }
}
