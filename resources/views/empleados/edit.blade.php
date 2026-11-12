@extends('layouts.app')
@section('titulo', 'Editar empleado')
@section('migas', 'Empleados')
@section('contenido')

<div class="stepper">
    <a class="stepper__item is-active" href="#sec-basica"><i>1</i> Información básica</a>
    <a class="stepper__item" href="#sec-empleo"><i>2</i> Datos del empleo</a>
    <a class="stepper__item" href="#sec-salario"><i>3</i> Salario y contrato</a>
    <a class="stepper__item" href="#sec-personales"><i>4</i> Datos personales y contacto</a>
</div>
<div class="cab">
    <div><h1>Empleados</h1><p>Editando a {{ $empleado->nombre_completo }} ({{ $empleado->codigo }})</p></div>
    <div class="cab__acciones"><a href="{{ route('empleados.show', $empleado) }}" class="btn">Ver ficha</a></div>
</div>
@include('empleados._formulario', ['accion' => route('empleados.update', $empleado), 'metodo' => 'PUT'])
@endsection
