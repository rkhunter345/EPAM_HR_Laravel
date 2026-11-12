<?php

namespace App\Models\Empleados;

use App\Models\Asistencias\Asistencia;
use App\Models\Asistencias\Justificacion;
use App\Models\Asistencias\SolicitudPermiso;
use App\Models\Asistencias\SolicitudVacaciones;
use App\Models\Configuracion\CalendarioTrabajo;
use App\Models\Configuracion\TipoContrato;
use App\Models\Configuracion\TipoEmpleo;
use App\Models\Nomina\DetallePlanilla;
use App\Models\Organizacion\Compania;
use App\Models\Organizacion\Departamento;
use App\Models\Organizacion\Designacion;
use App\Models\Organizacion\GradoEmpleado;
use App\Models\Organizacion\GrupoEmpleados;
use App\Models\Organizacion\Sucursal;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Expediente del colaborador: datos personales, contrato, salario y documentos.
 * Tabla: empleados
 */
class Empleado extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'empleados';

    public $etiquetaCampo = 'nombre_completo';

    protected $fillable = [
        'codigo', 'primer_nombre', 'segundo_nombre', 'apellido', 'nombre_completo',
        'genero', 'fecha_nacimiento', 'fecha_ingreso', 'estado',
        'compania_id', 'sucursal_id', 'departamento_id', 'designacion_id',
        'grado_empleado_id', 'grupo_id', 'tipo_empleo_id', 'tipo_contrato_id', 'calendario_id',
        'moneda_salario', 'tipo_salario', 'salario_mensual', 'frecuencia_pago', 'metodo_pago', 'asignacion_familiar', 'afp',
        'cuenta_bancaria', 'jornada_laboral', 'horas_semanales', 'proyecto_cliente',
        'fecha_inicio', 'fecha_confirmacion', 'fecha_fin_contrato',
        'tipo_documento', 'numero_documento', 'estado_civil', 'telefono', 'correo_personal',
        'direccion', 'distrito', 'contacto_emergencia', 'telefono_emergencia',
        'documento_principal', 'observaciones', 'id_usuario', 'crear_usuario',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'fecha_inicio' => 'date',
            'fecha_confirmacion' => 'date',
            'fecha_fin_contrato' => 'date',
            'salario_mensual' => 'decimal:2', 'asignacion_familiar' => 'boolean',
            'horas_semanales' => 'float',
            'crear_usuario' => 'boolean',
        ];
    }

    public const ESTADOS = ['Activo', 'En proceso', 'Inactivo', 'Suspendido'];

    // ----------------------------- relaciones -----------------------------
    public function compania(): BelongsTo
    {
        return $this->belongsTo(Compania::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function designacion(): BelongsTo
    {
        return $this->belongsTo(Designacion::class);
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(GradoEmpleado::class, 'grado_empleado_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoEmpleados::class, 'grupo_id');
    }

    public function tipoEmpleo(): BelongsTo
    {
        return $this->belongsTo(TipoEmpleo::class);
    }

    public function tipoContrato(): BelongsTo
    {
        return $this->belongsTo(TipoContrato::class);
    }

    public function calendario(): BelongsTo
    {
        return $this->belongsTo(CalendarioTrabajo::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }

    public function vacaciones(): HasMany
    {
        return $this->hasMany(SolicitudVacaciones::class);
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(SolicitudPermiso::class);
    }

    public function justificaciones(): HasMany
    {
        return $this->hasMany(Justificacion::class);
    }

    public function detallesPlanilla(): HasMany
    {
        return $this->hasMany(DetallePlanilla::class);
    }

    // ------------------------------ utilidades -----------------------------
    public function iniciales(): string
    {
        $partes = preg_split('/\s+/', trim((string) $this->nombre_completo));

        return mb_strtoupper(mb_substr($partes[0] ?? '?', 0, 1).mb_substr($partes[1] ?? '', 0, 1));
    }

    /** Código automático HR-EMP-00001 */
    public static function siguienteCodigo(): string
    {
        $ultimo = static::query()->orderByDesc('id')->value('codigo');
        $numero = $ultimo ? ((int) preg_replace('/\D/', '', $ultimo)) + 1 : 1;

        return 'HR-EMP-'.str_pad((string) $numero, 5, '0', STR_PAD_LEFT);
    }
}
