@if($propio)
    <span class="v-mini">Caso propio · derivar a RR. HH.</span>
@else
    <form method="POST" action="{{ route('equipo.revisar', [$tipo, $id]) }}" style="display:flex;gap:5px;align-items:center">
        @csrf
        <select name="decision" style="padding:5px 7px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px">
            <option>Aprobada</option><option>Rechazada</option><option>Información solicitada</option>
        </select>
        <input type="text" name="observacion" placeholder="Observación" style="padding:5px 7px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px;width:110px">
        <button class="btn btn--sm btn--pri">Guardar</button>
    </form>
@endif
