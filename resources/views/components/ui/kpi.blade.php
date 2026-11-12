<div class="v-kpi">
    <div class="v-kpi__top">
        <span class="v-kpi__ico tono-{{ $tono ?? 'azul' }}"><x-ui.icono :name="$icono ?? 'tablero'" :size="14" /></span>
        <span class="v-kpi__label">{{ $label }}</span>
    </div>
    <div class="v-kpi__valor">{{ $valor }}@isset($unidad)<small>{{ $unidad }}</small>@endisset</div>
    @isset($sub)<div class="v-kpi__sub">{!! $sub !!}</div>@endisset
</div>
