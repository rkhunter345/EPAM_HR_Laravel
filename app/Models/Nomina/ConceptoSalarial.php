<?php

namespace App\Models\Nomina;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptoSalarial extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'conceptos_salariales';

    public $etiquetaCampo = 'nombre';

    protected $fillable = ['codigo', 'nombre', 'tipo', 'monto', 'porcentaje', 'activo', 'descripcion'];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2', 'porcentaje' => 'float', 'activo' => 'boolean'];
    }

    public const TIPOS = ['Ingreso', 'Descuento', 'Aporte', 'Impuesto', 'Aporte empleador'];
}
