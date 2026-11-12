<?php

namespace App\Models\Organizacion;

use App\Models\Empleados\Empleado;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'departamentos';

    public $etiquetaCampo = 'nombre';

    protected $fillable = ['sucursal_id', 'nombre', 'responsable', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function designaciones(): HasMany
    {
        return $this->hasMany(Designacion::class);
    }

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class);
    }
}
