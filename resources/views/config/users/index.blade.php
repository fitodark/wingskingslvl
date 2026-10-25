@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="wk-encabezado">
	    <p class="h2 text-black">Control de Usuarios</p>
	    <div class="wk-encabezado-acciones">
	        <a href="{{ route('usuarios.create') }}" class="btn btn-outline-success btn-add-new" role="button">
	        <i class="fa-solid fa-plus"></i>
	        <span>Agregar</span>
	        </a>
	    </div>
	</div>
  
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
		<div class="col-md-12 py-3">
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <p>{{ $message }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div>
                <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <tr>
                        <th width="5%">#</th>
                        <th width="30%">Nombre</th>
                        <th width="20%">Correo</th>
                        <th width="15%">Rol</th>
                        <th width="15%">Estatus</th>
                        <th width="15%">Acción</th>
                    </tr>
                    @foreach ($users as $user)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach ($user->stringRole as $rol)
                                {{$rol->description}} <br>
                            @endforeach
                        </td>
                        <td>@activo($user->status)</td>
                        <td>
                            <form action="{{ route('usuarios.destroy', $user->id) }}" method="POST">
                                <a href="{{ route('usuarios.edit', $user->id) }}">
                                    <i class="ico-edit"></i>
                                </a>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn">
                                    <i class="ico-delete"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </table>
                </div>
                {!! $users->links() !!}
            </div>
        </div>
    </div>
</div>

@endsection
