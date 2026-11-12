<?php

/** Tasas y parámetros de la Planilla de Remuneraciones — Perú 2026.
 *  Fuentes: SUNAT (UIT 5,500, D.S. 301-2025-EF), MTPE (RMV S/ 1,230 desde
 *  01/10/2026, D.S. que incrementa la RMV), SBS (comisiones AFP sobre flujo),
 *  prima de seguro AFP 2026: 1.37%, EsSalud 9% (D.L. 19990), asignación
 *  familiar 10% de la RMV (D.L. 25129).
 */
return [
    'rmv' => 1230.00,
    'uit' => 5500.00,

    // Ingresos
    'asignacion_familiar_pct' => 0.10,
    'recargo_hora_extra_1' => 1.25,   // primeras 2 horas (+25%)
    'recargo_hora_extra_2' => 1.35,   // desde la 3.ª hora (+35%)
    'min_extras_tasa_1' => 120,       // minutos que van al 25%
    'bono_asistencia' => 50.00,

    // Descuentos del trabajador
    'onp' => 0.13,                    // Sistema Nacional de Pensiones
    'afp_aporte' => 0.10,             // aporte obligatorio (todas las AFP)
    'afp_prima_seguro' => 0.0137,     // prima de seguro 2026
    'afp_comisiones' => [             // comisión sobre flujo (SBS 2026)
        'Habitat'   => 0.0147,
        'Integra'   => 0.0155,
        'Prima'     => 0.0160,
        'Profuturo' => 0.0169,
    ],

    // Impuesto a la renta de 5.ª categoría (retención mensual = anual / 12)
    'renta_uit_exoneradas' => 7,
    'renta_tramos_uit' => [5 => 0.05, 20 => 0.14, 35 => 0.17, 45 => 0.20],
    'renta_tasa_exceso' => 0.30,

    // Aportes del empleador
    'essalud' => 0.09,
    'sctr' => 0.0125,
];
