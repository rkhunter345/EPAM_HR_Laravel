@extends('layouts.app')
@section('titulo', 'Integraciones')
@section('seccion', 'Administración')
@section('migas', 'Integraciones')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Integraciones</h1>
        <p>Servicios conectados al sistema. Las credenciales se almacenan protegidas y no se muestran en la interfaz.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Conectadas" :valor="$conectadas" icono="nube" tono="verde" sub="Operativas" />
    <x-ui.kpi label="Con incidencia" :valor="$caidas" icono="alerta" tono="rojo" sub="Requieren revisión" />
    <x-ui.kpi label="Activas" :valor="$activas" icono="actividad" tono="azul" sub="Habilitadas" />
    <x-ui.kpi label="Total" :valor="$integraciones->count()" icono="servidor" tono="gris" sub="Registradas" />
</div>

<x-ui.tarjeta titulo="Servicios" icono="nube" nota="La prueba de conexión no expone tokens ni claves" sin-relleno="1">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Servicio</th><th>Tipo</th><th>URL base</th><th>Estado</th><th>Última sincronización</th><th>Acción</th></tr></thead>
        <tbody>
        @forelse($integraciones as $i)
            <tr>
                <td><strong>{{ $i->nombre }}</strong><br><small class="v-mini">{{ $i->detalle }}</small></td>
                <td class="v-mini">{{ $i->tipo }}</td>
                <td class="v-mono v-mini">{{ $i->url ?: '—' }}</td>
                <td><x-ui.badge :valor="$i->estado" /></td>
                <td class="v-mini">{{ $i->ultima_sincronizacion?->format('d/m/Y H:i') ?? '—' }}</td>
                <td>
                    <form method="POST" action="{{ route('sistema.integraciones.probar', $i) }}">@csrf
                        <button class="btn btn--sm"><x-ui.icono name="rayo" :size="12" /> Probar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="vacio">Sin integraciones registradas.</td></tr>
        @endforelse
        </tbody></table></div>
</x-ui.tarjeta>

<div class="v-grid v-grid--2 v-mt">
    <x-ui.tarjeta titulo="Buenas prácticas" icono="escudo">
        <div class="v-dato"><span>Credenciales</span><strong>Almacenadas protegidas, nunca visibles</strong></div>
        <div class="v-dato"><span>Acceso técnico excepcional</span><strong>Con procedimiento y auditoría</strong></div>
        <div class="v-dato"><span>Rotación de tokens</span><strong>Programada por el administrador</strong></div>
    </x-ui.tarjeta>
    <x-ui.tarjeta titulo="Sobre el módulo IoT" icono="camara">
        <div class="v-estado v-estado--info"><x-ui.icono name="camara" :size="16" /><div><strong>Estaciones autenticadas por token</strong>El servicio IoT envía eventos a <code>/api/iot/evento</code> con la cabecera de token; la web no expone la clave.</div></div>
        <p class="v-nota v-mt">Los eventos recibidos se registran como indicios sujetos a revisión de RR. HH.</p>
    </x-ui.tarjeta>
</div>
@endsection
