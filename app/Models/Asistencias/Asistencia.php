<?php

namespace App\Models\Asistencias;

use App\Models\Empleados\Empleado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro diario de jornada. La tardanza y los minutos trabajados se calculan
 * en AsistenciaService (hook `saving` del modelo).
 */
class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'empleado_id', 'fecha', 'hora_entrada', 'hora_inicio_refrigerio',
        'hora_fin_refrigerio', 'hora_salida', 'tardanza_min', 'refrigerio_min',
        'exceso_refrigerio_min', 'minutos_trabajados', 'estado', 'origen', 'observacion',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date', 'tardanza_min' => 'integer', 'minutos_trabajados' => 'integer'];
    }

    public const ESTADOS = ['Asistió', 'Tardanza', 'Ausente', 'Justificado', 'Incompleto'];

    public const ORIGENES = ['Web', 'Móvil', 'QR', 'IoT'];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function estaCompleta(): bool
    {
        return filled($this->hora_entrada) && filled($this->hora_salida);
    }
}
