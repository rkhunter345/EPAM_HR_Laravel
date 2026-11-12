<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_planilla', function (Blueprint $table) {
            $table->id();
            $table->string('periodo', 40);
            $table->integer('anio');
            $table->integer('mes');
            $table->string('estado', 30)->default('Borrador');
            $table->date('fecha_cierre')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->decimal('total_ingresos', 14, 2)->default(0);
            $table->decimal('total_descuentos', 14, 2)->default(0);
            $table->decimal('total_neto', 14, 2)->default(0);
            $table->text('observacion')->nullable();
            $table->timestamps();
        });

        Schema::create('detalles_planilla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_planilla_id')->constrained('periodos_planilla')->cascadeOnDelete();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->decimal('basico', 12, 2)->default(0);
            $table->decimal('horas_extras', 12, 2)->default(0);
            $table->decimal('bono_asistencia', 12, 2)->default(0);
            $table->decimal('total_ingresos', 12, 2)->default(0);
            $table->decimal('desc_tardanza', 12, 2)->default(0);
            $table->decimal('desc_ausencia', 12, 2)->default(0);
            $table->decimal('aporte_pension', 12, 2)->default(0);
            $table->decimal('renta_5ta', 12, 2)->default(0);
            $table->decimal('adelantos', 12, 2)->default(0);
            $table->decimal('total_descuentos', 12, 2)->default(0);
            $table->decimal('neto', 12, 2)->default(0);
            $table->integer('tardanzas_min')->default(0);
            $table->integer('ausencias')->default(0);
            $table->timestamps();
        });

        Schema::create('conceptos_salariales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30);
            $table->string('nombre');
            $table->string('tipo', 30);
            $table->decimal('monto', 12, 2)->default(0);
            $table->decimal('porcentaje', 6, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 60);
            $table->string('periodo', 40)->nullable();
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->nullOnDelete();
            $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha');
            $table->text('detalle')->nullable();
            $table->timestamps();
        });

        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('usuario', 60)->nullable();
            $table->string('accion');
            $table->string('modulo')->nullable();
            $table->string('detalle')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['auditorias', 'reportes_generados', 'conceptos_salariales', 'detalles_planilla', 'periodos_planilla'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
