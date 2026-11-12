@extends('layouts.app')

@section('titulo', 'Salida temporal')
@section('migas', 'Mi jornada / Salida temporal')

@section('contenido')
<style>
.sal-grid{display:grid;grid-template-columns:1.45fr 1fr;gap:14px;align-items:start}
@media(max-width:1000px){.sal-grid{grid-template-columns:1fr}}
.sal-lbl{display:block;font-size:12px;font-weight:700;color:#5b6b7f;margin:12px 0 5px}
.sal-ctrl{width:100%;padding:10px 12px;border:1px solid #e6e9ef;border-radius:9px;font:inherit;background:#fbfcfe;color:#1f2733}
.sal-tot{display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px dashed #eef1f5;font-size:13.5px}
.sal-tot:last-child{border-bottom:0}
.sal-tot span{color:#5b6b7f}
.sal-nota{font-size:11.5px;color:#8b98a9;margin-top:6px;line-height:1.5}
.sal-aviso{margin-top:12px;background:#eaf4fe;border:1px solid #bcd9f7;border-radius:10px;padding:10px 12px;font-size:12.5px;color:#1257a8;line-height:1.55}
.sal-azul{width:100%;margin-top:14px;background:#2490ef;color:#fff;border:0;border-radius:9px;padding:11px 14px;font:inherit;font-weight:700;cursor:pointer}
.sal-verde{width:100%;margin-top:12px;background:#16a34a;color:#fff;border:0;border-radius:9px;padding:11px 14px;font:inherit;font-weight:700;cursor:pointer}
.sal-bd{display:inline-block;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px">
    <div style="display:flex;align-items:center;gap:10px">
        @if($activa)
            <span class="sal-bd" style="background:#fef3c7;color:#92400e">● {{ $activa->estado }} · {{ $activa->motivo }}</span>
        @else
            <span class="sal-bd" style="background:#dcfce7;color:#166534">En jornada laboral</span>
        @endif
        @if($empleado)<span style="color:#5b6b7f;font-size:13px">{{ collect(explode(' ', $empleado->nombre_completo))->first() }}</span>@endif
    </div>
    <div style="text-align:right">
        <div style="font-weight:800;font-size:16px">{{ now()->format('H:i:s') }}</div>
        <small style="color:#8b98a9">{{ now()->format('d/m/Y') }}</small>
    </div>
</div>

<div class="sal-grid">
    <div>
        <div class="card">
            <div class="card__cab"><h2>Registrar salida temporal</h2></div>
            <div class="card__cuerpo">
                <form method="POST" action="{{ route('salidas.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($empleados->isNotEmpty())
                        <label class="sal-lbl">Empleado</label>
                        <select name="empleado_id" class="sal-ctrl" required>
                            <option value="">Seleccionar colaborador…</option>
                            @foreach($empleados as $e)
                                <option value="{{ $e->id }}">{{ $e->nombre_completo }} ({{ $e->codigo }})</option>
                            @endforeach
                        </select>
                    @endif
                    <label class="sal-lbl">Motivo de salida temporal</label>
                    <select name="motivo" class="sal-ctrl" required>
                        @foreach(\App\Models\Asistencias\SalidaTemporal::MOTIVOS as $motivo)
                            <option value="{{ $motivo }}">{{ $motivo }}</option>
                        @endforeach
                    </select>
                    <div class="sal-nota">La categoría <b>Necesidad personal.</b> se utiliza para situaciones como ir al baño, sin exponer detalles privados. La opción <b>Otro.</b> permite escribir una explicación personalizada.</div>
                    <label class="sal-lbl">Observación</label>
                    <textarea name="observacion" rows="2" class="sal-ctrl" placeholder="Escriba una breve explicación"></textarea>
                    <div class="sal-nota">Opcional para salidas breves.</div>
                    <label class="sal-lbl">Adjuntar sustento</label>
                    <input type="file" name="documento" class="sal-ctrl" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="sal-nota">Opcional. Formatos permitidos: PDF, JPG, JPEG y PNG. Útil para atención médica, emergencia familiar o falla técnica.</div>
                    <button type="submit" class="sal-azul">Registrar salida temporal</button>
                    <div class="sal-aviso">Al registrar la salida temporal, el monitoreo de presencia se pausará. Al retornar, deberá seleccionar <b>'Regresar al puesto'</b>.</div>
                </form>
            </div>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card__cab"><h2>Estado del monitoreo</h2></div>
            <div class="card__cuerpo">
                <div class="sal-tot"><span>Estado:</span><b>{{ $monitoreo['estado'] }}</b></div>
                <div class="sal-tot"><span>Trabajador detectado:</span><b>{{ $monitoreo['detectado'] }}</b></div>
                <div class="sal-tot"><span>Tiempo de tolerancia:</span><b>{{ $monitoreo['tolerancia'] }}</b></div>
                <div class="sal-tot"><span>Última verificación:</span><b>{{ $monitoreo['verificacion'] }}</b></div>
                <div class="sal-tot"><span>Zona monitoreada:</span><b>{{ $monitoreo['zona'] }}</b></div>
                <div class="sal-nota">Sin cámaras en baños ni zonas privadas. Sin grabación de audio ni sanciones automáticas.</div>
            </div>
        </div>
        <div class="card">
            <div class="card__cab"><h2>Salida temporal activa</h2></div>
            <div class="card__cuerpo">
                @if($activa)
                    <div class="sal-tot"><span>Hora de salida:</span><b>{{ $activa->hora_salida->format('h:i a.') }}</b></div>
                    <div class="sal-tot"><span>Motivo:</span><b>{{ $activa->motivo }}</b></div>
                    <div class="sal-tot"><span>Estado:</span><b>{{ $activa->estado }}.</b></div>
                    <div class="sal-tot"><span>Tiempo transcurrido:</span><b>{{ $activa->hora_salida->diff(now())->format('%H:%I') }}.</b></div>
                    <form method="POST" action="{{ route('salidas.regresar', $activa) }}">
                        @csrf
                        <button type="submit" class="sal-verde">✓ Regresar al puesto</button>
                    </form>
                @else
                    <div class="sal-nota">No hay una salida temporal activa. Cuando registre una, aquí se mostrará su seguimiento con el botón <b>'Regresar al puesto'</b>.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top:14px">
    <div class="card__cab"><h2>Historial de salidas temporales</h2></div>
    <div class="card__cuerpo" style="padding-top:6px">
        <table>
            <tr><th>Fecha</th><th>Empleado</th><th>Motivo</th><th>Salida</th><th>Retorno</th><th>Tolerancia</th><th>Sustento</th><th>Estado</th></tr>
            @forelse($salidas as $s)
                <tr>
                    <td>{{ $s->hora_salida->format('d/m/Y') }}</td>
                    <td>{{ $s->empleado->nombre_completo ?? '—' }}</td>
                    <td>{{ $s->motivo }}</td>
                    <td>{{ $s->hora_salida->format('h:i a.') }}</td>
                    <td>{{ $s->hora_retorno ? $s->hora_retorno->format('h:i a.') : '—' }}</td>
                    <td>{{ $s->tolerancia_min }} min</td>
                    <td>@if($s->documento)<a href="{{ route('salidas.adjunto', $s) }}">Ver archivo</a>@else — @endif</td>
                    <td><span class="sal-bd" style="background:{{ $s->hora_retorno ? '#e2e8f0;color:#334155' : '#fef3c7;color:#92400e' }}">{{ $s->hora_retorno ? 'Completada' : $s->estado }}</span></td>
                </tr>
            @empty
                <tr><td colspan="8" style="color:#8b98a9">Sin salidas temporales registradas.</td></tr>
            @endforelse
        </table>
    </div>
</div>
@endsection
