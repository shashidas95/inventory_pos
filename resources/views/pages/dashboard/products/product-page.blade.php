@extends('layouts.sidenav-layout')
@section('content')
    {{-- @include('components.products.product-list')
    @include('components.products.product-delete') --}}
    @include('components.dashboard.admin.products.product-create')
    {{-- @include('components.products.product-update') --}}
@endsection
