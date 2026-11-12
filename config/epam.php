<?php

/** Configuración propia de EPAM HR.
 *  Ollama: URL del servidor local (por defecto http://127.0.0.1:11434) y modelo.
 *  IoT: token que deben presentar las estaciones para enviar eventos.
 *       Defina IOT_TOKEN en .env con un valor aleatorio privado; si falta o es
 *       el marcador de ejemplo, la API rechaza los eventos (fail-safe).
 */
return [
    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
        'modelo' => env('OLLAMA_MODELO', 'llama3.2'),
    ],
    'iot' => [
        'token' => env('IOT_TOKEN', ''),
    ],
];
