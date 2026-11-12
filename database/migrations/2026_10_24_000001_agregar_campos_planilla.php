<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->boolean('asignacion_familiar')->default(false)->after('metodo_pago');
            $table->string('afp', 20)->default('ONP')->after('asignacion_familiar');
        });

        Schema::table('detalles_planilla', function (Blueprint $table) {
            $table->decimal('asig_familiar', 12, 2)->default(0)->after('basico');
            $table->string('afp', 20)->default('ONP')->after('aporte_pension');
            $table->decimal('essalud', 12, 2)->default(0)->after('renta_5ta');
            $table->decimal('sctr', 12, 2)->default(0)->after('essalud');
            $table->decimal('costo_empresa', 12, 2)->default(0)->after('sctr');
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn(['asignacion_familiar', 'afp']);
        });
        Schema::table('detalles_planilla', function (Blueprint $table) {
            $table->dropColumn(['asig_familiar', 'afp', 'essalud', 'sctr', 'costo_empresa']);
        });
    }
};
