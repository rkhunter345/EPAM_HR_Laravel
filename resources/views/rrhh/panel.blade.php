@extends('layouts.app')
@section('titulo', 'Dashboard de Recursos Humanos')
@section('seccion', 'Recursos Humanos')
@section('migas', 'Dashboard')

@section('contenido')
@php $max = max(1, collect($serie)->max(fn ($s) => max($s['asistio'], $s['tardanza'], $s['ausente']))); @endphp
<div class="v-cab">
    <div>
        <h1>Dashboard de Recursos Humanos</h1>
        <p>Estado general de personal, asistencia, casos pendientes, monitoreo IoT y nómina del periodo vigente.</p>
    </div>
    <div class="v-cab__acc">
        <a class="btn" href="{{ route('rrhh.reportes') }}"><x-ui.icono name="reporte" :size="14" /> Reportes</a>
        <a class="btn btn--pri" href="{{ route('rrhh.personal') }}"><x-ui.icono name="empleados" :size="14" /> Personal</a>
    </div>
</div>

<div class="v-kpis v-kpis--6">
    <x-ui.kpi label="Personal total" :valor="$totalPersonal" icono="empleados" tono="azul" sub="{{ $activos }} activos" />
    <x-ui.kpi label="Asistencia hoy" :valor="$asistenciaHoy" icono="check" tono="verde" sub="Tasa mensual {{ $tasaAsistencia }}%" />
    <x-ui.kpi label="Tardanzas hoy" :valor="$tardanzasHoy" icono="reloj" tono="ambar" sub="Fuera de tolerancia" />
    <x-ui.kpi label="Ausentes hoy" :valor="$ausentesHoy" icono="ausencia" tono="rojo" sub="Sin marcación" />
    <x-ui.kpi label="Casos pendientes" :valor="$casosPendientes" icono="solicitud" tono="violeta" sub="{{ $justificacionesPend }} justificaciones" />
    <x-ui.kpi label="Eventos IoT hoy" :valor="$alertasIot" icono="camara" tono="cian" sub="A revisión, no sanción" />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Asistencia de los últimos 6 meses" icono="actividad" nota="Asistió, tardanza y ausencia registradas">
            <div class="v-barras">
                @foreach($serie as $s)
                    <div class="v-barras__col">
                        <div class="v-barras__b" style="height:{{ max(6, (int) round($s['asistio'] / $max * 100)) }}%" title="Asistió: {{ $s['asistio'] }}"></div>
                        <div class="v-barras__b tono-ambar" style="height:{{ max(4, (int) round($s['tardanza'] / $max * 100)) }}%" title="Tardanza: {{ $s['tardanza'] }}"></div>
                        <div class="v-barras__b tono-rojo" style="height:{{ max(3, (int) round($s['ausente'] / $max * 100)) }}%" title="Ausente: {{ $s['ausente'] }}"></div>
                        <span class="v-mini">{{ $s['etiqueta'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="v-leyenda"><span><i class="tono-verde"></i> Asistió</span><span><i class="tono-ambar"></i> Tardanza</span><span><i class="tono-rojo"></i> Ausente</span></div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Personal por departamento" icono="organigrama" nota="Colaboradores activos por área">
            @php $maxDep = max(1, (int) $porDepartamento->max('empleados_count')); @endphp
            @foreach($porDepartamento as $d)
                <div class="v-fila-barra">
                    <span class="v-fila-barra__nom">{{ $d->nombre }}</span>
                    <span class="v-fila-barra__pista"><i style="width:{{ (int) round($d->empleados_count / $maxDep * 100) }}%"></i></span>
                    <span class="v-fila-barra__val">{{ $d->empleados_count }}</span>
                </div>
            @endforeach
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Nómina del periodo" icono="dinero">
            @if($periodo)
                <div class="v-dato"><span>Periodo</span><strong>{{ $periodo->periodo }}</strong></div>
                <div class="v-dato"><span>Estado</span><strong><x-ui.badge :valor="$periodo->estado" /></strong></div>
                <div class="v-dato"><span>Ingresos</span><strong>S/ {{ number_format((float) $periodo->total_ingresos, 2) }}</strong></div>
                <div class="v-dato"><span>Descuentos</span><strong>S/ {{ number_format((float) $periodo->total_descuentos, 2) }}</strong></div>
                <div class="v-dato"><span>Neto a pagar</span><strong>S/ {{ number_format((float) $periodo->total_neto, 2) }}</strong></div>
            @else
                <div class="v-estado v-estado--neutro"><x-ui.icono name="dinero" :size="16" /><div>Sin periodos de nómina registrados.</div></div>
            @endif
            <a class="btn btn--bloque v-mt" href="{{ route('rrhh.nomina') }}"><x-ui.icono name="planilla" :size="14" /> Ir a nómina</a>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Casos que requieren decisión" icono="alerta" nota="Solo un responsable autorizado puede resolver">
            <div class="v-lista">
                @forelse($emergencias as $e)
                    <div class="v-lista__item">
                        <span class="v-lista__ico tono-rojo"><x-ui.icono name="medico" :size="14" /></span>
                        <div class="v-lista__txt"><strong>{{ $e->empleado->nombre_completo ?? '—' }}</strong><small>{{ $e->motivo }} · {{ $e->fecha_hora?->format('d/m H:i') }}</small></div>
                        <x-ui.badge :valor="$e->estado" />
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin emergencias abiertas</strong><small>No hay reportes pendientes de revisión.</small></div></div>
                @endforelse
            </div>
            <div class="v-dato v-mt"><span>Justificaciones pendientes</span><strong>{{ $justificacionesPend }}</strong></div>
            <div class="v-dato"><span>Eventos IoT por revisar</span><strong>{{ $alertasIot }}</strong></div>
            <a class="btn btn--bloque v-mt" href="{{ route('rrhh.justificaciones') }}"><x-ui.icono name="solicitud" :size="14" /> Revisar casos</a>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Vacaciones próximas" icono="ausencia" nota="Aprobadas para los próximos 30 días" sin-relleno="1">
            <div class="v-lista">
                @forelse($vacacionesProximas as $v)
                    <div class="v-lista__item">
                        <span class="v-avatar v-avatar--verde">{{ $v->empleado?->iniciales() }}</span>
                        <div class="v-lista__txt"><strong>{{ $v->empleado->nombre_completo ?? '—' }}</strong><small>{{ $v->desde?->format('d/m') }} al {{ $v->hasta?->format('d/m') }} · {{ (float) $v->dias }} días</small></div>
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin vacaciones programadas</strong><small>No hay ausencias aprobadas en los próximos 30 días.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Últimos eventos auditados" icono="llave" nota="Trazabilidad de acciones sensibles" sin-relleno="1">
            <div class="v-lista">
                @forelse($ultimos as $a)
                    <div class="v-lista__item">
                        <span class="v-lista__ico tono-gris"><x-ui.icono name="llave" :size="14" /></span>
                        <div class="v-lista__txt"><strong>{{ $a->accion ?? 'Acción' }}</strong><small>{{ \Illuminate\Support\Str::limit($a->detalle ?? '', 56) }} · {{ $a->usuario ?? '' }}</small></div>
                        <span class="v-lista__hora">{{ $a->created_at?->format('d/m H:i') }}</span>
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin registros</strong><small>La auditoría se llenará con las acciones del sistema.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
