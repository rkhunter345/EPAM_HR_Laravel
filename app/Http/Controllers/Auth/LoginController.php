<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    /** FASE 2.G: límite de intentos por usuario+IP (5 intentos / 1 minuto). */
    private const MAX_INTENTOS = 5;

    private function claveIntento(Request $request): string
    {
        return 'login:'.mb_strtolower((string) $request->input('usuario')).'|'.$request->ip();
    }
    public function form()
    {
        if (Auth::check()) {
            return redirect()->route('tablero');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ], [
            'usuario.required' => 'Ingrese su usuario.',
            'password.required' => 'Ingrese su contraseña.',
        ]);

        $clave = $this->claveIntento($request);
        if (Cache::get($clave.':intentos', 0) >= self::MAX_INTENTOS) {
            return back()->withErrors(['usuario' => 'Demasiados intentos. Espere un minuto y vuelva a intentarlo.'])->withInput();
        }

        if (Auth::attempt(['usuario' => $datos['usuario'], 'password' => $datos['password'], 'activo' => true], $request->boolean('recordar'))) {
            $request->session()->regenerate();
            Cache::forget($clave.':intentos');
            Cache::forget($clave.':bloqueo');

            return redirect()->intended(route('tablero'));
        }

        $intentos = Cache::get($clave.':intentos', 0) + 1;
        Cache::put($clave.':intentos', $intentos, now()->addMinute());

        // Mensaje genérico: no revela si falló el usuario o la contraseña.
        return back()->withErrors(['usuario' => 'Usuario o contraseña incorrectos.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
