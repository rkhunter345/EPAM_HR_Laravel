<?php

namespace App\Models\Sistema;

use App\Models\Asistencias\Asistencia;
use App\Models\Empleados\Empleado;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteGenerado extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'reportes_generados';

    public $etiquetaCampo = 'tipo';

    protected $fillable = ['tipo', 'periodo', 'empleado_id', 'generado_por', 'fecha', 'detalle'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public const TIPOS = ['Asistencia', 'Tardanzas y ausencias', 'Horas trabajadas',
        'Planilla de remuneraciones', 'Recibo de pago', 'Constancia de trabajo'];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
