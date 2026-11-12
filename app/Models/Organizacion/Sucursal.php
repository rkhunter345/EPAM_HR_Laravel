<?php

namespace App\Models\Organizacion;

use App\Models\Empleados\Empleado;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'sucursales';

    public $etiquetaCampo = 'nombre';

    protected $fillable = ['compania_id', 'nombre', 'ciudad', 'direccion', 'responsable', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function compania(): BelongsTo
    {
        return $this->belongsTo(Compania::class);
    }

    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class);
    }

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class);
    }
}
