<?php

namespace controller;

use \orm\OrmUser;
use \orm\OrmMesa;
use objects\Mesa;

class ApiController extends Controller
{
    function comprobarLogin($login)
    {
        $orm = new OrmUser;
        $existe = $orm->existeLogin($login);
        echo $existe ? "si" : "no";
    }

    function comprobarSesion($login, $contrasenna)
    {
        $orm = new OrmUser;
        $error = false;
        if ($orm->existeLogin($login)) {
            $contrasennaValida = $orm->recibirContrasenna($login);
            if (password_verify($contrasenna, $contrasennaValida["contrasenna"])) {
                $_SESSION["login"] = $login;
                $_SESSION["rol"] = $login == "admin" ? 0 : 1;
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }
        echo $error ? "si" :  "no";
    }

    function obtenerEstadisticas($login)
    {
        header('Content-type: application/json');
        $orm = new OrmUser;
        echo json_encode($orm->obtenerEstadisticas($login));
    }

    function obtenerRanking($tipo)
    {
        header('Content-type: application/json');
        $orm = new OrmUser;
        echo json_encode($orm->obtenerRanking($tipo));
    }

    function obtenerUsuariosMesa($id)
    {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        echo json_encode($orm->obtenerUsuariosMesa($id));
    }

    function obtenerMesas()
    {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        echo json_encode($orm->obtenerMesas());
    }

    function sentarseEnMesa($id, $pos, $login)
    {
        if ($login == $_SESSION["login"]) {
            $orm = new OrmMesa;
            return $orm->sentarseEnMesa($id, $pos, $login);
        }
    }

    function levantarseDeLaMesa($id, $pos, $login)
    {
        $orm = new OrmMesa;
        if ($_SESSION["login"] == $login && $orm->levantarseDeLaMesa($id, $pos, $login)) {
            echo "ok";
        } else {
            echo "te reviento";
        }
    }

    function comprobarEstadoUsuario($login)
    {
        header('Content-type: application/json');
        $orm = new OrmUser;
        echo json_encode($orm->comprobarEstadoUsuario($login));
    }
    function cambiarEmail($login, $emailCambio)
    {
        $orm = new OrmUser;
        if ($orm->modificarEmail($login, $emailCambio)) {
            echo 1;
        } else {
            echo 0;
        }
    }

    function cambiarContrasenna($login, $contrasenna)
    {
        $orm = new OrmUser;
        $contrasenna = password_hash($contrasenna, PASSWORD_DEFAULT);
        if ($orm->modificarContrasenna($login, $contrasenna)) {
            echo 1;
        } else {
            echo 2;
        }
    }

    public function cambiarEstadoPartida($id, $estado = 1)
    {
        $orm = new OrmMesa;
        if ($orm->cambiarEstadoPartida($id, $estado)) {
            echo "ok";
        } else {
            echo "nook";
        }
    }

    public function pedirMus($id, $login)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $orm = new OrmMesa;
            $situacion = $orm->obtenerSituacionActual($id);
            if ($situacion["turno"] == 3) {
                if ($orm->iniciarDescartes($id)) {
                    $situacion = $orm->obtenerSituacionActual($id);
                    echo json_encode($situacion);
                } else {
                    $error = "fallan descartes";
                    echo json_encode($error);
                }
            } else {
                $situacion["turno"]++;
                if ($orm->actualizarSituacion($id, $situacion["turno"])) {
                    echo json_encode($situacion);
                } else {
                    $error = "falla actualizacion";
                    echo json_encode($error);
                }
            }
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function descartar($id, $login, $descartes)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $descartes = explode("+", $descartes);
            $nDescartes = $descartes[count($descartes) - 1];
            $orm = new OrmMesa;
            $situacion = $orm->obtenerSituacionActual($id);
            $cartas = $orm->darMus($id, $login, $descartes);
            if ($situacion["turno"] == 3) {
                if ($orm->volverAMenuMus($id)) {
                    $situacion = $orm->obtenerSituacionActual($id);
                    $situacion["cartas"] = $cartas;
                    $situacion["descartes"] = $nDescartes;
                    echo json_encode($situacion);
                } else {
                    $error = "falla vuelta al mus";
                    echo json_encode($error);
                }
            } else {
                $situacion["turno"]++;
                if ($orm->actualizarSituacion($id, $situacion["turno"])) {
                    $situacion["cartas"] = $cartas;
                    $situacion["descartes"] = $nDescartes;
                    echo json_encode($situacion);
                } else {
                    $error = "falla actualizacion";
                    echo json_encode($error);
                }
            }
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function noHayMus($id, $login)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $orm = new OrmMesa;
            if ($orm->noHayMus($id)) {
                $situacion = $orm->obtenerSituacionActual($id);
                echo json_encode($situacion);
            } else {
                $error = "falla el paso a grande";
                echo json_encode($error);
            }
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function envidar($id, $login, $envite)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $orm = new OrmMesa;
            $situacion = $orm->obtenerSituacionActual($id);
            if ($orm->envidar($situacion, $login, $envite)) {
                $situacion = $orm->obtenerSituacionActual($id);
                echo json_encode($situacion);
            } else {
                $error = "falla el envite";
                echo json_encode($error);
            }
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function pasar($id, $login)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $orm = new OrmMesa;
            $situacion = $orm->obtenerSituacionActual($id);
            if ($situacion["turno"] == 3) {
                if ($orm->siguienteJugada($situacion)) {
                    $situacion = $orm->obtenerSituacionActual($id);
                    echo json_encode($situacion);
                } else {
                    $error = "falla cambio jugada";
                    echo json_encode($error);
                }
            } else {
                $situacion["turno"]++;
                if ($orm->actualizarSituacion($id, $situacion["turno"])) {
                    echo json_encode($situacion);
                } else {
                    $error = "falla actualizacion";
                    echo json_encode($error);
                }
            }
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function echarOrdago($id, $login)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $orm = new OrmMesa;
            $situacion = $orm->obtenerSituacionActual($id);
            if ($orm->echarOrdago($situacion, $login)) {
                $situacion = $orm->obtenerSituacionActual($id);
                echo json_encode($situacion);
            } else {
                $error = "falla el ordago";
                echo json_encode($error);
            }
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function noQuerer($id, $login)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $orm = new OrmMesa;
            $situacion = $orm->obtenerSituacionActual($id);
            if ($situacion["turno"] + 2 > 3) {
                if ($orm->anotarPuntos($situacion)) {
                    $situacion = $orm->obtenerSituacionActual($id);
                    $situacion["marcadores"] = $orm->obtenerMarcador($id);
                    echo json_encode($situacion);
                } else {
                    $error = "fallan cambio jugada o puntos";
                    echo json_encode($error);
                }
            } else {
                $situacion["turno"] += 2;
                if ($orm->actualizarSituacion($id, $situacion["turno"])) {
                    echo json_encode($situacion);
                } else {
                    $error = "falla actualizacion";
                    echo json_encode($error);
                }
            }
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function querer($id, $login)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $orm = new OrmMesa;
            $situacion = $orm->obtenerSituacionActual($id);
            if ($situacion["estado"] == "envite") {
                if ($orm->quererEnvite($situacion)) {
                    $situacion = $orm->obtenerSituacionActual($id);
                    echo json_encode($situacion);
                } else {
                    $error = "falla aceptacion envite";
                    echo json_encode($error);
                }
            }
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function reenvidar($id, $login, $envite)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $orm = new OrmMesa;
            $situacion = $orm->obtenerSituacionActual($id);
            if ($orm->reenvidar($situacion, $login, $envite)) {
                $situacion = $orm->obtenerSituacionActual($id);
                echo json_encode($situacion);
            } else {
                $error = "falla el envite";
                echo json_encode($error);
            }
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function comprobarParesYJuego($id, $login)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $orm = new OrmMesa;
            $situacion = $orm->obtenerSituacionActual($id);
            if ($situacion["jugada"] == "pares") {
                $situacion = $orm->ActualizarPares($situacion);
            } else {
                $situacion = $orm->ActualizarJuego($situacion);
            }
            echo json_encode($situacion);
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function verificarParJuego($id, $login)
    {
        header('Content-type: application/json');
        if ($_SESSION["login"] == $login) {
            $orm = new OrmMesa;
            $situacion = $orm->obtenerSituacionActual($id);
            $posicion = ($situacion["mano"] + $situacion["turno"]) % 4;
            $situacion["comprobacion"] = $situacion["jugada"] == "pares" ? $orm->hayPares($situacion["mesa_id"], $posicion) :
                $orm->hayJuego($situacion["mesa_id"], $posicion);
            echo json_encode($situacion);
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function repartirNuevaMano($id) {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        $situacion = $orm->obtenerSituacionActual($id);
        if ($situacion["estado"] == "repartir") {
            $orm->recogerCartas($id);
            $orm->empezarMano($id, $situacion["mano"]);
            $situacion = $orm->obtenerSituacionActual($id);
            echo json_encode($situacion);
        } else {
            $error = "reparto incorrecto";
            echo json_encode($error);
        }
    }

    public function verMisCartas($id, $login) {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        if ($_SESSION["login"] == $login) {
            $cartas = $orm->obtenerCartas($id, $orm->obtenerPosicion($login));
            echo json_encode($cartas);
        } else {
            $error = "no coincide usuario";
            echo json_encode($error);
        }
    }

    public function obtenerCartas($id, $control) {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        if ($control == "control") {
            $cartas = [];
            for ($i = 0; $i < 4; $i++) {
                $cartas[$i] = $orm->obtenerCartas($id, $i);
            }
            $cartas[4] = $id;
            echo json_encode($cartas);
        } else {
            $error = "peticion no ejecutada desde servidor";
            echo json_encode($error);
        }
    }

    public function adelantarMano($id, $control) {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        if ($control == "control") {            
            $situacion = $orm->adelantarMano($id);
            echo json_encode($situacion);
        } else {
            $error = "peticion no ejecutada desde servidor";
            echo json_encode($error);
        }
    }

    public function resolverOrdago($id, $control) {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        if ($control == "control") {
            $situacion = $orm->obtenerSituacionActual($id);
            $resultado = $orm->resolverOrdago($situacion);            
            echo json_encode($resultado);
        } else {
            $error = "peticion no ejecutada desde servidor";
            echo json_encode($error);
        }
    }

    public function resolverGrande($id, $control) {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        if ($control == "control") {
            $situacion = $orm->obtenerSituacionActual($id);
            $resultado= $orm->resolverGrande($situacion);            
            echo json_encode($resultado);
        } else {
            $error = "peticion no ejecutada desde servidor";
            echo json_encode($error);
        }
    }

    public function resolverChica($id, $control) {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        if ($control == "control") {
            $situacion = $orm->obtenerSituacionActual($id);
            $resultado= $orm->resolverChica($situacion);            
            echo json_encode($resultado);
        } else {
            $error = "peticion no ejecutada desde servidor";
            echo json_encode($error);
        }
    }

    public function resolverPares($id, $control) {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        if ($control == "control") {
            $situacion = $orm->obtenerSituacionActual($id);
            $resultado= $orm->resolverPares($situacion);            
            echo json_encode($resultado);
        } else {
            $error = "peticion no ejecutada desde servidor";
            echo json_encode($error);
        }
    }

    public function resolverJuego($id, $control) {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        if ($control == "control") {
            $situacion = $orm->obtenerSituacionActual($id);
            $resultado= $orm->resolverJuego($situacion);            
            echo json_encode($resultado);
        } else {
            $error = "peticion no ejecutada desde servidor";
            echo json_encode($error);
        }
    }

    public function resolverPunto($id, $control) {
        header('Content-type: application/json');
        $orm = new OrmMesa;
        if ($control == "control") {
            $situacion = $orm->obtenerSituacionActual($id);
            $resultado= $orm->resolverPunto($situacion);            
            echo json_encode($resultado);
        } else {
            $error = "peticion no ejecutada desde servidor";
            echo json_encode($error);
        }
    }

    public function abandonarMesa($id, $login) {
        if ($login == $_SESSION["login"]) {
            $orm = new OrmMesa;
            echo $orm->abandonarMesa($id, $login)? "ok" : "error al abandonar";
        } else {
            $error = "no coincide usuario";
            echo $error;
        }
    }

    public function prueba() {
        $orm = new OrmMesa;
        $mesa = $orm->obtenerMesa(2);
        $marcadores = $orm->obtenerMarcador(2);
        $marcadores = $orm->sumarYManipularMarcadores(40, $marcadores, $mesa, 2);
    }

    
}
