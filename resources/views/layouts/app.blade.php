<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TPV') }}</title>

    <!-- Fonts -->
    <!--<link rel="dns-prefetch" href="//fonts.gstatic.com">-->
    <!--<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">-->
    <script src="https://kit.fontawesome.com/e862dac06d.js" crossorigin="anonymous"></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/mystyle.css') }}" rel="stylesheet">
    <!-- <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.min.css') }}" rel="stylesheet"> -->
    

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg navbar-light header-bg">
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
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('/comandas') }}">Comandas</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('summary.index') }}">Resumen de Ventas</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('/catalogos') }}">Productos</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('clientes') }}">Clientes</a>
                    </div>
                    </li>
                </ul>
                </div>
            @endguest
            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto my-2 my-lg-0">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item mr-sm-2">
                        <a class="nav-link text-black" href="{{ route('login') }}">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>{{ __('Ingresar') }}</span>
                        </a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item my-2 my-sm-0">
                            <a class="nav-link text-black" href="{{ route('register') }}">{{ __('Registrar') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <!-- {{ Auth::user()->name }}  -->
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

        <footer class="text-center text-white fixed-bottom footer-bg">
            <!-- Grid container -->
            <div class="container pt-2">
                <!-- Section: Social media -->
                <section class="">
                <div class="container text-center text-md-start mt-5">
                    <!-- Grid row -->
                    <div class="row mt-3">
                    <!-- Grid column -->
                    <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-3">
                        <!-- Content -->
                        <a class="btn btn-link btn-floating btn-lg text-black m-1" href="#!" role="button" >
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    </div>
                    <!-- Grid column -->

                    <!-- Grid column -->
                    <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-3">
                        <!-- Links -->
                        <a class="btn btn-link btn-floating btn-lg text-black m-1" href="#!" role="button" data-mdb-ripple-color="dark">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                    <!-- Grid column -->

                    <!-- Grid column -->
                    <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-3">
                        <!-- Links -->
                        <a class="btn btn-link btn-floating btn-lg text-black m-1" href="#!" role="button" data-mdb-ripple-color="dark">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                    <!-- Grid column -->
                    </div>
                    <!-- Grid row -->
                </div>
                </section>
                <!-- Section: Social media -->
            </div>
            <!-- Grid container -->

            <!-- Copyright -->
            <div class="text-center text-black p-2 footer-bg">
                ©2022 Copyright:
                <a class="text-dark" href="https://ftp.com/">ftp.com</a>
            </div>
            <!-- Copyright -->
        </footer>
    </div>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/bootstrap3-typeahead.js') }}"></script>

    <!-- Datepicker Files -->
    <link rel="stylesheet" href="{{asset('js/datePicker/css/bootstrap-datepicker3.css')}}">
    <link rel="stylesheet" href="{{asset('js/datePicker/css/bootstrap-datepicker3.standalone.css')}}">
    <script src="{{asset('js/datePicker/js/bootstrap-datepicker.js')}}"></script>
    <!-- Languaje -->
    <script src="{{asset('js/datePicker/locales/bootstrap-datepicker.es.min.js')}}"></script>

    <script>
    var path = "{{ route('getQueryClient') }}";
    $('input.typeahead').typeahead({
        source:  function (query, process) {
            return $.get(path, { query: query }, function (data) {
                return process(data);
            });
        },
        displayText: function (item) {
            return item.name + ', ' + item.address;
        },
        afterSelect: function (item) {
            console.log('afterSelect');
            console.log(item);
            $('#clientId').val(item.id);
        }
    });
    $('#dialogDetails').on('show.bs.modal', function (event) {
        console.log("show simple modal...");
        var button = $(event.relatedTarget);
        var id = button.data('productid');
        var venta = button.data('ventaid');
        var tab = button.data('tab');
        var price = button.data('price');
        var productName = button.data('productname');

        var modal = $(this)
        $('#productName').html(productName);
        modal.find('.modal-body #idProduct').val(id);
        modal.find('.modal-body #ventaId').val(venta);
        modal.find('.modal-body #tab').val(tab);
        modal.find('.modal-body #price').val(price);
    })
    $('#dialogFoodDetails').on('show.bs.modal', function (event) {
        console.log("show modal...");
        var button = $(event.relatedTarget);
        var id = button.data('productid');
        var venta = button.data('ventaid');
        var tab = button.data('tab');
        var price = button.data('price');
        var foodname = button.data('foodname');

        var modal = $(this)
        $('#flavors').val('');
        $('#pieces').val('');
        $('#foodName').html(foodname);
        modal.find('.modal-body #idProduct').val(id);
        modal.find('.modal-body #ventaId').val(venta);
        modal.find('.modal-body #tab').val(tab);
        modal.find('.modal-body #price').val(price);

        document.getElementById('myTable').innerHTML = "";

        $('#addFoodRow').prop('disabled', true);
    })
    $('#finalizarVentaModal').on('show.bs.modal', function (event) {
        console.log("show modal finalizarVentaModal...");
        var button = $(event.relatedTarget);
        var location = button.data('location');
        var total = button.data('total');
        var ventaId = button.data('ventaid');

        var modal = $(this);
        modal.find('.modal-body #location').val(location);
        modal.find('.modal-body #total').val(total);
        modal.find('.modal-body #folio').val(ventaId);
        modal.find('.modal-body #quantity').val('');
    })
    $('#addClientModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        // var productname = button.data('productname');
        var ventaid = button.data('ventaid');

        var modal = $(this);
        modal.find('.modal-body #type').val(2);
        modal.find('.modal-body #ventaid').val(ventaid);
        modal.find('.modal-body #clientName').val('');
        modal.find('.modal-body #clientPhone').val('');
        modal.find('.modal-body #clientAddress').val('');
        modal.find('.modal-body #clientReference').val('');
    })
    $('#exampleModal').on('show.bs.modal', function (event) {
        console.log("show exampleModal modal...");
    })
    $('#carouselExampleIndicators').on('slide.bs.carousel', function () {
        console.log("carousel events...");
    })
    $('#collapseOne').on('shown.bs.collapse', function (event) {
        console.log('collapseOne');
        $('#type').val(1);
        $('#table').val('');
    })
    $('#collapseTwo').on('shown.bs.collapse', function (event) {
        console.log('collapseTwo');
        $('#type').val(2);
        $('#findClient').val('');
        $('#clientName').val('');
        $('#clientPhone').val('');
        $('#clientAddress').val('');
        $('#clientReference').val('');
    })
    $("#addrow").on("click", function () {
        var newRow = $("<tr>");
        var cols = "";
        var labelPieces = $("select[name='piecesnumber'] option:selected").text();
        var valuePieces = $("select[name='piecesnumber'] option:selected").val();

        var labelFlavors = $("select[name='flavors'] option:selected").text();
        var valueFlavors = $("select[name='flavors'] option:selected").val();

        if (valuePieces !== undefined && valueFlavors != undefined) {
          cols += '<td id="' + valuePieces + '">' + labelPieces + '</td>';
          cols += '<td id="' + valueFlavors + '">' + labelFlavors + '</td>';

          cols += '<td><button type="button" class="ibtnDel btn btn-outline-danger btn-sm">Eliminar</button></td>';
          newRow.append(cols);
          $("table.order-list").append(newRow);
          var $dataElements = $('#myTable').find('tr');
          data = [];

          $.each($dataElements, function(i, elem){
            var childrens = [];
            if (i >= 0) {
              childrens = $(elem).children();
              if (childrens) {
                data.push(
                  [{'key' : childrens[0].id, 'value' : childrens[0].innerHTML},
                  {'key' : childrens[1].id, 'value' : childrens[1].innerHTML}]
                );
              }
            }
          });
          $('#addFoodRow').prop('disabled', false);
          $('#description').val(JSON.stringify(data));
        } else {
          alert('Seleccionar los productos...');
        }
    });
    $("table.order-list").on("click", ".ibtnDel", function (event) {
        $(this).closest("tr").remove();
        var length = $('#myTable').find('tr').length;
        if (length == 1) {
            $('#addFoodRow').prop('disabled', true);
        }
    });
    $("#client").keyup(function(){
        var query = $(this).val();
        if (query != '' && query.length > 3){
            var _token = $('input[name="_token"]').val();
            $.ajax({
                url : "{{ route('getQueryClient') }}",
                method : "POST",
                data : {
                    query : query,
                    _token : _token
                },
                success : function (data) {
                    $('#clientList').fadeIn();
                    $('#clientList').html(data);
                }
            });
        }
    });
    $("#finalizarVenta").click(function(event) {
        event.preventDefault();
        var quantity = $('#quantity').val();
        var ventaid = $('#folio').val();

        var _token = $('input[name="_token"]').val();
        $.ajax({
            url : "{{ route('cerrarVenta') }}",
            method : "POST",
            data : {
                quantity : quantity,
                ventaid : ventaid,
                _token : _token
            },
            success : function (data) {
                console.log(data);
                if(data.errors) {
                    $('.alert-danger').html('');
                    $.each(data.errors, function(key, value){
                  			$('.alert-danger').show();
                  			$('.alert-danger').append('<li>'+value+'</li>');
                		});
                } else {
                    $('.alert-danger').hide();
                    $('#finalizarVentaModal').modal('hide');
                    window.location = data.url
                }
            }
        });

    });
    $('.datepicker').datepicker({
        format: "dd/mm/yyyy",
        language: "es",
        autoclose: true
    });
    $("#btnAddClient").click(function(event) {
        event.preventDefault();
        var ventaid = $('#ventaid').val();
        var type = $('#type').val();
        var clientName = $('#clientName').val();
        var clientPhone = $('#clientPhone').val();
        var clientAddress = $('#clientAddress').val();
        var clientReference = $('#clientReference').val();

        var _token = $('input[name="_token"]').val();
        $.ajax({
            url : "{{ route('addClient') }}",
            method : "POST",
            data : {
                ventaid : ventaid,
                type : type,
                name : clientName,
                phone : clientPhone,
                address : clientAddress,
                reference : clientReference,
                _token : _token
            },
            success : function (data) {
                console.log(data);
                if(data.errors) {
                    $('.alert-danger').html('');
                    $.each(data.errors, function(key, value){
                  			$('.alert-danger').show();
                  			$('.alert-danger').append('<li>'+value+'</li>');
                		});
                } else {
                    $('.alert-danger').hide();
                    $('#addClientModal').modal('hide');
                    window.location = data.url
                }
            }
        });

    });
    </script>

</body>
</html>
