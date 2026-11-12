<?php

namespace App\Models\Nomina;

use App\Models\Concerns\TieneEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoPlanilla extends Model
{
    use HasFactory, TieneEtiqueta;

    protected $table = 'periodos_planilla';

    public $etiquetaCampo = 'periodo';

    protected $fillable = ['periodo', 'anio', 'mes', 'estado', 'fecha_cierre', 'fecha_pago',
        'total_ingresos', 'total_descuentos', 'total_neto', 'observacion'];

    protected function casts(): array
    {
        return ['fecha_cierre' => 'date', 'fecha_pago' => 'date',
            'total_ingresos' => 'decimal:2', 'total_descuentos' => 'decimal:2', 'total_neto' => 'decimal:2'];
    }

    public const ESTADOS = ['Borrador', 'Revisión', 'Aprobación', 'Boletas generadas', 'Pagado'];

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePlanilla::class);
    }
}
