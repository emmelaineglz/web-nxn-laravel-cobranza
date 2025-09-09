<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Agregar SoftDeletes si no existe
            if (!Schema::hasColumn('clientes', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Solo crear el índice único en RFC si NO existe
        if (!$this->uniqueIndexExists('clientes', 'rfc')) {
            Schema::table('clientes', function (Blueprint $table) {
                // Asegura tipo, si necesitas cambiar longitud requiere doctrine/dbal
                // $table->string('rfc', 13)->change(); // opcional
                $table->unique('rfc', 'clientes_rfc_unique');
            });
        }
    }

    public function down(): void
    {
        // Drop softDeletes si existe
        if (Schema::hasColumn('clientes', 'deleted_at')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Quitar único solo si existe
        if ($this->uniqueIndexExists('clientes', 'rfc')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropUnique('clientes_rfc_unique');
            });
        }
    }

    private function uniqueIndexExists(string $table, string $column): bool
    {
        $dbName = DB::getDatabaseName();
        $rows = DB::select("
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = ?
              AND table_name   = ?
              AND column_name  = ?
              AND non_unique   = 0
            LIMIT 1
        ", [$dbName, $table, $column]);

        return !empty($rows);
    }
};
