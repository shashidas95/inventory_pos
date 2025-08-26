@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-12 mt-5">
                <div class="bg-light p-5">
                    <h1 class="text-center">Pos Inventory Application</h1>
                    <hr>
                    <div class="text-center">
                        <a class="btn bg-gradient-primary" href="{{ route('login') }}">Start Sell</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
