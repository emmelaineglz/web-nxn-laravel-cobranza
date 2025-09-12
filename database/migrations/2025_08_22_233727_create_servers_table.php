<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');        // Nombre del servidor
            $table->string('host');        // Dirección o IP
            $table->string('username');    // Usuario
            $table->text('password');      // Contraseña (cifrada)
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('servers');
    }
};

