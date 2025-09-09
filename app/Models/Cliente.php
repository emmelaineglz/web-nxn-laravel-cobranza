<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes; // Se mantiene el uso de SoftDeletes para eliminación lógica

    protected $fillable = [
        'nombre', 'razon_social', 'rfc', 'domicilio_fiscal',
        'contacto1', 'contacto2', 'contacto3', 'datos_bancarios',
        'factura', 'server_id',
        'grupo_empresarial', 'empresa',
        'servicios_demanda', 'servicios_fijos', 'caracteristicas',
    ];

    protected $casts = [
        'factura'            => 'boolean',
        'server_id'          => 'integer',
        'servicios_demanda'  => 'array',   // JSON → array
        'servicios_fijos'    => 'array',   // JSON → array
        'caracteristicas'    => 'array',   // JSON → array
    ];

    public function server() 
    {
        return $this->belongsTo(Server::class);
    }
}
