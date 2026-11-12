<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_entrada')->nullable();
            $table->time('hora_inicio_refrigerio')->nullable();
            $table->time('hora_fin_refrigerio')->nullable();
            $table->time('hora_salida')->nullable();
            $table->integer('tardanza_min')->default(0);
            $table->integer('refrigerio_min')->default(0);
            $table->integer('exceso_refrigerio_min')->default(0);
            $table->integer('minutos_trabajados')->default(0);
            $table->string('estado', 20)->default('Asistió');
            $table->string('origen', 20)->default('Web');
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->unique(['empleado_id', 'fecha']);
        });

        Schema::create('justificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('motivo');
            $table->string('horario_afectado')->nullable();
            $table->string('documento')->nullable();
            $table->string('estado', 30)->default('Pendiente');
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('comunicante')->nullable();
            $table->text('explicacion')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();
        });

        Schema::create('solicitudes_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->string('tipo', 30)->default('Vacaciones');
            $table->date('desde');
            $table->date('hasta');
            $table->decimal('dias', 5, 1)->default(0);
            $table->string('estado', 20)->default('Pendiente');
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();
        });

        Schema::create('solicitudes_permiso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_desde')->nullable();
            $table->time('hora_hasta')->nullable();
            $table->string('motivo');
            $table->string('estado', 20)->default('Pendiente');
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['solicitudes_permiso', 'solicitudes_vacaciones', 'justificaciones', 'asistencias'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
