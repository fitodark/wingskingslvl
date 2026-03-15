<div class="row">
    <div class="form-group col-md-6">
        <strong>Nombre Completo:</strong>
        @if (empty($user))
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
            placeholder="Nombre" name="name" value="{{ old('name') }}" 
            required autocomplete="name" autofocus>
        @else
            <input id="name" type="text" class="form-control"
            name="name" value="{{ $user->name }}" 
            required autofocus>
        @endif
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <strong>Email:</strong>
        @if (empty($user))
        <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" 
            name="email" value="{{ old('email') }}" 
            required autocomplete="email">
        @else
            <input id="email" type="text" class="form-control"
            name="email" value="{{ $user->email }}" 
            required autofocus>
        @endif
        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <strong>Rol:</strong>
        @if (empty($user))
            <select name="idrol" id="idrol" class="form-control">
                @foreach ($roles as $value)
                <option disable="true" value="{{ $value->id }}">{{$value->description}}</option>
                @endforeach
            </select>
        @else
            <select name="idrol" id="idrol" class="form-control" value="{{ $rol->role_id }}">
            @foreach ($roles as $value)
            <option disable="true" value="{{ $value->id }}"
                {{ ($value->id == $rol->role_id) ? 'selected' : '' }}>{{ $value->description}}</option>
            @endforeach
            </select>
        @endif
        @error('idrol')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<!-- @if (!empty($user))
<div class="row">
    <div class="form-group col-md-6">
        <strong>Estatus:</strong>
        <select name="status" id="status" class="form-control" value="{{ $user->status }}">
        @foreach ($arraystatus as $key => $value)
        <option disable="true" value="{{ $key }}"
            {{ ($key == $user->status) ? 'selected' : '' }}>{{ $value}}</option>
        @endforeach
        </select>
    </div>
</div>
@endif -->

<div class="row">
    <div class="form-group col-md-6">
    <div class="form-check">
        <input id="change-password" type="checkbox" class="form-check-input" 
        name="change-password" {{ ($disablecheck) ? '' : 'disabled' }}>
        <label class="form-check-label" for="change-password">
            Cambiar Contraseña
        </label>
    </div>
    </div>
</div>

<div class="row">
    <div class="form-group col-md-6">
        <strong>Contraseña:</strong>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
        name="password" required autocomplete="new-password" {{ ($disablecheck) ? 'disabled' : '' }}>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <strong>Confirmar Contraseña:</strong>
        <input id="password-confirm" type="password" class="form-control" 
        name="password_confirmation" required autocomplete="new-password" {{ ($disablecheck) ? 'disabled' : '' }}>
    </div>
</div>

