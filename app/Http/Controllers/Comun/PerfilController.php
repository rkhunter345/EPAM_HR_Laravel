<?php

namespace App\Http\Controllers\Comun;

use App\Http\Controllers\Concerns\ResuelveEmpleado;
use App\Http\Controllers\Controller;
use App\Models\Sistema\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    use ResuelveEmpleado;

    public function index()
    {
        return view('comun.perfil', [
            'usuario' => auth()->user(),
            'empleado' => $this->empleadoActual(),
            'sesiones' => DB::table('sessions')->where('user_id', auth()->id())->count(),
        ]);
    }

    public function actualizar(Request $request)
    {
        $u = auth()->user();
        $datos = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'nullable|email|max:120',
        ]);

        $u->update($datos);

        Auditoria::create([
            'usuario_id' => $u->id, 'usuario' => $u->name, 'accion' => 'Actualización de perfil',
            'modulo' => 'Perfil', 'detalle' => 'Datos de contacto actualizados',
            'ip' => $request->ip(), 'created_at' => now(),
        ]);

        return back()->with('ok', 'Datos de perfil actualizados.');
    }

    public function contrasena(Request $request)
    {
        $datos = $request->validate([
            'actual' => 'required',
            'password' => 'required|min:8|confirmed',
        ], ['password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.']);

        if (! Hash::check($datos['actual'], auth()->user()->password)) {
            return back()->withErrors(['actual' => 'La contraseña actual no coincide.']);
        }

        auth()->user()->update(['password' => Hash::make($datos['password'])]);

        Auditoria::create([
            'usuario_id' => auth()->id(), 'usuario' => auth()->user()->name, 'accion' => 'Cambio de contraseña',
            'modulo' => 'Perfil', 'detalle' => 'Cambio de credenciales propio',
            'ip' => $request->ip(), 'created_at' => now(),
        ]);

        return back()->with('ok', 'Contraseña actualizada con el mecanismo de restablecimiento (sin mostrar la anterior).');
    }
}
