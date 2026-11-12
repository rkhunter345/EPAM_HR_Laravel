@extends('layouts.app')
@section('titulo', 'Monitoreo IoT')
@section('migas', 'Configuración / IoT')
@section('contenido')
<div class="cab">
    <div><h1>Monitoreo IoT</h1><p>Estaciones de trabajo con cámara o lector QR. Los eventos llegan por API con token; las detecciones se entrenarán después.</p></div>
    <div class="cab__acciones">
        <a href="{{ route('iot.estacion.crear') }}" class="btn btn--pri"><x-ui.icono name="mas" :size="14" /> Registrar estación</a>
    </div>
</div>

<div class="card">
    <div class="card__cab">
        <div><h2>Cámara de verificación</h2><small>Prueba local de la cámara del puesto (la imagen no se graba ni se envía)</small></div>
        <button type="button" id="camara-btn" class="btn btn--pri"><x-ui.icono name="camara" :size="15" /> Encender cámara</button>
    </div>
    <div class="card__cuerpo" style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap">
        <video id="camara-video" width="360" height="270" autoplay playsinline muted
               style="border-radius:10px;border:1px solid var(--line);background:#0f172a;display:none"></video>
        <div id="camara-vacio" style="width:360px;height:270px;display:grid;place-items:center;text-align:center;color:var(--texto-3);border:1.5px dashed var(--line);border-radius:10px">
            <span>Cámara apagada.<br>Pulsa <strong>Encender cámara</strong> para verificar el encuadre del puesto.</span>
        </div>
        <div id="camara-estado" style="flex:1;min-width:200px">
            <p style="margin:0 0 8px;color:var(--texto-2)">Estado: <strong id="camara-texto">Apagada</strong></p>
            <ul style="margin:0;padding-left:18px;color:var(--texto-3);font-size:12px">
                <li>La cámara funciona en <strong>http://127.0.0.1</strong> (localhost). Para verla desde otro equipo de la red se requiere <strong>https</strong>.</li>
                <li>Si el navegador pide permiso, pulsa «Permitir». Si niega el permiso, se mostrará el error.</li>
            </ul>
        </div>
    </div>
</div>

<div class="kpis">
    <div class="kpi"><div class="kpi__cabeza"><span class="kpi__icono tono-azul"><x-ui.icono name="organigrama" :size="13" /></span><span class="kpi__label">Estaciones registradas</span></div><div class="kpi__valor">{{ $estaciones->count() }}</div><div class="kpi__sub">{{ $estaciones->where('activo', true)->count() }} activas</div></div>
    <div class="kpi"><div class="kpi__cabeza"><span class="kpi__icono tono-rojo"><x-ui.icono name="ausencia" :size="13" /></span><span class="kpi__label">Ausencias del puesto (hoy)</span></div><div class="kpi__valor">{{ $porTipo['ausencia_puesto'] ?? 0 }}</div><div class="kpi__sub">Detectadas por cámara</div></div>
    <div class="kpi"><div class="kpi__cabeza"><span class="kpi__icono tono-ambar"><x-ui.icono name="reloj" :size="13" /></span><span class="kpi__label">Uso de celular (hoy)</span></div><div class="kpi__valor">{{ $porTipo['uso_celular'] ?? 0 }}</div><div class="kpi__sub">Durante la jornada</div></div>
    <div class="kpi"><div class="kpi__cabeza"><span class="kpi__icono tono-verde"><x-ui.icono name="check" :size="13" /></span><span class="kpi__label">Marcaciones QR (hoy)</span></div><div class="kpi__valor">{{ $porTipo['marcacion_qr'] ?? 0 }}</div><div class="kpi__sub">Vía lector de estación</div></div>
</div>

