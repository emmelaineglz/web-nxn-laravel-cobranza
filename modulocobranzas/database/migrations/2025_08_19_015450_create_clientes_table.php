<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');                 // requerido
            $table->string('razon_social')->nullable();
            $table->string('rfc')->unique();          // requerido + único
            $table->string('domicilio_fiscal')->nullable();

            // 👇 Contactos opcionales (NULL permitido)
            $table->string('contacto1')->nullable();
            $table->string('contacto2')->nullable();
            $table->string('contacto3')->nullable();

            $table->string('datos_bancarios')->nullable();

            // Factura como booleano
            $table->boolean('factura')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
