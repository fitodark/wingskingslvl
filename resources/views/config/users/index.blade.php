@extends('layouts.app')

@section('content')
  <div class="container">
      <div class="row justify-content-center">
          <div class="col-md-8">
              <div class="card">
                  <div class="card-header">Usuarios</div>

                  <div class="card-body">
                      <table class="table table-bordered">
                          <tr>
                              <th>No</th>
                              <th>Name</th>
                              <th>E-Mail</th>
                              <th width="280px">Action</th>
                          </tr>
                          @foreach ($users as $user)
                          <tr>
                              <td>{{ ++$i }}</td>
                              <td>{{ $user->name }}</td>
                              <td>{{ $user->email }}</td>
                              <td>

                              </td>
                          </tr>
                          @endforeach
                      </table>
                      {!! $users->links() !!}
                  </div>
              </div>
          </div>
      </div>
  </div>

@endsection
