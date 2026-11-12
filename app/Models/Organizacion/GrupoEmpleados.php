<?php

namespace App\Models\Organizacion;

use App\Models\Empleados\Empleado;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoEmpleados extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'grupos_empleados';

    public $etiquetaCampo = 'nombre';

    protected $fillable = ['nombre', 'descripcion'];

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'grupo_id');
    }
}
