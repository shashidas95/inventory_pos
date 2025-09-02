@extends('layouts.sidenav-layout')
@section('content')
    @include('components.dashboard.admin.invoices.list')
    {{-- @include('components.invoice.invoice-delete')
    @include('components.invoice.invoice-details') --}}
@endsection
