@extends('layouts.app')
@section('titulo', 'Mis justificaciones')
@section('seccion', 'Mi espacio')
@section('migas', 'Justificaciones')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Justificaciones</h1>
        <p>Presenta el sustento de una tardanza, ausencia o marcación observada. La aprobación corresponde a tu supervisor o a Recursos Humanos.</p>
    </div>
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Nueva justificación" icono="solicitud" nota="Adjunta el documento que sustente el caso (PDF, JPG o PNG hasta 4 MB)">
            <form method="POST" action="{{ route('espacio.justificaciones.guardar') }}" enctype="multipart/form-data">
                @csrf
                <div class="v-form-2">
                    <div class="campo"><label>Fecha del hecho *</label><input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required></div>
                    <div class="campo"><label>Horario afectado</label><input type="text" name="horario_afectado" value="{{ old('horario_afectado') }}" placeholder="08:00 - 09:00"></div>
                </div>
                <div class="campo"><label>Motivo *</label><input type="text" name="motivo" value="{{ old('motivo') }}" required placeholder="Ej.: cita médica, trámite personal"></div>
                <div class="campo"><label>Explicación *</label><textarea name="explicacion" rows="4" required placeholder="Detalle el sustento de la justificación">{{ old('explicacion') }}</textarea></div>
                <div class="campo"><label>Documento de sustento</label><input type="file" name="documento" accept=".pdf,.jpg,.jpeg,.png"></div>
                <button class="btn btn--pri"><x-ui.icono name="solicitud" :size="14" /> Enviar justificación</button>
                <p class="v-nota v-mt">El caso queda en estado Pendiente hasta la revisión. No puedes aprobar tus propios casos.</p>
            </form>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Mis justificaciones" icono="nota" nota="Historial de casos presentados" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Fecha</th><th>Motivo</th><th>Horario</th><th>Documento</th><th>Estado</th><th>Observación</th></tr></thead>
                <tbody>
                @forelse($justificaciones as $j)
                    <tr>
                        <td><strong>{{ $j->fecha?->format('d/m/Y') }}</strong></td>
                        <td>{{ $j->motivo }}</td>
                        <td>{{ $j->horario_afectado ?: '—' }}</td>
                        <td>@if($j->documento)<a class="v-mini" href="{{ route('espacio.justificaciones.adjunto', $j) }}">Ver adjunto</a>@else <span class="v-mini">—</span>@endif</td>
                        <td><x-ui.badge :valor="$j->estado" /></td>
                        <td class="v-mini">{{ $j->observacion ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="vacio">Todavía no presentaste justificaciones.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Marcaciones recientes" icono="reloj" nota="Puedes justificar cualquiera de estos registros" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Fecha</th><th>Entrada</th><th>Estado</th></tr></thead>
                <tbody>
                @foreach($asistencias as $a)
                    <tr>
                        <td>{{ $a->fecha->format('d/m/Y') }}</td>
                        <td>{{ substr((string) $a->hora_entrada, 0, 5) ?: '—' }}</td>
                        <td><x-ui.badge :valor="$a->estado" /></td>
                    </tr>
                @endforeach
                </tbody></table></div>
        </x-ui.tarjeta>
        <x-ui.tarjeta titulo="Flujo de revisión" icono="actividad">
            <div class="v-tl">
                <div class="v-tl__item is-hecho"><div class="v-tl__hora">1 · Presentación</div><div class="v-tl__txt">Registras el caso con su sustento.</div></div>
                <div class="v-tl__item is-activo"><div class="v-tl__hora">2 · Revisión</div><div class="v-tl__txt">Supervisor o RR. HH. evalúa el caso. No puede resolver el suyo propio.</div></div>
                <div class="v-tl__item is-pendiente"><div class="v-tl__hora">3 · Decisión</div><div class="v-tl__txt">Aprobada, rechazada o información solicitada, con observación.</div></div>
                <div class="v-tl__item is-pendiente"><div class="v-tl__hora">4 · Registro</div><div class="v-tl__txt">Se audita con usuario, fecha y valores anterior/nuevo.</div></div>
            </div>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
