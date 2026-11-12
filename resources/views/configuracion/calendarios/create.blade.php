@extends('layouts.app')
@section('titulo', 'Nuevo '.$singular)
@section('migas', $titulo)
@section('contenido')
    @include('components.crud.form')
@endsection
