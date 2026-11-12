<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salidas_temporales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->string('motivo', 60);
            $table->string('observacion', 500)->nullable();
            $table->string('documento', 255)->nullable();
            $table->dateTime('hora_salida');
            $table->dateTime('hora_retorno')->nullable();
            $table->string('estado', 30)->default('Monitoreo pausado');
            $table->unsignedTinyInteger('tolerancia_min')->default(5);
            $table->timestamps();
        });

        // Datos de demostración: primeras 3 empleados con una salida completada
        $empleados = DB::table('empleados')->orderBy('id')->limit(3)->pluck('id');
        foreach ($empleados as $eid) {
            DB::table('salidas_temporales')->insert([
                'empleado_id' => $eid,
                'motivo' => 'Necesidad personal.',
                'observacion' => 'Salida breve de demostración.',
                'hora_salida' => now()->setTime(10, 35),
                'hora_retorno' => now()->setTime(10, 37),
                'estado' => 'Completada',
                'tolerancia_min' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if ($empleados->isNotEmpty()) {
            DB::table('salidas_temporales')->insert([
                'empleado_id' => $empleados->first(),
                'motivo' => 'Atención médica.',
                'observacion' => 'Control médico programado (demostración).',
                'hora_salida' => now()->subMinutes(30),
                'hora_retorno' => null,
                'estado' => 'Monitoreo pausado',
                'tolerancia_min' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('salidas_temporales');
    }
};
