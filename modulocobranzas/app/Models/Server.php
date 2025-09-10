<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = ['name','host','username','password'];
    protected $casts = ['password' => 'encrypted'];
    protected $hidden = ['password'];

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}
