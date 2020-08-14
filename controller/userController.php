<?php
namespace controller;
use orm\OrmUser;
class UserController extends Controller {

    /*
    * Método para ir al formulario de registro
    */
    function registro() {
        $title = "Registro";
        $enLobby = false;
        echo \dawfony\Ti::render("view/RegistroView.phtml", compact('title', 'enLobby'));
    }

    /*
    * Método para registrar al usuario
    */
    function registrar() {
        global $URL_PATH;
        $orm = new OrmUser;
        $login = sanitizar($_POST["login"]); //Login de registro
        $contrasenna = password_hash($_POST["contrasenna"], PASSWORD_DEFAULT); //Contraseña de registro con HASH
        $email = $_POST["email"]; //Email de registro
        $img = $_FILES["avatar"];
        if (strlen($img["name"]) > 0) {
            $img["name"] = cambiarNombreAvatar($img["name"], $login);
        } else {
            $img["name"] = "anonimus.jpg";
        }
        guardarAvatar($img); //Almacenar imagen en carpeta avatares
        if ($orm->registrarUsuario($login, $email, $img["name"], $contrasenna)) {
            //OK
        } else {
            //Error Registro
        }
        header("Location: $URL_PATH/");
    }

    /*
    * Método para cerrar sesion
    */
    function cerrarSesion() {
        global $URL_PATH;
        session_destroy();
        header("Location: $URL_PATH/");
    }

    function perfil($login) {
        $title = "Perfil de $login";
        $enLobby = false;
        $orm = new OrmUser;
        $usuario = $orm->obtenerUsuario($login);
        $estadisticas = $orm->obtenerEstadisticas($login);
        echo \dawfony\Ti::render("view/PerfilView.phtml", compact('title', 'enLobby', 'usuario', 'estadisticas'));
    }

    function cambiarAvatar($login, $imgAntigua) {
        global $URL_PATH;
        $orm = new OrmUser;
        $imgNueva = $_FILES["avatar"];
        $imgNueva["name"] = cambiarNombreAvatar($imgNueva["name"], $login);
        if ($imgAntigua != "anonimus.jpg") {
            actualizarAvatar($imgAntigua, $imgNueva);
        } else {
            $orm->modificarAvatar($login, $imgNueva["name"]);
            guardarAvatar($imgNueva);
        }        
        header("Location: $URL_PATH/perfil/$login");
    }
}