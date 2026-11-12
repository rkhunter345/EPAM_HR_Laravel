@extends('layouts.app')
@section('titulo', 'Editar '.$singular)
@section('migas', $titulo)
@section('contenido')
    @include('components.crud.form')
@endsection
