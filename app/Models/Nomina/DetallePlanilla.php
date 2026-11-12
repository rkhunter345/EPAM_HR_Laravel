<?php

namespace App\Models\Nomina;

use App\Models\Empleados\Empleado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePlanilla extends Model
{
    use HasFactory;

    protected $table = 'detalles_planilla';

    protected $fillable = ['periodo_planilla_id', 'empleado_id', 'basico', 'asig_familiar', 'horas_extras',
        'bono_asistencia', 'total_ingresos', 'desc_tardanza', 'desc_ausencia', 'aporte_pension', 'afp',
        'renta_5ta', 'adelantos', 'total_descuentos', 'neto', 'tardanzas_min', 'ausencias',
        'essalud', 'sctr', 'costo_empresa'];

    protected function casts(): array
    {
        return ['basico' => 'decimal:2', 'asig_familiar' => 'decimal:2', 'horas_extras' => 'decimal:2',
            'bono_asistencia' => 'decimal:2', 'essalud' => 'decimal:2', 'sctr' => 'decimal:2',
            'costo_empresa' => 'decimal:2',
            'total_ingresos' => 'decimal:2', 'desc_tardanza' => 'decimal:2', 'desc_ausencia' => 'decimal:2',
            'aporte_pension' => 'decimal:2', 'renta_5ta' => 'decimal:2', 'adelantos' => 'decimal:2',
            'total_descuentos' => 'decimal:2', 'neto' => 'decimal:2'];
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoPlanilla::class, 'periodo_planilla_id');
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
