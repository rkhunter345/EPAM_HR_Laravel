@extends('layouts.app')
@section('titulo', 'Vacaciones y permisos')
@section('seccion', 'Mi espacio')
@section('migas', 'Vacaciones y permisos')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Vacaciones y permisos</h1>
        <p>Solicita vacaciones o permisos por horas. El sistema calcula los días hábiles y envía la solicitud a tu supervisor o a RR. HH.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Días disponibles" :valor="$diasDisponibles" icono="ausencia" tono="verde" sub="Sobre 30 días anuales" />
    <x-ui.kpi label="Días usados" :valor="$diasUsados" icono="check" tono="azul" sub="Vacaciones aprobadas" />
    <x-ui.kpi label="Pendientes" :valor="$diasPendientes" icono="reloj" tono="ambar" sub="En espera de aprobación" />
    <x-ui.kpi label="Solicitudes" :valor="$vacaciones->count() + $permisos->count()" icono="solicitud" tono="violeta" sub="Registradas en total" />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Nueva solicitud" icono="solicitud" nota="Las vacaciones se cuentan en días hábiles; los permisos por horas">
            <form method="POST" action="{{ route('espacio.vacaciones.guardar') }}">
                @csrf
                <div class="v-form-2">
                    <div class="campo"><label>Tipo *</label>
                        <select name="tipo" required>
                            <option>Vacaciones</option><option>Permiso</option><option>Permiso por salud</option>
                        </select>
                    </div>
                    <div class="campo"><label>Motivo *</label><input type="text" name="motivo" value="{{ old('motivo') }}" required placeholder="Motivo de la solicitud"></div>
                    <div class="campo"><label>Desde *</label><input type="date" name="desde" value="{{ old('desde') }}" required></div>
                    <div class="campo"><label>Hasta *</label><input type="date" name="hasta" value="{{ old('hasta') }}" required></div>
                </div>
                <button class="btn btn--pri"><x-ui.icono name="solicitud" :size="14" /> Enviar solicitud</button>
                <p class="v-nota v-mt">No puedes aprobar tus propias solicitudes: otro responsable autorizado debe evaluarlas.</p>
            </form>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Mis vacaciones" icono="ausencia" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Desde</th><th>Hasta</th><th>Días</th><th>Tipo</th><th>Motivo</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($vacaciones as $v)
                    <tr>
                        <td>{{ $v->desde?->format('d/m/Y') }}</td>
                        <td>{{ $v->hasta?->format('d/m/Y') }}</td>
                        <td>{{ number_format((float) $v->dias, 1) }}</td>
                        <td>{{ $v->tipo }}</td>
                        <td class="v-mini">{{ $v->motivo }}</td>
                        <td><x-ui.badge :valor="$v->estado" /></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="vacio">No registraste solicitudes de vacaciones.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Mis permisos" icono="reloj" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Fecha</th><th>Horario</th><th>Motivo</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($permisos as $p)
                    <tr>
                        <td>{{ $p->fecha?->format('d/m/Y') }}</td>
                        <td>{{ substr((string) $p->hora_desde, 0, 5) }} – {{ substr((string) $p->hora_hasta, 0, 5) }}</td>
                        <td class="v-mini">{{ $p->motivo }}</td>
                        <td><x-ui.badge :valor="$p->estado" /></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="vacio">No registraste permisos.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Saldo de vacaciones" icono="calendario">
            <div class="v-donut" style="--p:{{ max(0, min(100, (int) round($diasDisponibles / 30 * 100))) }}">
                <div class="v-donut__c"><strong>{{ $diasDisponibles }}</strong><span>días libres</span></div>
            </div>
            <div class="v-dato"><span>Derecho anual</span><strong>30 días</strong></div>
            <div class="v-dato"><span>Aprobadas</span><strong>{{ $diasUsados }} días</strong></div>
            <div class="v-dato"><span>En revisión</span><strong>{{ $diasPendientes }} días</strong></div>
        </x-ui.tarjeta>
        <x-ui.tarjeta titulo="Reglas aplicadas" icono="escudo">
            <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Sin decisiones automáticas</strong>El sistema no aprueba ni rechaza solicitudes por sí mismo; solo las registra y calcula días.</div></div>
            <p class="v-nota v-mt">La aprobación siempre la realiza una persona autorizada, y queda auditada con fecha, responsable y observación.</p>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
