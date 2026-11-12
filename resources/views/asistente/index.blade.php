@extends('layouts.app')
@section('titulo', 'Asistente RR.HH.')
@section('migas', 'Configuración')
@section('contenido')
<div class="cab">
    <div><h1>Asistente de Recursos Humanos</h1><p>Consulta por empleado, código o área. Responde sólo con datos registrados en el sistema.</p></div>
    <div class="cab__acciones">
        <span class="badge badge--{{ ($ollamaOk ?? false) ? 'ok' : 'warn' }}">
            {{ ($ollamaOk ?? false) ? 'Ollama conectado · '.$modelo : 'Ollama no conectado · motor local' }}
        </span>
    </div>
</div>

<div class="grid grid--lado">
    <div class="card" style="margin-top:0">
        <div class="card__cab"><div><h2>Conversación</h2><small>No aprueba solicitudes ni modifica registros</small></div></div>
        <div class="card__cuerpo">
            <div class="chat">
                <div class="chat__msg chat__msg--bot">
                    Hola, soy el asistente de Recursos Humanos de EPAM. Puede preguntarme, por ejemplo:
                    <ul>
                        <li>¿Cómo va la asistencia de Angela Cruz?</li>
                        <li>¿Cuántas tardanzas hay este mes?</li>
                        <li>¿Cuál es el estado de la planilla?</li>
                    </ul>
                </div>
                @if($pregunta)
                    <div class="chat__msg chat__msg--yo">{{ $pregunta }}</div>
                @endif
                @if($respuesta)
                    <div class="chat__msg chat__msg--bot">
                        <strong>{{ $respuesta['titulo'] }}</strong>
                        <ul>
                            @foreach($respuesta['lineas'] as $linea)
                                <li>{{ $linea }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('asistente.preguntar') }}" class="chat-form">
                @csrf
                <input type="text" name="pregunta" placeholder="Escriba su consulta…" value="{{ $pregunta }}" required>
                <button class="btn btn--pri">Consultar</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:0">
        <div class="card__cab"><h2>Consultas frecuentes</h2></div>
        <div class="card__cuerpo">
            <form method="POST" action="{{ route('asistente.preguntar') }}">
                @csrf
                @foreach(['Angela Cruz', 'tardanzas del mes', 'planilla', 'Juan Pérez'] as $sugerencia)
                    <button name="pregunta" value="{{ $sugerencia }}" class="btn" style="width:100%;justify-content:flex-start;margin-bottom:7px">
                        <x-ui.icono name="chat" :size="14" /> {{ ucfirst($sugerencia) }}
                    </button>
                @endforeach
            </form>
        </div>
        <div class="card__pie">
            <small style="color:var(--texto-3)">El asistente usa <strong>Ollama local</strong> (variables <code>OLLAMA_URL</code> y <code>OLLAMA_MODELO</code> en <code>.env</code>, por defecto <code>http://127.0.0.1:11434</code> y <code>llama3.2</code>). Si Ollama no responde, contesta con el motor local de reglas. También puedes usarlo desde el botón robot flotante en cualquier pantalla.</small>
        </div>
    </div>
</div>
@endsection
