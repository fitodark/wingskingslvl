{{-- Selector de fecha; se inicializa en public/js/wk-common.js (.datepicker y .datepickercorteformat) --}}
@push('scripts')
<link rel="stylesheet" href="{{ asset('vendor/datepicker/css/bootstrap-datepicker3.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datepicker/css/bootstrap-datepicker3.standalone.min.css') }}">
<script src="{{ asset('vendor/datepicker/js/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('vendor/datepicker/locales/bootstrap-datepicker.es.min.js') }}"></script>
@endpush
