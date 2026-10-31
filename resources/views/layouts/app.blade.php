<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'WKings PV') }}</title>

    <!-- Íconos: Font Awesome Free 6.5.2 (solo estilo sólido), local en public/vendor -->
    <link href="{{ asset('vendor/fontawesome/css/fontawesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/solid.min.css') }}" rel="stylesheet">

    <!-- Estilos: Bootstrap 4.3.1 con el tema de Laravel (app.css) y los del punto de venta -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/mystyle.css') }}" rel="stylesheet">

</head>
<body>
    <div id="app">
        {{-- navbar-expand: el menú es un solo dropdown, siempre visible (con -lg desaparecía en tablet vertical) --}}
        <nav class="navbar navbar-expand navbar-light header-bg">
            @guest
                <div class="container">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <i class="fa-solid fa-house-user"></i>
                    </a>
                </div>
            @else
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-solid fa-house-user"></i>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ url('/home') }}">Home</a>
                        {{-- Las opciones se muestran según el rol; las rutas también lo validan (routes/web.php). --}}
                        @anyrole('admin', 'encargado', 'mesero')
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('/comandas') }}">Comandas</a>
                        @endanyrole
                        @role('admin')
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('/catalogos') }}">Productos</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('tarifasenvio.index') }}">Tarifas de envío</a>
                        @endrole
                        @anyrole('admin', 'encargado', 'mesero')
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('clientes') }}">Clientes</a>
                        @endanyrole
                        @anyrole('admin', 'encargado')
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('cortecaja') }}">Cortes de Caja</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('cortecajamovimientos') }}">Registrar Movimientos al Corte</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('inventarioproductos') }}">Inventario</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('compras') }}">Compras</a>
                        @endanyrole
                        @role('admin')
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('usuarios') }}">Usuarios</a>
                        @endrole
                    </div>
                    </li>
                </ul>
                </div>
            @endguest
            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item mr-sm-2">
                        <a class="nav-link text-black" href="{{ route('login') }}">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>{{ __('Ingresar') }}</span>
                        </a>
                    </li>
                    <!-- @if (Route::has('register') )
                        <li class="nav-item my-2 my-sm-0">
                            <a class="nav-link text-black" href="{{ route('register') }}">{{ __('Registrar') }}</a>
                        </li>
                    @endif -->
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ Auth::user()->name }} 
                            <i class="fa-solid fa-user"></i>
                            <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>

    </div>
    {{-- Scripts sin compilación: librerías fijas en public/vendor y un archivo por módulo en public/js.
         Cada vista agrega el JS de su módulo con @push('scripts'). --}}
    <script>
        window.WK = {
            csrf: "{{ csrf_token() }}",
            rutas: {
                buscarCliente: "{{ route('getQueryClient') }}",
                cliente: "{{ route('getClient') }}", direccionesCliente: "{{ route('direccionesCliente') }}",
                historialCliente: "{{ route('getClientDetails') }}",
                tarifaEnvio: "{{ route('tarifaEnvio') }}", clienteColonia: "{{ route('clienteColonia') }}",
                cerrarVenta: "{{ route('cerrarVenta') }}",
                eliminarProducto: "{{ route('eliminarProducto') }}",
                cerrarCorteCaja: "{{ route('cerrarcortecaja') }}"
            }
        };
    </script>
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/bootstrap-4.3.1.bundle.min.js') }}"></script>
    <script src="{{ asset('js/wk-common.js') }}"></script>
    @stack('scripts')
</body>
</html>
