@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if ($errors->any())
                <div class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <div class="card-group">
                <div class="card text-center border-light mb-3 bg-transparent">
                    <div class="mx-auto">
                        <img class="card-img-top" src="{{ asset('img/wings_back.png') }}" alt="Wings Kings">
                    </div>

                    <!-- <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <div class="mx-auto" style="width: 470px;">
                            <img src="{{ asset('img/wings.jpg') }}" class="rounded float-none" alt="Wings Kings">
                        </div>
                    </div> -->
                    <div class="card-footer border-light mb-3 card-bg">
                        <p class="h1 text-black">Bienvenidos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
