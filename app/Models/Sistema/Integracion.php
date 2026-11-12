<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Model;

class Integracion extends Model
{
    protected $table = 'integraciones';

    protected $fillable = ['nombre', 'tipo', 'estado', 'url', 'detalle', 'ultima_sincronizacion', 'activo'];

    protected $casts = ['ultima_sincronizacion' => 'datetime', 'activo' => 'boolean'];
}
