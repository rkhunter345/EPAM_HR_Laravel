<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Empleados\Empleado;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario del sistema. Roles: ADMIN, RRHH, SUPERVISOR, EMPLEADO.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = [
        'ADMIN' => 'Administrador del sistema',
        'RRHH' => 'Responsable de Recursos Humanos',
        'SUPERVISOR' => 'Supervisor o jefe de área',
        'EMPLEADO' => 'Empleado',
    ];

    protected $fillable = ['name', 'usuario', 'email', 'password', 'rol', 'empleado_id', 'activo'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function getNombreRolAttribute(): string
    {
        return self::ROLES[$this->rol] ?? $this->rol;
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'ADMIN';
    }

    /** Puede gestionar personal, asistencia, planilla y configuración. */
    public function gestionaRrhh(): bool
    {
        return in_array($this->rol, ['ADMIN', 'RRHH'], true);
    }

    /** Revisa y aprueba solicitudes de su área. */
    public function esSupervisor(): bool
    {
        return $this->rol === 'SUPERVISOR';
    }

    public function esEmpleado(): bool
    {
        return $this->rol === 'EMPLEADO';
    }

    public function inicialAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->name ?: $this->usuario ?: '?', 0, 1));
    }
}
