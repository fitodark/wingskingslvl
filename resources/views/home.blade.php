@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Bienvenido!!</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="mx-auto" style="width: 470px;">
                        <img src="wings.jpg" class="rounded float-none" alt="Wings Kings">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
