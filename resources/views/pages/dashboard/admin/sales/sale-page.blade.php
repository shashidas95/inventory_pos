@extends('layouts.sidenav-layout')
@section('content')
    {{-- Sales List --}}
    {{-- @include('components.dashboard.admin.sales.sale-list') --}}

    {{-- Create Sale Modal --}}
    @include('components.dashboard.admin.sales.create')

    {{-- Update Sale Modal --}}
    {{-- @include('components.dashboard.admin.sales.sale-update') --}}

    {{-- Delete Sale Modal --}}
    {{-- @include('components.dashboard.admin.sales.sale-delete') --}}
@endsection
