<?php

namespace App\Models\Asistencias;

use App\Models\Empleados\Empleado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudPermiso extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_permiso';

    protected $fillable = ['empleado_id', 'fecha', 'hora_desde', 'hora_hasta', 'motivo', 'estado', 'aprobado_por', 'observacion'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public const ESTADOS = ['Pendiente', 'Aprobada', 'Rechazada'];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
