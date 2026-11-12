<?php

namespace App\Models\Organizacion;

use App\Models\Empleados\Empleado;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compania extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'companias';

    public $etiquetaCampo = 'nombre';

    protected $fillable = ['nombre', 'ruc', 'moneda', 'telefono', 'correo', 'direccion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class);
    }

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class);
    }
}
