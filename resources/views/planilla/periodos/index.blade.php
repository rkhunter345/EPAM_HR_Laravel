@extends('layouts.app')
@section('titulo', 'Planilla de remuneraciones')
@section('migas', 'Nómina')
@section('contenido')
<div class="cab">
    <div><h1>Planilla de remuneraciones</h1><p>Cálculo mensual de ingresos, descuentos y neto a pagar</p></div>
    <div class="cab__acciones"><a href="{{ route('periodos.create') }}" class="btn btn--pri"><x-ui.icono name="mas" :size="15" /> Nuevo período</a></div>
</div>
<div class="card">
    <div class="tabla-wrap">
        <table class="tabla">
            <thead><tr><th>Período</th><th>Estado</th><th>Empleados</th><th>Ingresos</th><th>Descuentos</th><th>Neto</th><th>Cierre</th><th style="text-align:right">Acciones</th></tr></thead>
            <tbody>
            @forelse($periodos as $p)
                <tr>
                    <td><a href="{{ route('periodos.show', $p) }}"><strong>{{ $p->periodo }}</strong></a></td>
                    <td><x-ui.badge :valor="$p->estado" /></td>
                    <td>{{ $p->detalles_count }}</td>
                    <td>S/ {{ number_format((float) $p->total_ingresos, 2) }}</td>
                    <td>S/ {{ number_format((float) $p->total_descuentos, 2) }}</td>
                    <td><strong>S/ {{ number_format((float) $p->total_neto, 2) }}</strong></td>
                    <td>{{ optional($p->fecha_cierre)->format('d/m/Y') ?? '—' }}</td>
                    <td><div class="acciones">
                        <a class="btn btn--ico" href="{{ route('periodos.show', $p) }}" title="Ver boletas"><x-ui.icono name="ver" :size="15" /></a>
                        <a class="btn btn--ico" href="{{ route('periodos.edit', $p) }}" title="Editar"><x-ui.icono name="lapiz" :size="15" /></a>
                        <form method="POST" action="{{ route('periodos.destroy', $p) }}" onsubmit="return confirm('¿Eliminar el período y sus boletas?');">@csrf @method('DELETE')
                            <button class="btn btn--ico" title="Eliminar"><x-ui.icono name="basura" :size="15" /></button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="8" class="vacio">Aún no hay períodos de planilla. Cree el primero con «Nuevo período».</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card__pie"><small style="color:var(--texto-3)">{{ $periodos->total() }} períodos</small>{{ $periodos->links('components.crud.paginacion') }}</div>
</div>
@endsection
