@extends('layouts.admin')

@section('title', 'Quick Create Product')

@section('content')
@php
    $isQuickCreate = $isQuickCreate ?? true;
@endphp
@include('admin.products.partials.dynamic-form', ['isQuickCreate' => $isQuickCreate])
@endsection
