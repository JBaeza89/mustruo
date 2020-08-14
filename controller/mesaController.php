<?php

namespace controller;

use orm\OrmMesa;
use objects\Mesa;
use objects\Usuario;
use objects\Carta;


class MesaController extends Controller
{
    function partida($id_mesa)
    {
        global $URL_PATH;
        $title = "Mesa $id_mesa";
        $orm = new OrmMesa;
        if ($orm->existeMesa($id_mesa)) {
            $parejaA = [];
            $parejaB = []; 
            $mesa = $orm->obtenerMesa($id_mesa);
            $usuariosMesa = $orm->obtenerUsuariosMesa($id_mesa);
            for ($i = 0; $i < 4; $i++) {
                if ($usuariosMesa[$i]["login"] == $_SESSION["login"]) {
                    $usuario = new Usuario;
                    $usuario->login = $usuariosMesa[$i]["login"];
                    $usuario->posicion = $usuariosMesa[$i]["posicion"];
                    $usuario->imagen = $usuariosMesa[$i]["imagen"];
                    if ($usuario->posicion % 2 == 0) {
                        array_push($parejaA, $usuario->login);
                    } else {
                        array_push($parejaB, $usuario->login);
                    }
                    break;
                }
            }
            if (!isset($usuario)) {
                header("Location: $URL_PATH");
            }
            foreach ($usuariosMesa as $usu) {
                if ($usu["login"] != $usuario->login) {
                    if (($usu["posicion"] + $usuario->posicion) % 2 == 0) {
                        $compannero = new Usuario;
                        $compannero->login = $usu["login"];
                        $compannero->posicion = $usu["posicion"];
                        $compannero->imagen = $usu["imagen"];
                        if ($compannero->posicion % 2 == 0) {
                            array_push($parejaA, $compannero->login);
                        } else {
                            array_push($parejaB, $compannero->login);
                        }
                    } else if (($usuario->posicion + 1) % 4 == $usu["posicion"]) {
                        $rivalDer = new Usuario;
                        $rivalDer->login = $usu["login"];
                        $rivalDer->posicion = $usu["posicion"];
                        $rivalDer->imagen = $usu["imagen"];
                        if ($rivalDer->posicion % 2 == 0) {
                            array_push($parejaA, $rivalDer->login);
                        } else {
                            array_push($parejaB, $rivalDer->login);
                        }
                    } else {
                        $rivalIzq = new Usuario;
                        $rivalIzq->login = $usu["login"];
                        $rivalIzq->posicion = $usu["posicion"];
                        $rivalIzq->imagen = $usu["imagen"];
                        if ($rivalIzq->posicion % 2 == 0) {
                            array_push($parejaA, $rivalIzq->login);
                        } else {
                            array_push($parejaB, $rivalIzq->login);
                        }
                    }
                }
            }
            $cartas = $orm->obtenerCartas($id_mesa, $usuario->posicion);
            $marcador = $orm->obtenerMarcador($id_mesa);
            $situacionEntrada = $orm->obtenerSituacionActual($id_mesa);
            echo \dawfony\Ti::render("view/MesaView.phtml", compact('title', 'usuario', 'compannero', 'rivalIzq', 'rivalDer', 'mesa', 'cartas', 'marcador', 'situacionEntrada', 'parejaA', 'parejaB'));
        
        } else {
            header("Location: $URL_PATH");
        }
    }

    function crearMesa()
    {
        global $URL_PATH;
        $orm = new OrmMesa;
        $mesa = new Mesa;
        $mesa->fecha = date('Y-m-d H:i:s');
        $mesa->privacidad_id = $_POST["privacidad"];
        $mesa->contrasenna = $mesa->privacidad_id == 1 ? "" : $_POST["contrasenna"];
        $mesa->juegos =  $_POST["juegos"];
        $mesa->vacas =  $_POST["vacas"];
        $mesa->puntos =  $_POST["puntos"];
        $mesa->login = $_POST["creador"];
        $orm->crearMesa($mesa);
        header("Location: $URL_PATH/mc");
    }
}
