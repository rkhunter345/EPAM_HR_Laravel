<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Empleados\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends ModuloCrudController
{
    protected string $modelo = User::class;
    protected string $vista = 'configuracion.usuarios';
    protected string $ruta = 'usuarios';
    protected string $titulo = 'Usuario';
    protected string $singular = 'usuario';
    protected string $descripcion = 'Cuentas de acceso y roles del sistema.';
    protected string $orden = 'usuario';
    protected array $buscarEn = ['name', 'usuario', 'email'];
    protected array $columnas = [
        ['campo' => 'name', 'label' => 'Nombre'],
        ['campo' => 'usuario', 'label' => 'Usuario'],
        ['campo' => 'rol', 'label' => 'Rol', 'badge' => true],
        ['campo' => 'email', 'label' => 'Correo'],
    ];
    protected array $campos = [
        ['name' => 'name', 'label' => 'Nombre completo', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'usuario', 'label' => 'Usuario de acceso', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'email', 'label' => 'Correo', 'type' => 'email', 'col' => 6],
        ['name' => 'rol', 'label' => 'Rol', 'type' => 'select', 'col' => 6, 'opciones' => User::ROLES],
        ['name' => 'empleado_id', 'label' => 'Empleado vinculado', 'type' => 'select_dinamico', 'col' => 6, 'fuente' => 'empleados'],
        ['name' => 'password', 'label' => 'Contraseña', 'type' => 'password', 'col' => 6],
        ['name' => 'activo', 'label' => 'Activo', 'type' => 'checkbox', 'col' => 12],
    ];

    protected function opciones(): array
    {
        return ['empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id')];
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => 'required|string|max:120',
            'usuario' => 'required|string|max:60|unique:users,usuario',
            'email' => 'nullable|email|unique:users,email',
            'rol' => 'required|in:'.implode(',', array_keys(User::ROLES)),
            'empleado_id' => 'nullable|exists:empleados,id',
            'password' => 'required|string|min:6',
            'activo' => 'boolean',
        ]);

        $datos['password'] = Hash::make($datos['password']);
        User::create($datos);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        $datos = $request->validate([
            'name' => 'required|string|max:120',
            'usuario' => 'required|string|max:60|unique:users,usuario,'.$usuario->id,
            'email' => 'nullable|email|unique:users,email,'.$usuario->id,
            'rol' => 'required|in:'.implode(',', array_keys(User::ROLES)),
            'empleado_id' => 'nullable|exists:empleados,id',
            'password' => 'nullable|string|min:6',
            'activo' => 'boolean',
        ]);

        if (blank($datos['password'] ?? null)) {
            unset($datos['password']);
        } else {
            $datos['password'] = Hash::make($datos['password']);
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario actualizado.');
    }
}
