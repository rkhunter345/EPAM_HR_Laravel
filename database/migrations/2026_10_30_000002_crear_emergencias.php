<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->unsignedBigInteger('reportado_por_usuario')->nullable();
            $table->string('reportado_por')->nullable();     // nombre de quien reporta
            $table->string('motivo');
            $table->dateTime('fecha_hora');
            $table->string('lugar')->nullable();
            $table->text('observacion')->nullable();
            $table->string('adjunto')->nullable();
            $table->string('estado', 30)->default('Pendiente');
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->dateTime('revisado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergencias');
    }
};
