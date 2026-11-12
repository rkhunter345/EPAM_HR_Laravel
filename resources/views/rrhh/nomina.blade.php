@extends('layouts.app')
@section('titulo', 'Nómina')
@section('seccion', 'Recursos Humanos')
@section('migas', 'Nómina')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Nómina</h1>
        <p>Periodos, cálculos y boletas. El acceso a remuneraciones exige permiso específico y toda consulta queda auditada.</p>
    </div>
    <div class="v-cab__acc">
        @if($periodo)
            <a class="btn" href="{{ route('rrhh.planilla', $periodo) }}"><x-ui.icono name="descargar" :size="14" /> Planilla PDF</a>
        @endif
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Periodos" :valor="$periodos->count()" icono="planilla" tono="azul" sub="Registrados" />
    <x-ui.kpi label="Periodo actual" :valor="$periodo->periodo ?? '—'" icono="calendario" tono="violeta" sub="{{ $periodo->estado ?? 'sin periodo' }}" />
    <x-ui.kpi label="Neto total" :valor="'S/ '.number_format((float) ($periodo->total_neto ?? 0), 2)" icono="dinero" tono="verde" sub="Del periodo seleccionado" />
    <x-ui.kpi label="Boletas generadas" :valor="$detalles->count()" icono="descargar" tono="ambar" sub="Detalles de planilla" />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Periodos de planilla" icono="calendario" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Periodo</th><th>Mes</th><th>Estado</th><th>Ingresos</th><th>Descuentos</th><th>Neto</th><th>Pago</th></tr></thead>
                <tbody>
                @forelse($periodos as $p)
                    <tr>
                        <td><strong>{{ $p->periodo }}</strong></td>
                        <td class="v-mini">{{ $p->mes }}/{{ $p->anio }}</td>
                        <td><x-ui.badge :valor="$p->estado" /></td>
                        <td>S/ {{ number_format((float) $p->total_ingresos, 2) }}</td>
                        <td>S/ {{ number_format((float) $p->total_descuentos, 2) }}</td>
                        <td><strong>S/ {{ number_format((float) $p->total_neto, 2) }}</strong></td>
                        <td class="v-mini">{{ $p->fecha_pago?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="vacio">Aún no hay periodos de planilla.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Detalle del periodo {{ $periodo->periodo ?? '' }}" icono="dinero" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Trabajador</th><th>Básico</th><th>Ingresos</th><th>Descuentos</th><th>Neto</th><th>Boleta</th></tr></thead>
                <tbody>
                @forelse($detalles as $d)
                    <tr>
                        <td><strong>{{ $d->empleado->nombre_completo ?? '—' }}</strong><br><small class="v-mini">{{ $d->empleado->departamento->nombre ?? '' }}</small></td>
                        <td>S/ {{ number_format((float) $d->basico, 2) }}</td>
                        <td>S/ {{ number_format((float) $d->total_ingresos, 2) }}</td>
                        <td>S/ {{ number_format((float) $d->total_descuentos, 2) }}</td>
                        <td><strong>S/ {{ number_format((float) $d->neto, 2) }}</strong></td>
                        <td>@if($periodo)<a class="btn btn--sm" href="{{ route('rrhh.boleta.pdf', [$periodo->id, $d->id]) }}"><x-ui.icono name="descargar" :size="12" /> PDF</a>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="vacio">El periodo no tiene detalles de planilla.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Etapas de la nómina" icono="actividad">
            <div class="v-stepper" style="flex-direction:column;align-items:flex-start;gap:6px">
                @foreach(\App\Models\Nomina\PeriodoPlanilla::ESTADOS as $i => $etapa)
                    <span class="v-stepper__i {{ ($periodo->estado ?? '') === $etapa ? 'is-activo' : '' }}"><i>{{ $i + 1 }}</i> {{ $etapa }}</span>
                @endforeach
            </div>
            <p class="v-nota v-mt">Las etapas son configurables. Ninguna boleta se publica sin cierre del periodo.</p>
        </x-ui.tarjeta>
        <x-ui.tarjeta titulo="Reglas de acceso" icono="escudo">
            <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Salarios protegidos</strong>Solo RR. HH. con permiso de nómina consulta remuneraciones; el supervisor y el administrador técnico no las ven.</div></div>
            <div class="v-dato v-mt"><span>Descarga de boletas</span><strong>Auditada por trabajador</strong></div>
            <div class="v-dato"><span>Boletas propias</span><strong>Consulta individual</strong></div>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
