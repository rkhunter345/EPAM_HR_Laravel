@extends('layouts.app')
@section('titulo', $empleado->nombre_completo)
@section('migas', 'Empleados')
@section('contenido')
<div class="cab">
    <div class="perfil">
        <span class="avatar avatar--lg avatar--foto">{{ $empleado->iniciales() }}</span>
        <div>
            <h2>{{ $empleado->nombre_completo }}</h2>
            <small>{{ $empleado->codigo }} · {{ $empleado->designacion->nombre ?? 'sin designación' }} · {{ $empleado->departamento->nombre ?? 'sin departamento' }}</small>
        </div>
    </div>
    <div class="cab__acciones">
        <x-ui.badge :valor="$empleado->estado" />
        <a href="{{ route('empleados.index') }}" class="btn">Volver</a>
        <a href="{{ route('empleados.edit', $empleado) }}" class="btn btn--pri"><x-ui.icono name="lapiz" :size="14" /> Editar</a>
    </div>
</div>

<div class="grid grid--4" style="margin-bottom:14px">
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-verde"><x-ui.icono name="check" :size="13" /></span><span class="kpi__label">Asistencias</span></div><div class="kpi__valor">{{ $resumen['asistio'] }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-ambar"><x-ui.icono name="reloj" :size="13" /></span><span class="kpi__label">Tardanzas</span></div><div class="kpi__valor">{{ $resumen['tardanza'] }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-rojo"><x-ui.icono name="ausencia" :size="13" /></span><span class="kpi__label">Ausencias</span></div><div class="kpi__valor">{{ $resumen['ausente'] }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-azul"><x-ui.icono name="planilla" :size="13" /></span><span class="kpi__label">Salario mensual</span></div><div class="kpi__valor">S/ {{ number_format((float) $empleado->salario_mensual, 0) }}</div></div>
</div>

<div class="grid grid--2">
    <div class="card" style="margin-top:0">
        <div class="card__cab"><h2>Información del empleado</h2></div>
        <div class="card__cuerpo">
            <dl class="detalle">
                <div class="detalle__item"><dt>Identificador</dt><dd>{{ $empleado->codigo }}</dd></div>
                <div class="detalle__item"><dt>Estado</dt><dd><x-ui.badge :valor="$empleado->estado" /></dd></div>
                <div class="detalle__item"><dt>Compañía</dt><dd>{{ $empleado->compania->nombre ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Sucursal</dt><dd>{{ $empleado->sucursal->nombre ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Departamento</dt><dd>{{ $empleado->departamento->nombre ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Designación</dt><dd>{{ $empleado->designacion->nombre ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Grado</dt><dd>{{ $empleado->grado->nombre ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Grupo</dt><dd>{{ $empleado->grupo->nombre ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Tipo de empleo</dt><dd>{{ $empleado->tipoEmpleo->nombre ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Tipo de contrato</dt><dd>{{ $empleado->tipoContrato->nombre ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Jornada</dt><dd>{{ $empleado->jornada_laboral }} · {{ $empleado->horas_semanales }} h semanales</dd></div>
                <div class="detalle__item"><dt>Calendario</dt><dd>{{ $empleado->calendario->nombre ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Fecha de ingreso</dt><dd>{{ optional($empleado->fecha_ingreso)->format('d/m/Y') ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Documento</dt><dd>{{ $empleado->tipo_documento }} {{ $empleado->numero_documento }}</dd></div>
                <div class="detalle__item"><dt>Teléfono</dt><dd>{{ $empleado->telefono ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Correo</dt><dd>{{ $empleado->correo_personal ?? '—' }}</dd></div>
                <div class="detalle__item detalle__item--full"><dt>Dirección</dt><dd>{{ $empleado->direccion ?? '—' }} {{ $empleado->distrito ? '('.$empleado->distrito.')' : '' }}</dd></div>
                <div class="detalle__item"><dt>Contacto de emergencia</dt><dd>{{ $empleado->contacto_emergencia ?? '—' }}</dd></div>
                <div class="detalle__item"><dt>Teléfono de emergencia</dt><dd>{{ $empleado->telefono_emergencia ?? '—' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="card" style="margin-top:0">
        <div class="card__cab"><h2>Últimas marcaciones</h2><a href="{{ route('asistencias.index', ['empleado_id' => $empleado->id]) }}" class="btn btn--sm">Ver todas</a></div>
        <div class="tabla-wrap">
            <table class="tabla">
                <thead><tr><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($asistencias as $a)
                    <tr>
                        <td>{{ $a->fecha->format('d/m/Y') }}</td>
                        <td>{{ $a->hora_entrada ?? '—' }}</td>
                        <td>{{ $a->hora_salida ?? '—' }}</td>
                        <td><x-ui.badge :valor="$a->estado" /></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="vacio">Sin marcaciones registradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid grid--2" style="margin-top:14px">
    <div class="card" style="margin-top:0">
        <div class="card__cab"><h2>Vacaciones y permisos</h2></div>
        <div class="tabla-wrap">
            <table class="tabla">
                <thead><tr><th>Tipo</th><th>Desde</th><th>Hasta</th><th>Días</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($vacaciones as $v)
                    <tr>
                        <td>{{ $v->tipo }}</td>
                        <td>{{ $v->desde->format('d/m/Y') }}</td>
                        <td>{{ $v->hasta->format('d/m/Y') }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) $v->dias, 1), '0'), '.') }}</td>
                        <td><x-ui.badge :valor="$v->estado" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="vacio">Sin solicitudes registradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top:0">
        <div class="card__cab"><h2>Boletas de planilla</h2></div>
        <div class="tabla-wrap">
            <table class="tabla">
                <thead><tr><th>Período</th><th>Ingresos</th><th>Descuentos</th><th>Neto</th></tr></thead>
                <tbody>
                @forelse($planillas as $p)
                    <tr>
                        <td><strong>{{ $p->periodo->periodo ?? '—' }}</strong></td>
                        <td>S/ {{ number_format((float) $p->total_ingresos, 2) }}</td>
                        <td>S/ {{ number_format((float) $p->total_descuentos, 2) }}</td>
                        <td><strong>S/ {{ number_format((float) $p->neto, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="vacio">Sin boletas generadas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
