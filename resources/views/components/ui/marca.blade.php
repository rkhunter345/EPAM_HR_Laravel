@props(['variante' => 'oscuro', 'compacta' => false, 'tam' => null])
@php
    $clases = 'marca marca--'.$variante.($compacta ? ' marca--compacta' : '').($tam === 'lg' ? ' marca--lg' : '');
@endphp
<span {{ $attributes->merge(['class' => $clases]) }}>
    <svg class="marca__sello" viewBox="0 0 96 96" aria-hidden="true" focusable="false">
        <rect width="96" height="96" rx="20" fill="#0b2545"/>
        <circle cx="39" cy="35" r="9" fill="#ffffff"/>
        <path d="M22 70c0-9.4 7.6-17 17-17s17 7.6 17 17z" fill="#ffffff"/>
        <circle cx="64" cy="29" r="7.6" fill="#63b8ff"/>
        <path d="M51 66c0-8.4 5.8-15 13-15s13 6.6 13 15z" fill="#63b8ff"/>
        <circle cx="71" cy="48" r="5.4" fill="#2f8fe8"/>
    </svg>
    <span class="marca__txt"><strong>MULTISERVICIOS</strong><span>EPAM E.I.R.L.</span></span>
</span>
