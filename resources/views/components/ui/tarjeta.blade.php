<section class="v-card {{ $class ?? '' }}">
    @isset($titulo)
    <header class="v-card__cab">
        <div>
            <h2>@isset($icono)<x-ui.icono :name="$icono" :size="14" /> @endisset{{ $titulo }}</h2>
            @isset($nota)<small>{{ $nota }}</small>@endisset
        </div>
        @isset($acciones)<div class="v-acciones-fila">{{ $acciones }}</div>@endisset
    </header>
    @endisset
    <div class="{{ $sinRelleno ?? false ? '' : 'v-card__cuerpo' }}">{{ $slot }}</div>
    @isset($pie)<footer class="v-card__pie">{{ $pie }}</footer>@endisset
</section>
