<?php

namespace App\Http\Middleware;

use App\Models\Sistema\Auditoria;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guarda en la tabla auditorias cada acción de escritura (POST/PUT/PATCH/DELETE)
 * con usuario, módulo, ruta e IP. No interrumpe la petición si falla el registro.
 */
class RegistrarAuditoria
{
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && $request->user()
            && $request->route()) {
            try {
                Auditoria::create([
                    'usuario_id' => $request->user()->id,
                    'usuario' => $request->user()->usuario,
                    'accion' => $request->method().' '.$request->route()->getName(),
                    'modulo' => (string) $request->route()->getPrefix(),
                    'detalle' => mb_substr($request->path(), 0, 180),
                    'ip' => $request->ip(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable) {
                // la auditoría nunca debe bloquear la operación
            }
        }

        return $respuesta;
    }
}
