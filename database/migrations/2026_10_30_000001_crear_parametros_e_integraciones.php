<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametros', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 80)->unique();
            $table->string('grupo', 40)->default('general');
            $table->string('etiqueta');
            $table->string('valor')->nullable();
            $table->string('tipo', 20)->default('texto'); // texto | numero | booleano | tiempo
            $table->string('unidad', 20)->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamps();
        });

        Schema::create('integraciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo', 40);
            $table->string('estado', 30)->default('Conectado');
            $table->string('url')->nullable();
            $table->text('detalle')->nullable();
            $table->timestamp('ultima_sincronizacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('permisos_rol', function (Blueprint $table) {
            $table->id();
            $table->string('rol', 20);
            $table->string('modulo', 60);
            $table->boolean('permitido')->default(false);
            $table->timestamps();
            $table->unique(['rol', 'modulo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_rol');
        Schema::dropIfExists('integraciones');
        Schema::dropIfExists('parametros');
    }
};
