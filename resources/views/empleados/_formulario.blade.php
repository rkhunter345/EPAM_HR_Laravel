<div class="card">
    <div class="tabs">
        <a href="#sec-basica" class="is-active">Detalles del empleado</a>
        <a href="#sec-empleo">Datos del empleo</a>
        <a href="#sec-salario">Salario y contrato</a>
        <a href="#sec-personales">Datos personales y contacto</a>
    </div>

    <form method="POST" action="{{ $accion }}">
        @csrf
        @if($metodo !== 'POST')
            @method($metodo)
        @endif

        <div class="seccion" id="sec-basica">
            <h2 class="seccion-titulo"><x-ui.icono name="usuario" :size="15" /> Información básica</h2>
            <p class="seccion-nota">Datos generales del empleado. El identificador se genera automáticamente.</p>
            <div class="form-grid">
                <div class="campo col-4">
                    <label for="primer_nombre">Primer nombre *</label>
                    <input type="text" id="primer_nombre" name="primer_nombre" value="{{ old('primer_nombre', $empleado->primer_nombre) }}" required>
                </div>
                <div class="campo col-4">
                    <label for="segundo_nombre">Segundo nombre</label>
                    <input type="text" id="segundo_nombre" name="segundo_nombre" value="{{ old('segundo_nombre', $empleado->segundo_nombre) }}">
                </div>
                <div class="campo col-4">
                    <label for="apellido">Apellido *</label>
                    <input type="text" id="apellido" name="apellido" value="{{ old('apellido', $empleado->apellido) }}" required>
                </div>
                <div class="campo col-4">
                    <label for="genero">Género</label>
                    <select id="genero" name="genero">
                        <option value="">Seleccionar</option>
                        @foreach(['Femenino', 'Masculino', 'Otro'] as $g)
                            <option value="{{ $g }}" @selected(old('genero', $empleado->genero) === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="fecha_nacimiento">Fecha de nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($empleado->fecha_nacimiento)->format('Y-m-d')) }}">
                </div>
                <div class="campo col-4">
                    <label for="estado">Estado *</label>
                    <select id="estado" name="estado">
                        @foreach(\App\Models\Empleados\Empleado::ESTADOS as $estado)
                            <option value="{{ $estado }}" @selected(old('estado', $empleado->estado) === $estado)>{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="seccion" id="sec-empleo">
            <h2 class="seccion-titulo"><x-ui.icono name="organigrama" :size="15" /> Cargo y organización</h2>
            <p class="seccion-nota">Departamento, designación y calendario de trabajo del colaborador.</p>
            <div class="form-grid">
                <div class="campo col-4">
                    <label for="compania_id">Compañía</label>
                    <select id="compania_id" name="compania_id">
                        <option value="">Seleccionar</option>
                        @foreach($companias as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('compania_id', $empleado->compania_id) == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="sucursal_id">Sucursal</label>
                    <select id="sucursal_id" name="sucursal_id">
                        <option value="">Seleccionar</option>
                        @foreach($sucursales as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('sucursal_id', $empleado->sucursal_id) == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="departamento_id">Departamento</label>
                    <select id="departamento_id" name="departamento_id">
                        <option value="">Seleccionar</option>
                        @foreach($departamentos as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('departamento_id', $empleado->departamento_id) == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="designacion_id">Designación</label>
                    <select id="designacion_id" name="designacion_id">
                        <option value="">Seleccionar</option>
                        @foreach($designaciones as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('designacion_id', $empleado->designacion_id) == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="grado_empleado_id">Grado del empleado</label>
                    <select id="grado_empleado_id" name="grado_empleado_id">
                        <option value="">Seleccionar</option>
                        @foreach($grados as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('grado_empleado_id', $empleado->grado_empleado_id) == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="grupo_id">Grupo de empleados</label>
                    <select id="grupo_id" name="grupo_id">
                        <option value="">Seleccionar</option>
                        @foreach($grupos as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('grupo_id', $empleado->grupo_id) == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="tipo_empleo_id">Tipo de empleo</label>
                    <select id="tipo_empleo_id" name="tipo_empleo_id">
                        <option value="">Seleccionar</option>
                        @foreach($tiposEmpleo as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('tipo_empleo_id', $empleado->tipo_empleo_id) == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="calendario_id">Calendario de trabajo</label>
                    <select id="calendario_id" name="calendario_id">
                        <option value="">Seleccionar</option>
                        @foreach($calendarios as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('calendario_id', $empleado->calendario_id) == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="proyecto_cliente">Proyecto o cliente</label>
                    <input type="text" id="proyecto_cliente" name="proyecto_cliente" value="{{ old('proyecto_cliente', $empleado->proyecto_cliente) }}">
                </div>
            </div>
        </div>

        <div class="seccion" id="sec-salario">
            <h2 class="seccion-titulo"><x-ui.icono name="planilla" :size="15" /> Información salarial y contrato</h2>
            <p class="seccion-nota">Estos datos alimentan el cálculo automático de la planilla de remuneraciones.</p>
            <div class="form-grid">
                <div class="campo col-4">
                    <label for="salario_mensual">Salario mensual *</label>
                    <input type="number" step="0.01" id="salario_mensual" name="salario_mensual" value="{{ old('salario_mensual', $empleado->salario_mensual ?? 0) }}">
                </div>
                <div class="campo col-4">
                    <label for="moneda_salario">Moneda</label>
                    <select id="moneda_salario" name="moneda_salario">
                        @foreach(['PEN' => 'PEN - Sol', 'USD' => 'USD - Dólar'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('moneda_salario', $empleado->moneda_salario) === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="tipo_salario">Tipo de salario</label>
                    <select id="tipo_salario" name="tipo_salario">
                        @foreach(['Mensual', 'Quincenal', 'Por hora'] as $t)
                            <option value="{{ $t }}" @selected(old('tipo_salario', $empleado->tipo_salario) === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="frecuencia_pago">Frecuencia de pago</label>
                    <select id="frecuencia_pago" name="frecuencia_pago">
                        @foreach(['Mensual', 'Quincenal', 'Semanal'] as $f)
                            <option value="{{ $f }}" @selected(old('frecuencia_pago', $empleado->frecuencia_pago) === $f)>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="metodo_pago">Método de pago</label>
                    <select id="metodo_pago" name="metodo_pago">
                        @foreach(['Transferencia bancaria', 'Depósito en cuenta', 'Efectivo'] as $m)
                            <option value="{{ $m }}" @selected(old('metodo_pago', $empleado->metodo_pago) === $m)>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="cuenta_bancaria">Cuenta bancaria</label>
                    <input type="text" id="cuenta_bancaria" name="cuenta_bancaria" value="{{ old('cuenta_bancaria', $empleado->cuenta_bancaria) }}">
                </div>
                <div class="campo col-4">
                    <label for="afp">Sistema de pensiones</label>
                    <select id="afp" name="afp">
                        @foreach(['ONP' => 'ONP (13 %)', 'Habitat' => 'AFP Habitat (12.84 %)', 'Integra' => 'AFP Integra (12.92 %)', 'Prima' => 'AFP Prima (12.97 %)', 'Profuturo' => 'AFP Profuturo (13.06 %)'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('afp', $empleado->afp ?? 'ONP') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4 campo-check">
                    <input type="hidden" name="asignacion_familiar" value="0">
                    <input type="checkbox" id="asignacion_familiar" name="asignacion_familiar" value="1" @checked(old('asignacion_familiar', $empleado->asignacion_familiar))>
                    <label for="asignacion_familiar" style="margin:0">Asignación familiar (10 % de la RMV)</label>
                </div>
                <div class="campo col-4">
                    <label for="tipo_contrato_id">Tipo de contrato</label>
                    <select id="tipo_contrato_id" name="tipo_contrato_id">
                        <option value="">Seleccionar</option>
                        @foreach($tiposContrato as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('tipo_contrato_id', $empleado->tipo_contrato_id) == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="jornada_laboral">Jornada laboral</label>
                    <select id="jornada_laboral" name="jornada_laboral">
                        @foreach(['Tiempo completo', 'Medio tiempo', 'Por horas'] as $j)
                            <option value="{{ $j }}" @selected(old('jornada_laboral', $empleado->jornada_laboral) === $j)>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="horas_semanales">Horas semanales</label>
                    <input type="number" step="0.5" id="horas_semanales" name="horas_semanales" value="{{ old('horas_semanales', $empleado->horas_semanales) }}">
                </div>
                <div class="campo col-4">
                    <label for="fecha_ingreso">Fecha de ingreso</label>
                    <input type="date" id="fecha_ingreso" name="fecha_ingreso" value="{{ old('fecha_ingreso', optional($empleado->fecha_ingreso)->format('Y-m-d')) }}">
                </div>
                <div class="campo col-4">
                    <label for="fecha_confirmacion">Fecha de confirmación</label>
                    <input type="date" id="fecha_confirmacion" name="fecha_confirmacion" value="{{ old('fecha_confirmacion', optional($empleado->fecha_confirmacion)->format('Y-m-d')) }}">
                </div>
                <div class="campo col-4">
                    <label for="fecha_fin_contrato">Fin del contrato</label>
                    <input type="date" id="fecha_fin_contrato" name="fecha_fin_contrato" value="{{ old('fecha_fin_contrato', optional($empleado->fecha_fin_contrato)->format('Y-m-d')) }}">
                </div>
            </div>
        </div>

        <div class="seccion" id="sec-personales">
            <h2 class="seccion-titulo"><x-ui.icono name="usuario" :size="15" /> Datos personales y contacto</h2>
            <div class="form-grid">
                <div class="campo col-4">
                    <label for="tipo_documento">Tipo de documento</label>
                    <select id="tipo_documento" name="tipo_documento">
                        @foreach(['DNI', 'Carné de extranjería', 'Pasaporte', 'RUC'] as $td)
                            <option value="{{ $td }}" @selected(old('tipo_documento', $empleado->tipo_documento) === $td)>{{ $td }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="numero_documento">Número de documento</label>
                    <input type="text" id="numero_documento" name="numero_documento" value="{{ old('numero_documento', $empleado->numero_documento) }}">
                </div>
                <div class="campo col-4">
                    <label for="estado_civil">Estado civil</label>
                    <select id="estado_civil" name="estado_civil">
                        <option value="">Seleccionar</option>
                        @foreach(['Soltero(a)', 'Casado(a)', 'Conviviente', 'Divorciado(a)', 'Viudo(a)'] as $ec)
                            <option value="{{ $ec }}" @selected(old('estado_civil', $empleado->estado_civil) === $ec)>{{ $ec }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-4">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $empleado->telefono) }}">
                </div>
                <div class="campo col-4">
                    <label for="correo_personal">Correo personal</label>
                    <input type="email" id="correo_personal" name="correo_personal" value="{{ old('correo_personal', $empleado->correo_personal) }}">
                </div>
                <div class="campo col-4">
                    <label for="distrito">Distrito</label>
                    <input type="text" id="distrito" name="distrito" value="{{ old('distrito', $empleado->distrito) }}">
                </div>
                <div class="campo col-12">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $empleado->direccion) }}">
                </div>
                <div class="campo col-6">
                    <label for="contacto_emergencia">Contacto de emergencia</label>
                    <input type="text" id="contacto_emergencia" name="contacto_emergencia" value="{{ old('contacto_emergencia', $empleado->contacto_emergencia) }}">
                </div>
                <div class="campo col-6">
                    <label for="telefono_emergencia">Teléfono de emergencia</label>
                    <input type="text" id="telefono_emergencia" name="telefono_emergencia" value="{{ old('telefono_emergencia', $empleado->telefono_emergencia) }}">
                </div>
                <div class="campo col-12">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $empleado->observaciones) }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-acciones">
            <a href="{{ route('empleados.index') }}" class="btn">Cancelar</a>
            <button class="btn btn--pri">Guardar empleado</button>
        </div>

    <script>
    document.querySelectorAll('.stepper__item, .tabs a').forEach(function (a) {
        a.addEventListener('click', function (ev) {
            ev.preventDefault();
            var destino = this.getAttribute('href');
            var sec = document.querySelector(destino);
            if (sec) { sec.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
            document.querySelectorAll('.stepper__item, .tabs a').forEach(function (x) { x.classList.remove('is-active'); });
            this.classList.add('is-active');
        });
    });
    </script>
</form>
</div>
