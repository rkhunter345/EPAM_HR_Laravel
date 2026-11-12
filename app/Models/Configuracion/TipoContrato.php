<?php

namespace App\Models\Configuracion;

use App\Models\Empleados\Empleado;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoContrato extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'tipos_contrato';

    public $etiquetaCampo = 'nombre';

    protected $fillable = ['nombre', 'duracion_meses', 'descripcion'];

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'tipo_contrato_id');
    }
}
