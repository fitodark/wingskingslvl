@extends('layouts.app')

@section('content')
<div class="container example-container">

    <div class="wk-encabezado">
        <p class="h2 text-black">Tarifas de envío</p>
        <div class="wk-encabezado-acciones">
            <a href="{{ route('tarifasenvio.create') }}" class="btn btn-outline-success btn-add-new" role="button">
                <i class="fa-solid fa-plus"></i>
                <span>Agregar</span>
            </a>
        </div>
    </div>

    <p class="text-muted small mb-2">
        Colonias que consulta el agente de WhatsApp. El monto express es informativo: no se suma al total ni sale en el ticket.
        Una colonia desactivada ya no se ofrece al cliente.
    </p>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif

    <form method="GET" action="{{ route('tarifasenvio.index') }}" class="form-inline mb-3">
        <label class="sr-only" for="buscarColonia">Buscar colonia</label>
        <input type="search" id="buscarColonia" name="q" value="{{ $buscar }}" class="form-control mr-2 mb-2"
            placeholder="Buscar colonia">
        <button type="submit" class="btn btn-outline-primary mb-2">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Buscar</button>
        @if ($buscar !== '')
            <a href="{{ route('tarifasenvio.index') }}" class="btn btn-link mb-2">Ver todas</a>
        @endif
    </form>

    <div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
        <tr>
            <th>Colonia</th>
            <th>Tipo</th>
            <th>Envío</th>
            <th class="text-right">Tarifa express</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($tarifas as $tarifa)
            <tr class="{{ $tarifa->activo ? '' : 'text-muted' }}">
                <td>{{ $tarifa->colonia }}</td>
                <td>{{ \App\TarifaEnvio::TIPOS[$tarifa->tipo] ?? $tarifa->tipo }}</td>
                <td>
                    @if ($tarifa->envio_gratuito)
                        <span class="badge badge-success">Gratuito</span>
                    @else
                        <span class="badge badge-warning">Solo express</span>
                    @endif
                </td>
                <td class="text-right text-nowrap">
                    @if ($tarifa->monto_express !== null)
                        @money($tarifa->monto_express)
                    @else
                        <span class="text-muted">Sin tarifa</span>
                    @endif
                </td>
                <td>{{ $tarifa->activo ? 'Activa' : 'Inactiva' }}</td>
                <td class="text-nowrap">
                    <a href="{{ route('tarifasenvio.edit', $tarifa->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</a>
                    <form method="POST" action="{{ route('tarifasenvio.activo', $tarifa->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $tarifa->activo ? 'btn-outline-danger' : 'btn-outline-success' }}">
                            {{ $tarifa->activo ? 'Desactivar' : 'Activar' }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No hay colonias que coincidan.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    {!! $tarifas->links() !!}
</div>
@endsection
