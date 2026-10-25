/*
 * Alta/edición de usuarios: habilita los campos de contraseña y PIN solo si se marca "cambiar".
 */
(function ($) {
    'use strict';

    $('#change-password').on('click', function () {
        $('#password, #password-confirm').prop('disabled', !this.checked);
    });
    $('#change-pin').on('click', function () {
        $('#pin, #pin-confirm').prop('disabled', !this.checked);
    });
})(jQuery);