<div class="card">
    <div class="card__cab"><div><h2>Estaciones</h2><small>Cada estación envía su último ping al sistema</small></div></div>
    <div class="tabla-wrap"><table class="tabla">
        <thead><tr><th>Nombre</th><th>Tipo</th><th>Ubicación</th><th>Empleado asignado</th><th>Eventos</th><th>Último ping</th><th>Estado</th></tr></thead>
        <tbody>
        @forelse($estaciones as $estacion)
            <tr>
                <td><strong>{{ $estacion->nombre }}</strong></td>
                <td><x-ui.badge :valor="$estacion->tipo === 'camara' ? 'Cámara' : 'QR'" /></td>
                <td>{{ $estacion->ubicacion ?? '—' }}</td>
                <td>{{ $estacion->empleado->nombre_completo ?? '—' }}</td>
                <td>{{ $estacion->eventos_count }}</td>
                <td>{{ $estacion->ultimo_ping?->format('d/m H:i') ?? 'sin pings' }}</td>
                <td><x-ui.badge :valor="$estacion->activo ? 'Activo' : 'Inactivo'" /></td>
            </tr>
        @empty
            <tr><td colspan="7" class="vacio">Sin estaciones. Registre la primera con «Registrar estación».</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

<div class="card">
    <div class="card__cab"><div><h2>Últimos eventos</h2><small>Recibidos por POST /api/iot/evento (header X-IOT-TOKEN)</small></div></div>
    <div class="tabla-wrap"><table class="tabla">
        <thead><tr><th>Fecha y hora</th><th>Estación</th><th>Empleado</th><th>Evento</th><th>Duración</th><th>Estado</th><th style="text-align:right">Acciones</th></tr></thead>
        <tbody>
        @forelse($eventos as $evento)
            <tr>
                <td>{{ $evento->detectado_en->format('d/m/Y H:i') }}</td>
                <td>{{ $evento->estacion->nombre }}</td>
                <td>{{ $evento->empleado->nombre_completo ?? '—' }}</td>
                <td>{{ ['ausencia_puesto' => 'Ausencia del puesto', 'uso_celular' => 'Uso de celular', 'regreso' => 'Regreso al puesto', 'marcacion_qr' => 'Marcación QR'][$evento->tipo] ?? $evento->tipo }}</td>
                <td>{{ $evento->duracion_min ? $evento->duracion_min.' min' : '—' }}</td>
                <td><x-ui.badge :valor="$evento->estado" /></td>
                <td>
                    <div class="acciones">
                        @if($evento->estado === 'Registrado')
                            <form method="POST" action="{{ route('iot.revisar', $evento) }}">
                                @csrf
                                <input type="hidden" name="estado" value="Revisado">
                                <button class="btn btn--sm">Revisado</button>
                            </form>
                            <form method="POST" action="{{ route('iot.revisar', $evento) }}">
                                @csrf
                                <input type="hidden" name="estado" value="Descartado">
                                <button class="btn btn--sm btn--peligro">Descartar</button>
                            </form>
                        @else
                            <small style="color:var(--texto-3)">—</small>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="vacio">Sin eventos recibidos todavía.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

<script>
(function () {
    var btn = document.getElementById('camara-btn');
    var video = document.getElementById('camara-video');
    var vacio = document.getElementById('camara-vacio');
    var texto = document.getElementById('camara-texto');
    var stream = null;

    function detener() {
        if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
        video.srcObject = null;
        video.style.display = 'none';
        vacio.style.display = 'grid';
        texto.textContent = 'Apagada';
        btn.innerHTML = btn.dataset.encender;
    }

    btn.dataset.encender = btn.innerHTML;

    btn.addEventListener('click', function () {
        if (stream) { detener(); return; }
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            texto.textContent = 'No disponible (requiere HTTPS o localhost)';
            return;
        }
        navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720 }, audio: false })
            .then(function (s) {
                stream = s;
                video.srcObject = s;
                video.style.display = 'block';
                vacio.style.display = 'none';
                texto.textContent = 'Encendida (' + s.getVideoTracks()[0].label + ')';
                btn.innerHTML = '⏹ Apagar cámara';
            })
            .catch(function (err) {
                var msg = {
                    NotAllowedError: 'Permiso denegado: permite el acceso a la cámara en el candado de la barra de direcciones.',
                    NotFoundError: 'No se encontró ninguna cámara conectada.',
                    NotReadableError: 'La cámara está siendo usada por otra aplicación.',
                }[err.name] || ('Error de cámara: ' + err.name);
                texto.textContent = msg;
            });
    });

    window.addEventListener('pagehide', detener);
})();
</script>
@endsection
