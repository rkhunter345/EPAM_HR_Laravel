<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ruc', 20)->nullable();
            $table->string('moneda', 10)->default('PEN');
            $table->string('telefono', 30)->nullable();
            $table->string('correo')->nullable();
            $table->string('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compania_id')->constrained('companias')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('ciudad')->nullable();
            $table->string('direccion')->nullable();
            $table->string('responsable')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('responsable')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('designaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('departamentos')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('grados_empleado', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('nivel')->default(1);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('grupos_empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('tipos_empleo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('tipos_contrato', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('duracion_meses')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('calendarios_trabajo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('dias_laborables', 60)->default('Lun-Vie');
            $table->time('hora_entrada')->default('08:00:00');
            $table->time('hora_salida')->default('17:00:00');
            $table->integer('tolerancia_min')->default(10);
            $table->integer('refrigerio_min')->default(45);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['calendarios_trabajo', 'tipos_contrato', 'tipos_empleo', 'grupos_empleados',
            'grados_empleado', 'designaciones', 'departamentos', 'sucursales', 'companias'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
