function comprobarRegistro() {
    var error = false;
    var login = $('#login').val();
    var contrasenna = document.getElementById("contrasenna").value;
    var repitecontrasenna = document.getElementById("repitecontrasenna").value;
    var email = $("#email").val();
    var avatar = "";
    //Comprobar si avatar no esta vacio
    if (undefined !== document.getElementById("avatar").files[0]) {
        var avatar = document.getElementById("avatar").files[0].name;
    }
    //Comprobacion login
    if (login.length <= 3) {
        $('#errorLogin').text('*El login iene que tener mas de 3 carácteres');
        error = true;
    } else {
        $('#errorLogin').text('');
    }
    //Comprobacion email
    if (email.length < 1) {
        $('#errorEmail').text('*El email es campo obligatorio');
        error = true;
    } else if (!(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.([a-zA-Z]{2,4})+$/.test(email))) {
        $('#errorEmail').text('*Introduce un email');
        error = true;
    } else {
        $('#errorEmail').text('');
    }
    //Comprobacion contraseñas
    if (contrasenna.length < 1) {
        $('#errorContrasenna').text('*La contraseña es campo obligatorio');
        error = true;
    } else if (contrasenna.length <= 3) {
        $('#errorContrasenna').text('*La contraseña tiene que tener al menos 4 carácteres');
        error = true;
    } else if (contrasenna != repitecontrasenna) {
        $('#errorContrasenna').text('*Las contraseñas no coinciden');
        error = true;
    } else {
        $('#errorContrasenna').text('');
    }
    //Comprobacion avatar
    if (avatar.length > 0) {
        var extension = avatar.substring(avatar.lastIndexOf(".") + 1);
        if (extension != "jpg" && extension != "png" && extension != "jpeg" && extension != "gif") {
            $('#errorAvatar').text('*El archivo no es una imagen');
            error = true;
        }
    }
    if (!error) {
        //Comprobar si exite el usuario, sino registramos
        fetch(`${URL_PATH}/api/comprobarLogin/${login}`)
            .then(function (response) {
                return response.text()
            }).then (function (datos){
                if (datos == "si") {
                    $('#errorLogin').text('*El usuario ya existe');
                    error = true;
                } else {
                    $('#enviar').click();
                }
        })
    }
}

window.onload = function () {
    $(document).on("keypress", 'form', function (e) {
        var code = e.keyCode || e.which;
        if (code == 13) {
            e.preventDefault();
            comprobarRegistro();
            return false;
        }
    });
}