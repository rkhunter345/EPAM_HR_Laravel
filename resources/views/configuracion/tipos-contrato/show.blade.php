@extends('layouts.app')
@section('titulo', $titulo)
@section('migas', $titulo)
@section('contenido')
    @include('components.crud.show')
@endsection
