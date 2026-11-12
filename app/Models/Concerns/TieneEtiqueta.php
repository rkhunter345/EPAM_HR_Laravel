<?php

namespace App\Models\Concerns;

/**
 * Etiqueta legible de un registro (usada en listados y selects).
 * Cada modelo define $etiquetaCampo (por defecto 'nombre').
 */
trait TieneEtiqueta
{
    public function getEtiquetaAttribute(): string
    {
        $campo = $this->etiquetaCampo ?? 'nombre';
        $valor = $this->getAttribute($campo);

        return filled($valor) ? (string) $valor : ('#'.$this->getKey());
    }
}
