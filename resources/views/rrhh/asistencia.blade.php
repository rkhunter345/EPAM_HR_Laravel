@extends('layouts.app')
@section('titulo', 'Asistencia general')
@section('seccion', 'Recursos Humanos')
@section('migas', 'Asistencia general')

@section('contenido')
@php $h = fn ($t) => $t ? substr($t, 0, 5) : '—:—'; @endphp
<div class="v-cab">
    <div>
        <h1>Asistencia general</h1>
        <p>Marcaciones de todo el personal. Toda corrección exige motivo obligatorio y conserva el valor anterior en la auditoría.</p>
    </div>
</div>

<form method="GET" class="v-card v-mb" style="padding:12px 16px">
    <div class="v-barra-filtros">
        <div class="campo" style="max-width:180px"><label>Fecha</label><input type="date" name="desde" value="{{ $desde }}"></div>
        <button class="btn btn--pri btn--sm"><x-ui.icono name="filtrar" :size="13" /> Consultar</button>
        <span class="v-mini" style="margin-left:auto">{{ $filas->count() }} registros · {{ $salidas->count() }} salidas temporales</span>
    </div>
</form>

<div class="v-kpis">
    <x-ui.kpi label="Presentes" :valor="$presentes" icono="check" tono="verde" sub="Asistió, tardanza o justificado" />
    <x-ui.kpi label="Tardanzas" :valor="$tardanzas" icono="reloj" tono="ambar" sub="Fuera de tolerancia" />
    <x-ui.kpi label="Ausentes" :valor="$ausentes" icono="ausencia" tono="rojo" sub="Sin marcación completa" />
    <x-ui.kpi label="Incompletos" :valor="$incompletos" icono="alerta" tono="violeta" sub="Falta salida o refrigerio" />
</div>

<x-ui.tarjeta titulo="Marcaciones del día" icono="reloj" nota="Corrección con motivo y trazabilidad" sin-relleno="1">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Trabajador</th><th>Área</th><th>Entrada</th><th>Refrigerio</th><th>Salida</th><th>Horas</th><th>Estado</th><th>Corrección</th></tr></thead>
        <tbody>
        @forelse($filas as $f)
            <tr>
                <td><strong>{{ $f->empleado->nombre_completo ?? '—' }}</strong><br><small class="v-mini">{{ $f->empleado->codigo ?? '' }}</small></td>
                <td class="v-mini">{{ $f->empleado->departamento->nombre ?? '—' }}</td>
                <td>{{ $h($f->hora_entrada) }}</td>
                <td>{{ $h($f->hora_inicio_refrigerio) }}–{{ $h($f->hora_fin_refrigerio) }}</td>
                <td>{{ $h($f->hora_salida) }}</td>
                <td>{{ number_format($f->minutos_trabajados / 60, 1) }} h</td>
                <td><x-ui.badge :valor="$f->estado" /></td>
                <td>
                    <form method="POST" action="{{ route('rrhh.asistencia.corregir', $f) }}" style="display:flex;gap:4px;align-items:center;flex-wrap:wrap">
                        @csrf
                        <input type="time" name="hora_entrada" value="{{ $f->hora_entrada ? substr($f->hora_entrada,0,5) : '' }}" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px">
                        <input type="time" name="hora_salida" value="{{ $f->hora_salida ? substr($f->hora_salida,0,5) : '' }}" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px">
                        <input type="text" name="motivo" placeholder="Motivo *" required style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px;width:120px">
                        <button class="btn btn--sm"><x-ui.icono name="lapiz" :size="12" /></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="vacio">No hay marcaciones para la fecha consultada.</td></tr>
        @endforelse
        </tbody></table></div>
</x-ui.tarjeta>

<div class="v-grid v-grid--2 v-mt">
    <x-ui.tarjeta titulo="Salidas temporales del día" icono="pausa" sin-relleno="1">
        <div class="v-lista">
            @forelse($salidas as $s)
                <div class="v-lista__item">
                    <span class="v-lista__ico tono-ambar"><x-ui.icono name="pausa" :size="14" /></span>
                    <div class="v-lista__txt"><strong>{{ $s->empleado->nombre_completo ?? '—' }}</strong><small>{{ $s->motivo }} · {{ $s->hora_salida?->format('H:i') }} → {{ $s->hora_retorno?->format('H:i') ?? 'pendiente' }}</small></div>
                    <x-ui.badge :valor="$s->estado" />
                </div>
            @empty
                <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin salidas</strong><small>No se registraron pausas del monitoreo.</small></div></div>
            @endforelse
        </div>
    </x-ui.tarjeta>
    <x-ui.tarjeta titulo="Regla de corrección" icono="escudo">
        <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Motivo obligatorio</strong>Sin motivo el sistema rechaza la corrección. El valor anterior queda guardado en la auditoría.</div></div>
        <div class="v-dato v-mt"><span>Se registra</span><strong>Usuario, rol, fecha, valor anterior y nuevo</strong></div>
        <div class="v-dato"><span>Casos propios</span><strong>Debe resolverlos otro responsable</strong></div>
    </x-ui.tarjeta>
</div>
@endsection
