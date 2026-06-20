@extends('layouts.app')

@section('content')
  <div class="container">
      <div class="row justify-content-center">
          <div class="col-md-16">
              <div class="card">
                  <div class="card-header">Editar Producto
                    <div class="float-right">
                        <a class="btn btn-primary" href="{{ route('catalogos.index') }}">Regresar</a>
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
                  <div class="card-body">
                  <form action="{{ route('catalogos.update',$catalogo->id) }}" method="POST">
                      @csrf
                      @method('PUT')

                       <div class="row">
                          <div class="form-group col-md-6">
                                  <strong>Nombre:</strong>
                                  <input type="text" name="name" value="{{ $catalogo->name }}" class="form-control" placeholder="Name">
                          </div>
                          <div class="form-group col-md-6">
                                  <strong>Descripción:</strong>
                                  <textarea class="form-control" style="height:100px" name="detail"
                                      placeholder="Detail">{{ $catalogo->detail }}</textarea>
                          </div>
                          <div class="form-group col-md-6">
                                  <strong>Precio:</strong>
                                  <input type="number" name="price" class="form-control" placeholder="Precio"
                                  pattern="[0-9]+([\.,][0-9]+)?" step="0.01" title="This should be a number with up to 2 decimal places."
                                  value="{{ $catalogo->price }}">
                          </div>

                          <div class="form-group col-md-6">
                                  <strong>Categoria:</strong>
                                  <select class="custom-select" name="type" value="{{ $catalogo->type }}">
                                    @foreach ($productsType as $key => $value)
                                    <option value="{{ $key }}" {{ ($key == 0) ? 'selected' : '' }}>{{$value}}</option>
                                    @endforeach
                                  </select>
                          </div>
                        <div class="form-group col-md-6">
                            <strong>Venta en:</strong>
                            <select class="custom-select" name="promotionType" value="{{ $catalogo->promotion_type }}">
                                @foreach ($productsPromotionType as $key => $value)
                                <option value="{{ $key }}" {{ ($key == 0) ? 'selected' : '' }}>{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                          <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                          </div>
                      </div>
                  </form>
              </div>
            </div>
          </div>
      </div>
  </div>
@endsection
