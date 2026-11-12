<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('primer_nombre');
            $table->string('segundo_nombre')->nullable();
            $table->string('apellido');
            $table->string('nombre_completo')->nullable();
            $table->string('genero', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->string('estado', 20)->default('Activo');

            $table->foreignId('compania_id')->nullable()->constrained('companias')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->foreignId('designacion_id')->nullable()->constrained('designaciones')->nullOnDelete();
            $table->foreignId('grado_empleado_id')->nullable()->constrained('grados_empleado')->nullOnDelete();
            $table->foreignId('grupo_id')->nullable()->constrained('grupos_empleados')->nullOnDelete();
            $table->foreignId('tipo_empleo_id')->nullable()->constrained('tipos_empleo')->nullOnDelete();
            $table->foreignId('tipo_contrato_id')->nullable()->constrained('tipos_contrato')->nullOnDelete();
            $table->foreignId('calendario_id')->nullable()->constrained('calendarios_trabajo')->nullOnDelete();

            $table->string('moneda_salario', 10)->default('PEN');
            $table->string('tipo_salario', 30)->default('Mensual');
            $table->decimal('salario_mensual', 12, 2)->default(0);
            $table->string('frecuencia_pago', 30)->default('Mensual');
            $table->string('metodo_pago', 40)->default('Transferencia bancaria');
            $table->string('cuenta_bancaria', 40)->nullable();
            $table->string('jornada_laboral', 40)->default('Tiempo completo');
            $table->decimal('horas_semanales', 5, 1)->nullable();
            $table->string('proyecto_cliente')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_confirmacion')->nullable();
            $table->date('fecha_fin_contrato')->nullable();

            $table->string('tipo_documento', 30)->nullable();
            $table->string('numero_documento', 20)->nullable();
            $table->string('estado_civil', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('correo_personal')->nullable();
            $table->string('direccion')->nullable();
            $table->string('distrito')->nullable();
            $table->string('contacto_emergencia')->nullable();
            $table->string('telefono_emergencia', 30)->nullable();
            $table->string('documento_principal')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('id_usuario')->default(false);
            $table->boolean('crear_usuario')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
