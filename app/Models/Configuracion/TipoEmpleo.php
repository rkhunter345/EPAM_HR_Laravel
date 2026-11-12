<?php

namespace App\Models\Configuracion;

use App\Models\Empleados\Empleado;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoEmpleo extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'tipos_empleo';

    public $etiquetaCampo = 'nombre';

    protected $fillable = ['nombre', 'descripcion'];

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'tipo_empleo_id');
    }
}
