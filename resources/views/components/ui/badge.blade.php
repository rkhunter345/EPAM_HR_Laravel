@php
    $mapa = [
        'Activo'=>'ok','Activa'=>'ok','Asistió'=>'ok','Aprobada'=>'ok','Aprobado'=>'ok','Pagado'=>'ok',
        'Completada'=>'ok','Conectado'=>'ok','Disponible'=>'ok','Revisado'=>'ok','Alto'=>'ok','Operativo'=>'ok',
        'Boletas generadas'=>'ok','Publicada'=>'ok','Hecho'=>'ok','Cumplido'=>'ok',
        'Tardanza'=>'warn','En proceso'=>'warn','Pendiente'=>'warn','Revisión'=>'warn','En revisión'=>'warn',
        'Información solicitada'=>'warn','Incompleto'=>'warn','Advertencia'=>'warn','Medio'=>'warn','Por vencer'=>'warn',
        'Inactivo'=>'bad','Inactiva'=>'bad','Ausente'=>'bad','Rechazada'=>'bad','Rechazado'=>'bad','Suspendido'=>'bad',
        'Desconectado'=>'bad','Crítico'=>'bad','Desactivada'=>'bad','Descartado'=>'neutro','Baja'=>'neutro',
        'Borrador'=>'neutro','No leído'=>'info','Registrado'=>'info','Justificado'=>'info','Informativa'=>'info',
        'Información'=>'info','Derivada'=>'violeta','Monitoreo pausado'=>'violeta','Vacaciones'=>'violeta',
    ];
    $tono = $mapa[$valor ?? ''] ?? 'info';
@endphp
<span class="badge badge--{{ $tono }}">{{ $valor }}</span>
