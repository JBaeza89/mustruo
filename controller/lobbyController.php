<?php
namespace controller;
use orm\OrmUser;
use orm\OrmMesa;
class LobbyController extends Controller {
    function lobby($arg = "principal") {
        
        if (isset($_SESSION["login"])) {
            $orm = new OrmUser;
            $usuario = $orm->obtenerUsuario($_SESSION["login"]);
            $estado = $orm->comprobarEstadoUsuario($usuario->login);            
            $usuario->estaSentado = $estado["posicion"] >= 0;
            $usuario->posicion = $estado["posicion"];
            $usuario->mesa_id = $usuario->estaSentado ? $estado["mesa_id"] : -1;
            if ($usuario->estaSentado) {
                $orm = new OrmMesa;
                if ($orm->comprobarEstadoMesa($usuario->mesa_id)) {
                    global $URL_PATH;
                    header("Location: $URL_PATH/mesa/$usuario->mesa_id");
                }
            }            
        } else {
            $usuario = "anonimo";
        }
        $title = "Lobby";     

        echo \dawfony\Ti::render("view/LobbyView.phtml", compact('title', 'usuario', 'arg'));
    }
}