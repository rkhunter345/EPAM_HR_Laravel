<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 2.H — Integridad de la relación users.empleado_id.
 *
 * Migración INCREMENTAL y REVERSIBLE. Antes de tocar el esquema verifica los
 * datos: si existen cuentas huérfanas o vínculos duplicados, ABORTA con un
 * mensaje claro sin modificar nada (el propietario debe decidir cómo reparar
 * esos datos primero). Nunca borra ni cambia datos automáticamente.
 *
 * Qué hace si los datos son consistentes:
 *  up():   índice ÚNICO en users.empleado_id (una cuenta por expediente) y
 *          clave foránea hacia empleados.id (on delete: set null).
 *  down(): elimina la clave foránea y el índice único.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('empleados')) {
            return;
        }

        // 1) Informe previo: cuentas huérfanas (empleado_id sin expediente).
        $huerfanas = DB::table('users as u')
            ->leftJoin('empleados as e', 'e.id', '=', 'u.empleado_id')
            ->whereNotNull('u.empleado_id')->whereNull('e.id')
            ->pluck('u.usuario')->all();

        // 2) Vínculos duplicados (dos cuentas apuntando al mismo expediente).
        $duplicados = DB::table('users')
            ->select('empleado_id', DB::raw('count(*) as total'))
            ->whereNotNull('empleado_id')->groupBy('empleado_id')->having('total', '>', 1)
            ->pluck('empleado_id')->all();

        if ($huerfanas || $duplicados) {
            $this->abortConInforme($huerfanas, $duplicados);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('empleado_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('empleado_id')->references('id')->on('empleados')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['empleado_id']);
            $table->dropUnique(['empleado_id']);
        });
    }

    private function abortConInforme(array $huerfanas, array $duplicados): void
    {
        $lineas = ["Migración detenida: se requiere decidir cómo reparar datos antes de aplicar la unicidad de users.empleado_id."];
        if ($huerfanas) {
            $lineas[] = 'Cuentas huérfanas (empleado_id inexistente): '.implode(', ', $huerfanas);
        }
        if ($duplicados) {
            $lineas[] = 'Expedientes con más de una cuenta vinculada (empleado_id): '.implode(', ', $duplicados);
        }
        $lineas[] = 'Corrija estos vínculos en el módulo de cuentas y vuelva a ejecutar php artisan migrate.';
        $lineas[] = 'Sus datos NO fueron modificados.';

        throw new RuntimeException(implode("\n", $lineas));
    }
};
