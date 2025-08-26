@extends('layouts.sidenav-layout')
@section('content')
    {{-- @include('components.category.category-list')
    @include('components.category.category-delete') --}}

    @include('components.dashboard.admin.categories.category-list')
    {{-- @include('components.category.category-update') --}}
@endsection
