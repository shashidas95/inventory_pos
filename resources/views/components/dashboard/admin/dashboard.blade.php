@extends('layouts.sidenav-layout')


@section('content')
    <div class="container mt-5">
        <h3>Dashboard</h3>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h5>Today's Sales</h5>
                    <h3>{{ $today }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h5>This Month Sales</h5>
                    <h3>{{ $month }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h5>Total Sales</h5>
                    <h3>{{ $total }}</h3>
                </div>
            </div>
        </div>
    </div>
@endsection
