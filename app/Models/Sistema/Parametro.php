<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    protected $table = 'parametros';

    protected $fillable = ['clave', 'grupo', 'etiqueta', 'valor', 'tipo', 'unidad', 'descripcion', 'actualizado_por'];

    public static function valor(string $clave, $porDefecto = null)
    {
        return static::query()->where('clave', $clave)->value('valor') ?? $porDefecto;
    }

    public function getValorBoolAttribute(): bool
    {
        return in_array((string) $this->valor, ['1', 'true', 'si', 'sí', 'on'], true);
    }
}
