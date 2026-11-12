<?php

namespace App\Models\Sistema;

use App\Models\Empleados\Empleado;
use App\Models\Organizacion\Sucursal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IotEstacion extends Model
{
    protected $table = 'iot_estaciones';
    protected $fillable = ['nombre', 'ubicacion', 'sucursal_id', 'empleado_id', 'tipo', 'activo', 'ultimo_ping'];
    protected $casts = ['activo' => 'boolean', 'ultimo_ping' => 'datetime'];

    public const TIPOS = ['camara', 'qr'];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(IotEvento::class, 'estacion_id');
    }
}
