<?php
// app/Http/Controllers/ServerController.php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    /**
     * Mostrar lista de servidores.
     */
    public function index()
    {
        $servers = Server::latest()->paginate(10);
        return view('servers.index', compact('servers'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        return view('servers.create');
    }

    /**
     * Guardar nuevo servidor.
     */
    public function store(Request $request)
{
    $data = $request->validate([
        'name'     => ['required','string','max:100'],
        'biz_name' => ['nullable','string','max:150'],
        'host'     => ['nullable','string','max:255'],
        'username' => ['required','string','max:100'],
        'password' => ['required','string','max:255'],
    ]);

    $s = Server::create($data);

    return response()->json([
        'ok' => true,
        'server' => [
            'id' => $s->id,
            'name' => $s->name,
            'biz_name' => $s->biz_name,
            'host' => $s->host,
            'username' => $s->username,
            'created_at_fmt' => $s->created_at->format('Y-m-d H:i'),
        ],
    ]);
}
    /**
     * Formulario de edición.
     */
    public function edit(Server $server)
    {
        // ¡Ojo! $server->password ya llega DESCIFRADO por el cast.
        // No lo muestres en la vista. Si quieres permitir cambiarla,
        // deja el campo vacío y cámbiala sólo si el usuario escribe algo.
        return view('servers.edit', compact('server'));
    }

    /**
     * Actualizar servidor.
     */
    public function update(Request $request, Server $server)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'biz_name' => ['nullable','string','max:150'],
            'host'     => ['nullable','string','max:255'],
            'username' => ['required','string','max:100'],
            'password' => ['nullable','string','max:255'],
        ]);
        if(empty($data['password'])) unset($data['password']);

        $server->update($data);

        return response()->json([
            'ok' => true,
            'server' => [
                'id' => $server->id,
                'name' => $server->name,
                'biz_name' => $server->biz_name,
                'host' => $server->host,
                'username' => $server->username,
                'created_at_fmt' => $server->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Eliminar servidor.
     */
    public function destroy(Server $server)
    {
        $server->delete();
        return response()->json(['ok'=>true]);
    }
}
