<?php

namespace App\Models\Asistencias;

use App\Models\Empleados\Empleado;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudVacaciones extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_vacaciones';

    protected $fillable = ['empleado_id', 'tipo', 'desde', 'hasta', 'dias', 'estado', 'aprobado_por', 'motivo', 'observacion'];

    protected function casts(): array
    {
        return ['desde' => 'date', 'hasta' => 'date', 'dias' => 'float'];
    }

    public const TIPOS = ['Vacaciones', 'Permiso', 'Licencia'];

    public const ESTADOS = ['Pendiente', 'Aprobada', 'Rechazada'];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /** Días corridos del rango solicitado */
    public function calcularDias(): float
    {
        if (! $this->desde || ! $this->hasta) {
            return 0;
        }

        return Carbon::parse($this->desde)->diffInDays(Carbon::parse($this->hasta)) + 1;
    }
}
