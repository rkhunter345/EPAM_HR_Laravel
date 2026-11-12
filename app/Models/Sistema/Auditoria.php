<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Model;

/**
 * Auditoría de creación, modificación, aprobación, rechazo y descarga.
 * Se alimenta desde el middleware RegistrarAuditoria y desde los controladores.
 */
class Auditoria extends Model
{
    protected $table = 'auditorias';

    public $timestamps = false;

    protected $fillable = ['usuario_id', 'usuario', 'accion', 'modulo', 'detalle', 'ip', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
