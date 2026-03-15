@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Editar Usuario del Sistema</p>
        </div>
		<div class="col-md-2 ">
            <a class="btn btn-outline-secondary custom" href="{{ route('usuarios') }}" role="button">
				<i class="fa-sharp fa-solid fa-plus"></i>
				<span>Regresar</span>
			</a>
		</div>
	</div>
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row">
		<div class="col-md-12">
            <form action="{{ route('usuarios.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('config.users.bodyform')
                <div class="row">
                    <div class="form-group col-md-4"></div>
                    <div class="form-group col-md-4">
                        <button type="submit" class="btn btn-primary custom">Actualizar</button>
                    </div>
                    <div class="form-group col-md-4"></div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
