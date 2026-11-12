<?php

namespace App\Models\Configuracion;

use App\Models\Empleados\Empleado;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarioTrabajo extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'calendarios_trabajo';

    public $etiquetaCampo = 'nombre';

    protected $fillable = ['nombre', 'dias_laborables', 'hora_entrada', 'hora_salida', 'tolerancia_min', 'refrigerio_min'];

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'calendario_id');
    }
}
