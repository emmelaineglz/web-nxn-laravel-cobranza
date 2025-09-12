<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('grupo_empresarial')->nullable()->after('factura');
            $table->string('empresa')->nullable()->after('grupo_empresarial');
            $table->json('servicios_demanda')->nullable()->after('empresa');
            $table->json('servicios_fijos')->nullable()->after('servicios_demanda');
            $table->json('caracteristicas')->nullable()->after('servicios_fijos');
        });
    }

    public function down(): void {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['grupo_empresarial','empresa','servicios_demanda','servicios_fijos','caracteristicas']);
        });
    }
};
