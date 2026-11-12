<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 2.H â€” Integridad de la relaciÃ³n users.empleado_id.
 *
 * MigraciÃ³n INCREMENTAL y REVERSIBLE. Antes de tocar el esquema verifica los
 * datos: si existen cuentas huÃ©rfanas o vÃ­nculos duplicados, ABORTA con un
 * mensaje claro sin modificar nada (el propietario debe decidir cÃ³mo reparar
 * esos datos primero). Nunca borra ni cambia datos automÃ¡ticamente.
 *
 * QuÃ© hace si los datos son consistentes:
 *  up():   Ã­ndice ÃšNICO en users.empleado_id (una cuenta por expediente) y
 *          clave forÃ¡nea hacia empleados.id (on delete: set null).
 *  down(): elimina la clave forÃ¡nea y el Ã­ndice Ãºnico.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('empleados')) {
            return;
        }

        // 1) Informe previo: cuentas huÃ©rfanas (empleado_id sin expediente).
        $huerfanas = DB::table('users as u')
            ->leftJoin('empleados as e', 'e.id', '=', 'u.empleado_id')
            ->whereNotNull('u.empleado_id')->whereNull('e.id')
            ->pluck('u.usuario')->all();

        // 2) VÃ­nculos duplicados (dos cuentas apuntando al mismo expediente).
        $duplicados = DB::table('users')
            ->select('empleado_id', DB::raw('count(*) as total'))
            ->whereNotNull('empleado_id')->groupBy('empleado_id')->havingRaw('count(*) > 1')
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
        $lineas = ["MigraciÃ³n detenida: se requiere decidir cÃ³mo reparar datos antes de aplicar la unicidad de users.empleado_id."];
        if ($huerfanas) {
            $lineas[] = 'Cuentas huÃ©rfanas (empleado_id inexistente): '.implode(', ', $huerfanas);
        }
        if ($duplicados) {
            $lineas[] = 'Expedientes con mÃ¡s de una cuenta vinculada (empleado_id): '.implode(', ', $duplicados);
        }
        $lineas[] = 'Corrija estos vÃ­nculos en el mÃ³dulo de cuentas y vuelva a ejecutar php artisan migrate.';
        $lineas[] = 'Sus datos NO fueron modificados.';

        throw new RuntimeException(implode("\n", $lineas));
    }
};

