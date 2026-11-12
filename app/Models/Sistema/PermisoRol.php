<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Model;

class PermisoRol extends Model
{
    protected $table = 'permisos_rol';

    protected $fillable = ['rol', 'modulo', 'permitido'];

    protected $casts = ['permitido' => 'boolean'];
}
