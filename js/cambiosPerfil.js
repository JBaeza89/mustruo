window.onload = function () {
    console.log("Height: "+window.innerHeight)
    console.log("Width: "+window.innerWidth)
    var login = $('#spanLogin').text();
    var email = $('#spanEmail').text();
    $('#avatar').change(function () {
        $('#cambiarAvatar').click();
    })
    $('#editarEmail').click(function () {
        $('#spanEmail').css('display', 'none');
        $('#editarEmail').css('display', 'none');
        $('.editarEmail').css('display', 'inline');
    })
    $('#cancelarEmail').click(function () {
        $('#spanEmail').css('display', 'inline');
        $('#editarEmail').css('display', 'inline');
        $('.editarEmail').css('display', 'none');
        $('#email').val(email);
    })
    $('#cambiarEmail').click(function () {
        const emailCambio = $('#email').val();
        if (email === emailCambio) {
            $('#email').css('background-color', 'palegreen').css('color', 'green').css('border-color', 'green');
            alert('No ha habido cambios');
        } else if (!(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.([a-zA-Z]{2,4})+$/.test(emailCambio))) {
            $('#email').css('background-color', 'red').css('color', 'white').css('border-color', 'red');
            alert('Escriba su email, por favor.');
        } else {
            fetch(`${URL_PATH}/api/cambiarEmail/${login}/${emailCambio}`)
            .then(function (response) {
                return response.text()
            }).then(function (datos) {
                switch (datos) {
                    case "0":
                        //error
                        alert('Ha ocurrido un error interno, pruebe mas tarde');
                        break;
                    case "1":
                        //exito
                        $('#email').css('background-color', 'palegreen').css('color', 'green').css('border-color', 'green');
                        $('#spanEmail').css('display', 'inline');
                        $('#editarEmail').css('display', 'inline');
                        $('.editarEmail').css('display', 'none');
                        $('#email').val(emailCambio);
                        $('#spanEmail').text(emailCambio);
                        email = emailCambio;
                        alert('Email modificado con éxito');
                        break;
                }
            })
        }
    })
    $('#editarContrasenna').click(function () {
        $('#spanContrasenna').css('display', 'none');
        $('#editarContrasenna').css('display', 'none');
        $('.editarContrasennas').css('display', 'inline');
    })
    $('#cambiarContrasenna').click(function () {
        const contrasenna = $('#contrasenna').val();
        const repitecontrasenna = $('#repitecontrasenna').val();
        if (contrasenna.length < 4) {
            $('#contrasenna').val('');
            $('#repitecontrasenna').val('');
            alert('La contraseña tiene que tener al menos 4 carácteres');
        } else if (contrasenna != repitecontrasenna) {
            $('#contrasenna').val('');
            $('#repitecontrasenna').val('');
            alert('Las contraseñas no coinciden');
        } else {
            fetch(`${URL_PATH}/api/cambiarContrasenna/${login}/${contrasenna}`)
                .then(function (response) {
                    return response.text()
                }).then(function (datos) {
                    console.log(datos);
                    switch (datos) {
                        case "0":
                            //error
                            alert('Ha ocurrido un error interno, pruebe mas tarde');
                            break;
                        case "1":
                            //exito
                            $('#spanContrasenna').css('display', 'inline');
                            $('#editarContrasenna').css('display', 'inline');
                            $('.editarContrasennas').css('display', 'none');
                            $('#contrasenna').val('');
                            $('#repitecontrasenna').val('');
                            alert('Contraseña modificada con éxito');
                            break;
                    }
                })
        }
    })
    $('#cancelarContrasenna').click(function () {
        $('#spanContrasenna').css('display', 'inline');
        $('#editarContrasenna').css('display', 'inline');
        $('.editarContrasennas').css('display', 'none');
        $('#contrasenna').val('');
        $('#repitecontrasenna').val('');
    })
}