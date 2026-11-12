<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Cliente del asistente inteligente contra un servidor Ollama local
 * (http://127.0.0.1:11434/api/chat). El modelo y la URL se configuran en .env
 * (OLLAMA_URL, OLLAMA_MODELO). Nunca envía datos fuera del contexto autorizado
 * por el rol del usuario que consulta.
 */
class OllamaService
{
    public function url(): string
    {
        return rtrim((string) config('epam.ollama.url'), '/');
    }

    public function modelo(): string
    {
        return (string) config('epam.ollama.modelo', 'llama3.2');
    }

    /** ¿Está corriendo Ollama? (ping corto a /api/tags). */
    public function disponible(): bool
    {
        try {
            return Http::timeout(3)->get($this->url().'/api/tags')->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Conversación con el modelo. $contexto contiene SOLO los datos que el
     * rol del usuario puede ver. Devuelve null si Ollama no responde.
     */
    public function conversar(string $pregunta, string $contexto): ?string
    {
        try {
            $respuesta = Http::timeout(60)->post($this->url().'/api/chat', [
                'model' => $this->modelo(),
                'stream' => false,
                'options' => ['temperature' => 0.2],
                'messages' => [
                    ['role' => 'system', 'content' => $this->promptSistema()."\n\nDATOS AUTORIZADOS DEL SISTEMA (única fuente de verdad, no inventes cifras):\n".$contexto],
                    ['role' => 'user', 'content' => $pregunta],
                ],
            ]);
        } catch (\Throwable $e) {
            return null;
        }

        if ($respuesta->failed()) {
            return null;
        }

        return trim((string) $respuesta->json('message.content')) ?: null;
    }

    private function promptSistema(): string
    {
        return <<<TXT
Eres el asistente virtual de Recursos Humanos de MULTISERVICIOS EPAM E.I.R.L.
Reglas obligatorias:
1) Responde SOLO con los datos del bloque "DATOS AUTORIZADOS". Si el dato no está, dilo y sugiere qué consultar.
2) No inventes cifras, nombres ni fechas.
3) No puedes aprobar solicitudes, modificar marcaciones, salarios ni la planilla: eres solo consulta.
4) Antes de sugerir un reporte o constancia, confirma el período y la persona.
5) Responde en español, breve y claro (máximo 8 líneas).
TXT;
    }
}
