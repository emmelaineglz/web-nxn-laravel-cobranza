<?php

// database/migrations/2025_08_22_000000_add_server_id_to_clientes.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('server_id')
                  ->nullable()
                  ->constrained('servers')
                  ->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('server_id');
        });
    }
};
