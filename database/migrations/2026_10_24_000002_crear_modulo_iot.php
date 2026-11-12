<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Estaciones de trabajo con cámara / lector QR
        Schema::create('iot_estaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();          // estacion-1
            $table->string('ubicacion', 120)->nullable();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->nullOnDelete();
            $table->string('tipo', 20)->default('camara');   // camara | qr
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_ping')->nullable();
            $table->timestamps();
        });

        // Eventos enviados por cada estación
        Schema::create('iot_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estacion_id')->constrained('iot_estaciones')->cascadeOnDelete();
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->nullOnDelete();
            $table->string('tipo', 40);      // ausencia_puesto | uso_celular | regreso | marcacion_qr
            $table->timestamp('detectado_en');
            $table->unsignedTinyInteger('duracion_min')->nullable();
            $table->string('evidencia', 255)->nullable();    // ruta de captura (futuro)
            $table->string('estado', 20)->default('Registrado'); // Registrado | Revisado | Descartado
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iot_eventos');
        Schema::dropIfExists('iot_estaciones');
    }
};
