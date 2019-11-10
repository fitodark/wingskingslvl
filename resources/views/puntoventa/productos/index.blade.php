@extends('layouts.app')

@section('content')
  <div class="container">
      <div class="row justify-content-center">
          <div class="col-md-16">
              <div class="card">
                  <div class="card-header">Catalogo de Productos</div>

                  {{-- <div class="card-body"> --}}
                  <ul class="list-group list-group-flush">
                      <li class="list-group-item">


                      <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="Toolbar with button groups">
                        <div class="btn-group" role="group" aria-label="First group">
                          <a href="{{ route('catalogos.create') }}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Agregar</a>
                          <a href="#" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Buscar</a>
                        </div>
                      </div>

                      @if ($message = Session::get('success'))
                          <div class="alert alert-success">
                              <p>{{ $message }}</p>
                          </div>
                      @endif
                      </li>

                      <li class="list-group-item">
                      <table class="table table-striped">
                          <tr>
                              <th>No</th>
                              <th>Name</th>
                              <th>Details</th>
                              <th>Price</th>
                              <th width="280px">Action</th>
                          </tr>
                          @foreach ($products as $product)
                          <tr>
                              <td>{{ ++$i }}</td>
                              <td>{{ $product->name }}</td>
                              <td>{{ $product->detail }}</td>
                              <td>@money($product->price)</td>
                              <td>
                                  <form action="{{ route('catalogos.destroy',$product->id) }}" method="POST">

                                      <a class="btn btn-info" href="{{ route('catalogos.show',$product->id) }}">Show</a>

                                      <a class="btn btn-primary" href="{{ route('catalogos.edit',$product->id) }}">Edit</a>

                                      @csrf
                                      @method('DELETE')

                                      <button type="submit" class="btn btn-danger">Delete</button>
                                  </form>
                              </td>
                          </tr>
                          @endforeach
                      </table>
                      {!! $products->links() !!}
                      </li>
                  </ul>
              </div>
          </div>
      </div>
  </div>

@endsection
