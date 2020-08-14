<?php

namespace orm;

use \dawfony\Klasto;
use objects\Mesa;
use objects\Marcador;

class OrmMesa
{
    private function generarBaraja($id)
    {
        $bd = Klasto::getInstance();
        $palos = ["Oro", "Copa", "Espada", "Basto"];
        $numeros = [1, 2, 3, 4, 5, 6, 7, 10, 11, 12];
        $valores = [1, 1, 12, 4, 5, 6, 7, 10, 11, 12];
        foreach ($palos as $palo) {
            for ($i = 0; $i < count($numeros); $i++) {
                $params = [$id, $numeros[$i], $palo, "$numeros[$i]_$palo.jpg", $valores[$i]];
                $sql = "INSERT INTO `cartas` (`mesa_id`, `numero`, `palo`, `imagen`, `valor`, `estado`) VALUES (?, ?, ?, ?, ?, 4)";
                $bd->execute($sql, $params);
            }
        }
    }

    public function repartirCartasIniciales($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "SELECT `mesa_id`, `numero`, `palo`, `imagen`, `valor`, `estado` FROM `cartas` WHERE `mesa_id` = ?";
        $baraja = $bd->query($sql, $params, "objects\Carta");
        if (shuffle($baraja)) {
            for ($i = 0; $i < 16; $i++) {
                $params = [$i % 4, $id, $baraja[$i]->numero, $baraja[$i]->palo];
                $sql = "UPDATE `cartas` SET `estado` = ? WHERE `mesa_id` = ? AND `numero` = ? AND `palo` = ?";
                $bd->execute($sql, $params);
            }
        }
    }

    public function empezarMano($id, $mano = 0)
    {
        $bd = Klasto::getInstance();
        $params = [$mano, $id];
        $sql = "UPDATE `jugadas` SET `mano` = ?, `turno` = 0, `estado` = 'menu', `jugada` = 'mus', `grande` = '-', `chica` = '-', `pares` = '-', `juego` = '-', `punto` = '-', `acumulado` = 0, `rechazo` = 0 WHERE mesa_id = ?";
        $bd->execute($sql, $params);
        $this->repartirCartasIniciales($id);
    }

    public function recogerCartas($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "UPDATE `cartas` SET `estado` = 4 WHERE `mesa_id` = ?";
        $bd->execute($sql, $params);
    }

    public function adelantarMano($id)
    {
        $bd = Klasto::getInstance();
        $situacion = $this->obtenerSituacionActual($id);
        $situacion["mano"] = ($situacion["mano"] + 1) % 4;
        $situacion["turno"] = 0;
        $situacion["estado"] = 'repartir';
        $situacion["jugada"] = 'mus';
        $params = [$situacion["estado"], $situacion["jugada"], $situacion["mano"], $situacion["turno"], $id];
        $sql = "UPDATE `jugadas` SET `estado` = ?, `jugada` = ?, `mano` = ?, `turno` = ? WHERE `mesa_id` = ?";
        $bd->execute($sql, $params);
        return $situacion;
    }

    public function generarMarcadores($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id, 0];
        $sql = "INSERT INTO `marcador` (`mesa_id`, `pareja_id`) VALUES (?, ?)";
        $bd->execute($sql, $params);
        $params = [$id, 1];
        $bd->execute($sql, $params);
    }

    public function crearMesa($mesa)
    {
        $bd = Klasto::getInstance();
        $bd->startTransaction();
        $params = [$mesa->fecha, $mesa->contrasenna, $mesa->privacidad_id, $mesa->vacas, $mesa->juegos, $mesa->puntos, $mesa->login];
        $sql = "INSERT INTO `mesa` (`fecha`, `contrasenna`, `privacidad_id`, `vacas`, `juegos`, `puntos`, `login`) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $bd->execute($sql, $params);
        $params = [$mesa->login, $mesa->fecha];
        $sql = "SELECT `id_mesa` FROM `mesa` WHERE `login` = ? AND `fecha` = ?";
        $mesa->id_mesa = $bd->queryOne($sql, $params)["id_mesa"];
        $params = [$mesa->login, $mesa->id_mesa];
        $sql = "INSERT INTO `usuarios_por_mesa` (`login`, `mesa_id`, `pareja_id`, `posicion`) VALUES (?, ?, 0, 0)";
        $bd->execute($sql, $params);
        $params = [$mesa->id_mesa, 0, 0];
        $sql = "INSERT INTO `jugadas` (`mesa_id`, `mano`, `turno`) VALUES(?, ?, ?)";
        $bd->execute($sql, $params);
        $this->generarBaraja($mesa->id_mesa);
        $this->generarMarcadores($mesa->id_mesa);
        $this->repartirCartasIniciales($mesa->id_mesa);
        $bd->commit();
    }

    public function obtenerUsuariosMesa($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "SELECT usuarios_por_mesa.login, usuarios_por_mesa.posicion, usuario.imagen FROM `usuarios_por_mesa`" .
            "INNER JOIN usuario ON usuarios_por_mesa.login = usuario.login WHERE `mesa_id` = ?";
        return $bd->query($sql, $params);
    }

    public function obtenerMesas()
    {
        $bd = Klasto::getInstance();
        $params = [];
        $sql = "SELECT `id_mesa`, `fecha`, `contrasenna`, `privacidad_id`, `vacas`, `juegos`, `puntos`, `login` FROM `mesa`";
        return $bd->query($sql, $params, "objects\Mesa");
    }

    public function obtenerMesa($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "SELECT `id_mesa`, `fecha`, `contrasenna`, `privacidad_id`, `vacas`, `juegos`, `puntos`, `login` FROM `mesa` WHERE `id_mesa` = ?";
        return $bd->queryOne($sql, $params, "objects\Mesa");
    }

    public function sentarseEnMesa($id, $pos, $login)
    {
        $bd = Klasto::getInstance();
        $params = [$login, $id, $pos % 2, $pos];
        $sql = "INSERT INTO `usuarios_por_mesa` (`login`, `mesa_id`, `pareja_id`, `posicion`) VALUES (?, ?, ?, ?)";
        if ($bd->execute($sql, $params)) {
            return $bd->queryOne("SELECT imagen FROM usuario WHERE login = ?", [$login])[0];
        }
        return "nook";
    }

    public function eliminarMesa($id)
    {
        $bd = Klasto::getInstance();
        $bd->startTransaction();
        $params = [$id];
        $sql = "DELETE FROM `usuarios_por_mesa` WHERE `mesa_id` = ?";
        $sql2 = "DELETE FROM `cartas` WHERE `mesa_id` = ?";
        $sql3 = "DELETE FROM `marcador` WHERE `mesa_id` = ?";
        $sql4 = "DELETE FROM `jugadas` WHERE `mesa_id` = ?";
        $sql5 = "DELETE FROM `mesa` WHERE `id_mesa` = ?";
        $todoOk = $bd->execute($sql, $params) && $bd->execute($sql2, $params) && $bd->execute($sql3, $params) && $bd->execute($sql4, $params) && $bd->execute($sql5, $params);
        $bd->commit();
        return $todoOk;
    }

    public function levantarseDeLaMesa($id, $pos, $login)
    {
        $bd = Klasto::getInstance();
        if ($pos == 0) {
            return $this->eliminarMesa($id);
        }
        $params = [$login];
        $sql = "DELETE FROM `usuarios_por_mesa` WHERE `login` = ?";
        return $bd->execute($sql, $params);
    }

    public function comprobarEstadoMesa($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "SELECT `estado` FROM `mesa` WHERE id_mesa = ?";
        return $bd->queryOne($sql, $params)["estado"];
    }

    public function cambiarEstadoPartida($id, $estado)
    {
        $bd = Klasto::getInstance();
        $params = [$estado, $id];
        $sql = "UPDATE `mesa` SET estado = ? WHERE id_mesa = ?";
        return $bd->execute($sql, $params);
    }

    public function existeMesa($id)
    {
        return $this->comprobarEstadoMesa($id) == 1;
    }

    public function obtenerCartas($id, $estado)
    {
        $bd = Klasto::getInstance();
        $params = [$id, $estado];
        $sql = "SELECT `mesa_id`, `numero`, `palo`, `imagen`, `valor`, `estado` FROM `cartas` WHERE `mesa_id` = ? AND `estado` = ? ORDER BY `valor` DESC";
        return $bd->query($sql, $params, "objects\Carta");
    }

    public function obtenerMarcador($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "SELECT `pareja_id`, `puntos`, `juegos`, `vacas` FROM `marcador` WHERE `mesa_id` = ? ORDER BY `pareja_id` ASC";
        return $bd->query($sql, $params, "objects\Marcador");
    }

    public function obtenerSituacionActual($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "SELECT `mesa_id`, `mano`, `estado`, `turno`, `jugada`, `grande`, `chica`, `pares`, `juego`, `punto`, `acumulado`, `rechazo` FROM `jugadas` WHERE `mesa_id` = ?";
        return $bd->queryOne($sql, $params);
    }

    public function iniciarDescartes($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "UPDATE `jugadas` SET `estado` = 'descartando', `turno` = 0 WHERE `mesa_id` = ?";
        return $bd->execute($sql, $params);
    }

    public function actualizarSituacion($id, $turno)
    {
        $bd = Klasto::getInstance();
        $params = [$turno, $id];
        $sql = "UPDATE `jugadas` SET `turno` = ? WHERE `mesa_id` = ?";
        return $bd->execute($sql, $params);
    }

    public function volverAMenuMus($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "UPDATE `jugadas` SET `estado` = 'menu', `turno` = 0 WHERE `mesa_id` = ?";
        return $bd->execute($sql, $params);
    }

    public function obtenerPosicion($login)
    {
        $bd = Klasto::getInstance();
        $params = [$login];
        $sql = "SELECT `posicion` FROM `usuarios_por_mesa` WHERE `login` = ?";
        return $bd->queryOne($sql, $params)["posicion"];
    }

    private function repartirCartasMus($id, $posicion, $nDescartes)
    {
        $bd = Klasto::getInstance();
        $params = [$id, 4];
        $sql = "SELECT `mesa_id`, `numero`, `palo`, `imagen`, `valor`, `estado` FROM `cartas` WHERE `mesa_id` = ? AND `estado` = ?";
        $baraja = $bd->query($sql, $params, "objects\Carta");
        if (shuffle($baraja)) {
            if (count($baraja) >= $nDescartes) {
                for ($i = 0; $i < $nDescartes; $i++) {
                    $params = [$posicion, $id, $baraja[$i]->numero, $baraja[$i]->palo];
                    $sql = "UPDATE `cartas` SET `estado` = ? WHERE `mesa_id` = ? AND `numero` = ? AND `palo` = ?";
                    $bd->execute($sql, $params);
                }
            } else {
                for ($i = 0; $i < count($baraja); $i++) {
                    $params = [$posicion, $id, $baraja[$i]->numero, $baraja[$i]->palo];
                    $sql = "UPDATE `cartas` SET `estado` = ? WHERE `mesa_id` = ? AND `numero` = ? AND `palo` = ?";
                    $bd->execute($sql, $params);
                }
                $nDescartes -= count($baraja);
                $params = [$id];
                $sql = "UPDATE `cartas` SET `estado` = 4 WHERE mesa_id = ? AND `estado` = 5";
                $bd->execute($sql, $params);
                $this->repartirCartasMus($id, $posicion, $nDescartes);
            }
        }
    }

    public function darMus($id, $login, $descartes)
    {
        $nDescartes = $descartes[count($descartes) - 1];
        $posicion = $this->obtenerPosicion($login);
        $bd = Klasto::getInstance();
        if ($nDescartes > 0) {
            for ($i = 0; $i < $nDescartes; $i++) {
                $params = [$id, $descartes[$i]];
                $sql = "UPDATE `cartas` SET `estado` = 5 WHERE mesa_id = ? AND `imagen` = ?";
                $bd->execute($sql, $params);
            }
            $this->repartirCartasMus($id, $posicion, $nDescartes);
        }
        return $this->obtenerCartas($id, $posicion);
    }

    public function noHayMus($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "UPDATE `jugadas` SET `estado` = 'limpio', `turno` = 0, `jugada` = 'grande' WHERE `mesa_id` = ?";
        return $bd->execute($sql, $params);
    }

    public function envidar($situacion, $login, $envite)
    {
        $turno = ($situacion["turno"] + 1) % 2;
        $bd = Klasto::getInstance();
        $texto = "$login envida $envite";
        $params = [$turno, $texto, $envite, $situacion["mesa_id"]];
        switch ($situacion["jugada"]) {
            case 'grande':
                $sql = "UPDATE `jugadas` SET `estado` = 'envite', `turno` = ?, `grande` = ?, `acumulado` = ?, `rechazo` = 1 WHERE `mesa_id` = ?";
                break;
            case 'chica':
                $sql = "UPDATE `jugadas` SET `estado` = 'envite', `turno` = ?, `chica` = ?, `acumulado` = ?, `rechazo` = 1 WHERE `mesa_id` = ?";
                break;
            case 'pares':
                $sql = "UPDATE `jugadas` SET `estado` = 'envite', `turno` = ?, `pares` = ?, `acumulado` = ?, `rechazo` = 1 WHERE `mesa_id` = ?";
                break;
            case 'juego':
                $sql = "UPDATE `jugadas` SET `estado` = 'envite', `turno` = ?, `juego` = ?, `acumulado` = ?, `rechazo` = 1 WHERE `mesa_id` = ?";
                break;
            default:
                $sql = "UPDATE `jugadas` SET `estado` = 'envite', `turno` = ?, `punto` = ?, `acumulado` = ?, `rechazo` = 1 WHERE `mesa_id` = ?";
        }
        return $bd->execute($sql, $params);
    }

    public function siguienteJugada($situacion)
    {
        $bd = Klasto::getInstance();
        $params = [$situacion["mesa_id"]];
        switch ($situacion["jugada"]) {
            case 'grande':
                $sql = "UPDATE `jugadas` SET `estado` = 'limpio', `turno` = 0, `jugada` = 'chica', `acumulado` = 0, `rechazo` = 0 WHERE `mesa_id` = ?";
                break;
            case 'chica':
                $sql = "UPDATE `jugadas` SET `estado` = 'comprobando', `turno` = 0, `jugada` = 'pares', `acumulado` = 0, `rechazo` = 0 WHERE `mesa_id` = ?";
                break;
            case 'pares':
                $sql = "UPDATE `jugadas` SET `estado` = 'comprobando', `turno` = 0, `jugada` = 'juego', `acumulado` = 0, `rechazo` = 0 WHERE `mesa_id` = ?";
                break;
            default:
                $params = [$situacion["mesa_id"]];
                $sql = "UPDATE `jugadas` SET `estado` = 'repartir', `turno` = 0, `jugada` = 'mus', `acumulado` = 0, `rechazo` = 0 WHERE `mesa_id` = ?";
        }
        return $bd->execute($sql, $params);
    }

    public function echarOrdago($situacion, $login)
    {
        $turno = ($situacion["turno"] + 1) % 2;
        $bd = Klasto::getInstance();
        $texto = "Ordago de $login";
        if ($situacion["acumulado"] == 0) {
            $situacion["acumulado"]++;
        }
        $params = [$turno, $texto, $situacion["acumulado"], $situacion["mesa_id"]];
        switch ($situacion["jugada"]) {
            case 'grande':
                $sql = "UPDATE `jugadas` SET `estado` = 'ordago', `turno` = ?, `grande` = ?, `rechazo` = ? WHERE `mesa_id` = ?";
                break;
            case 'chica':
                $sql = "UPDATE `jugadas` SET `estado` = 'ordago', `turno` = ?, `chica` = ?, `rechazo` = ? WHERE `mesa_id` = ?";
                break;
            case 'pares':
                $sql = "UPDATE `jugadas` SET `estado` = 'ordago', `turno` = ?, `pares` = ?, `rechazo` = ? WHERE `mesa_id` = ?";
                break;
            case 'juego':
                $sql = "UPDATE `jugadas` SET `estado` = 'ordago', `turno` = ?, `juego` = ?, `rechazo` = ? WHERE `mesa_id` = ?";
                break;
            default:
                $sql = "UPDATE `jugadas` SET `estado` = 'ordago', `turno` = ?, `punto` = ?, `rechazo` = ? WHERE `mesa_id` = ?";
        }
        return $bd->execute($sql, $params);
    }

    private function annadirAEstadisticas($id, $campo, $pareja)
    {
        $bd = Klasto::getInstance();
        $usuariosMesa = $this->obtenerUsuariosMesa($id);
        if ($campo == "juegos") {
            foreach ($usuariosMesa as $usu) {
                $params = [$usu["login"]];
                $sql = "UPDATE `estadisticas` SET `juegos_jugados` = `juegos_jugados` + 1 WHERE `login` = ?";
                $bd->execute($sql, $params);
                if ($pareja == $usu["posicion"] % 2) {
                    $params = [$usu["login"]];
                    $sql = "UPDATE `estadisticas` SET `juegos_ganados` = `juegos_ganados` + 1 WHERE `login` = ?";
                    $bd->execute($sql, $params);
                }
            }
        } else if ($campo == "vacas") {
            foreach ($usuariosMesa as $usu) {
                $params = [$usu["login"]];
                $sql = "UPDATE `estadisticas` SET `vacas_jugadas` = `vacas_jugadas` + 1 WHERE `login` = ?";
                $bd->execute($sql, $params);
                if ($pareja == $usu["posicion"] % 2) {
                    $params = [$usu["login"]];
                    $sql = "UPDATE `estadisticas` SET `vacas_ganadas` = `vacas_ganadas` + 1 WHERE `login` = ?";
                    $bd->execute($sql, $params);
                }
            }
        }
    }

    private function sumarYManipularMarcadores($puntos, $marcador, $mesa, $pareja)
    {
        if ($marcador[$pareja]->puntos + $puntos >= $mesa->puntos) {
            $marcador[$pareja]->puntos = 0;
            $marcador[($pareja + 1) % 2]->puntos = 0;
            $this->annadirAEstadisticas($mesa->id_mesa, "juegos", $pareja);
            if ($marcador[$pareja]->juegos + 1 == $mesa->juegos) {
                $marcador[$pareja]->juegos = 0;
                $marcador[($pareja + 1) % 2]->juegos = 0;
                $this->annadirAEstadisticas($mesa->id_mesa, "vacas", $pareja);
                if ($marcador[$pareja]->vacas + 1 == $mesa->vacas) {
                    $marcador[$pareja]->vacas = 77;
                    $marcador[($pareja + 1) % 2]->vacas = 0;
                } else {
                    $marcador[$pareja]->vacas++;
                }
            } else {
                $marcador[$pareja]->juegos++;
            }
        } else {
            $marcador[$pareja]->puntos += $puntos;
        }
        return $marcador;
    }

    private function cerrarJugada($situacion)
    {
        $bd = Klasto::getInstance();
        $pareja = ($situacion["turno"] + $situacion["mano"] + 1) % 2 == 0 ? "A" : "B";
        $params = [$situacion["mesa_id"]];
        switch ($situacion["jugada"]) {
            case 'grande':
                $sql = "UPDATE `jugadas` SET `grande` = 'X' WHERE `mesa_id` = ?";
                break;
            case 'chica':
                $sql = "UPDATE `jugadas` SET `chica` = 'X' WHERE `mesa_id` = ?";
                break;
            case 'pares':
                $params = [$pareja, $situacion["mesa_id"]];
                $sql = "UPDATE `jugadas` SET `pares` = ? WHERE `mesa_id` = ?";
                break;
            case 'juego':
                $params = [$pareja, $situacion["mesa_id"]];
                $sql = "UPDATE `jugadas` SET `juego` = ? WHERE `mesa_id` = ?";
                break;
            default:
                $params = [$pareja, $situacion["mesa_id"]];
                $sql = "UPDATE `jugadas` SET `punto` = ? WHERE `mesa_id` = ?";
        }
        return $bd->execute($sql, $params);
    }

    public function anotarPuntos($situacion)
    {
        $bd = Klasto::getInstance();
        $pareja = ($situacion["turno"] + $situacion["mano"] + 1) % 2;
        $marcadores = $this->obtenerMarcador($situacion["mesa_id"]);
        $mesa = $this->obtenerMesa($situacion["mesa_id"]);
        $marcadores = $this->sumarYManipularMarcadores($situacion["rechazo"], $marcadores, $mesa, $pareja);
        $todoOk = false;
        $bd->startTransaction();
        foreach ($marcadores as $marcador) {
            $params = [$marcador->puntos, $marcador->juegos, $marcador->vacas, $situacion["mesa_id"], $marcador->pareja_id];
            $sql = "UPDATE `marcador` SET `puntos` = ?, `juegos` = ?, `vacas` = ? WHERE `mesa_id` = ? AND `pareja_id` = ?";
            $todoOk = $todoOk || $bd->execute($sql, $params);
        }
        $todoOk = $todoOk && $this->cerrarJugada($situacion) && $this->siguienteJugada($situacion);

        $bd->commit();
        return $todoOk;
    }

    public function quererEnvite($situacion)
    {
        $bd = Klasto::getInstance();
        $params = [$situacion["acumulado"], $situacion["mesa_id"]];
        switch ($situacion["jugada"]) {
            case 'grande':
                $sql = "UPDATE `jugadas` SET `grande` = ? WHERE `mesa_id` = ?";
                break;
            case 'chica':
                $sql = "UPDATE `jugadas` SET `chica` = ? WHERE `mesa_id` = ?";
                break;
            case 'pares':
                $sql = "UPDATE `jugadas` SET `pares` = ? WHERE `mesa_id` = ?";
                break;
            case 'juego':
                $sql = "UPDATE `jugadas` SET `juego` = ? WHERE `mesa_id` = ?";
                break;
            default:
                $sql = "UPDATE `jugadas` SET `punto` = ? WHERE `mesa_id` = ?";
        }
        $bd->startTransaction();
        $todoOk = $bd->execute($sql, $params) && $this->siguienteJugada($situacion);
        $bd->commit();
        return $todoOk;
    }

    public function reenvidar($situacion, $login, $envite)
    {
        $turno = ($situacion["turno"] + 1) % 2;
        $bd = Klasto::getInstance();
        $envite += $situacion["acumulado"];
        $texto = "$login sube a $envite";
        $params = [$turno, $texto, $envite, $situacion["acumulado"], $situacion["mesa_id"]];
        switch ($situacion["jugada"]) {
            case 'grande':
                $sql = "UPDATE `jugadas` SET `turno` = ?, `grande` = ?, `acumulado` = ?, `rechazo` = ? WHERE `mesa_id` = ?";
                break;
            case 'chica':
                $sql = "UPDATE `jugadas` SET `turno` = ?, `chica` = ?, `acumulado` = ?, `rechazo` = ? WHERE `mesa_id` = ?";
                break;
            case 'pares':
                $sql = "UPDATE `jugadas` SET `turno` = ?, `pares` = ?, `acumulado` = ?, `rechazo` = ? WHERE `mesa_id` = ?";
                break;
            case 'juego':
                $sql = "UPDATE `jugadas` SET `turno` = ?, `juego` = ?, `acumulado` = ?, `rechazo` = ? WHERE `mesa_id` = ?";
                break;
            default:
                $sql = "UPDATE `jugadas` SET `turno` = ?, `punto` = ?, `acumulado` = ?, `rechazo` = ? WHERE `mesa_id` = ?";
        }
        return $bd->execute($sql, $params);
    }

    public function tienePares($cartas)
    {
        return $cartas[0]->valor == $cartas[1]->valor || $cartas[1]->valor == $cartas[2]->valor ||
            $cartas[2]->valor == $cartas[3]->valor;
    }

    public function hayPares($id, $posicion)
    {
        $cartas = $this->obtenerCartas($id, $posicion);
        return  $cartas[0]->valor == $cartas[1]->valor || $cartas[1]->valor == $cartas[2]->valor ||
            $cartas[2]->valor == $cartas[3]->valor;
    }

    public function actualizarPares($situacion)
    {
        $posicion = ($situacion["mano"] + $situacion["turno"]) % 4;
        $tienePares = $this->hayPares($situacion["mesa_id"], $posicion);
        if ($situacion["turno"] == 0) {
            $situacion["turno"]++;
            $situacion["pares"] = $tienePares ? "S" : "N";
            $sql = "UPDATE `jugadas` SET `turno` = ?, `pares` = ? WHERE `mesa_id` = ?";
        } else if ($situacion["turno"] == 3) {
            $situacion["turno"] = 0;
            $situacion["pares"] .= $tienePares ? "S" : "N";
            $jugable = ($situacion["pares"][0] == 'S' && $situacion["pares"][1] == 'S') ||
                ($situacion["pares"][0] == 'S' && $situacion["pares"][3] == 'S') ||
                ($situacion["pares"][2] == 'S' && $situacion["pares"][1] == 'S') ||
                ($situacion["pares"][2] == 'S' && $situacion["pares"][3] == 'S');
            if ($jugable) {
                $situacion["pares"] = "-";
                $situacion["estado"] = "limpio";
                $sql = "UPDATE `jugadas` SET `turno` = ?, estado = 'limpio', `pares` = ? WHERE `mesa_id` = ?";
            } else {
                $ganador = strpos($situacion["pares"], 'S');
                if ($ganador !== false) {
                    $ganador = ($ganador + $situacion["mano"]) % 2;
                    $situacion["pares"] = $ganador == 0 ? "A" : "B";
                } else {
                    $situacion["pares"] = "X";
                }
                $situacion["jugada"] = "juego";
                $sql = "UPDATE `jugadas` SET `turno` = ?, `jugada` = 'juego', `pares` = ? WHERE `mesa_id` = ?";
            }
        } else {
            $situacion["pares"] .= $tienePares ? "S" : "N";
            $situacion["turno"]++;
            $sql = "UPDATE `jugadas` SET `turno` = ?, `pares` = ? WHERE `mesa_id` = ?";
        }
        $bd = Klasto::getInstance();
        $params = [$situacion["turno"], $situacion["pares"], $situacion["mesa_id"]];
        $bd->execute($sql, $params);
        $situacion["comprobacion"] = $tienePares;
        return $situacion;
    }

    public function tieneJuego($cartas)
    {
        $suma = 0;
        foreach ($cartas as $carta) {
            $suma += $carta->valor > 10 ? 10 : $carta->valor;
        }
        return $suma > 30;
    }

    public function hayJuego($id, $posicion)
    {
        $cartas = $this->obtenerCartas($id, $posicion);
        $suma = 0;
        foreach ($cartas as $carta) {
            $suma += $carta->valor > 10 ? 10 : $carta->valor;
        }
        return $suma > 30;
    }

    public function actualizarJuego($situacion)
    {
        $posicion = ($situacion["mano"] + $situacion["turno"]) % 4;
        $tieneJuego = $this->hayJuego($situacion["mesa_id"], $posicion);
        if ($situacion["turno"] == 0) {
            $situacion["turno"]++;
            $situacion["juego"] = $tieneJuego ? "S" : "N";
            $sql = "UPDATE `jugadas` SET `turno` = ?, `juego` = ? WHERE `mesa_id` = ?";
        } else if ($situacion["turno"] == 3) {
            $situacion["turno"] = 0;
            $situacion["juego"] .= $tieneJuego ? "S" : "N";
            $jugable = ($situacion["juego"][0] == 'S' && $situacion["juego"][1] == 'S') ||
                ($situacion["juego"][0] == 'S' && $situacion["juego"][3] == 'S') ||
                ($situacion["juego"][2] == 'S' && $situacion["juego"][1] == 'S') ||
                ($situacion["juego"][2] == 'S' && $situacion["juego"][3] == 'S');
            if ($jugable) {
                $situacion["juego"] = "-";
                $situacion["punto"] = "X";
                $sql = "UPDATE `jugadas` SET `turno` = ?, estado = 'limpio', `juego` = ?, `punto` = 'X' WHERE `mesa_id` = ?";
            } else {
                $ganador = strpos($situacion["juego"], 'S');
                if ($ganador === false) {
                    $situacion["juego"] = "X";
                    $situacion["jugada"] = "punto";
                    $sql = "UPDATE `jugadas` SET `turno` = ?, `estado` = 'limpio', `jugada` = 'punto', `juego` = ? WHERE `mesa_id` = ?";
                } else {
                    $ganador = ($ganador + $situacion["mano"]) % 2;
                    $situacion["juego"] = $ganador == 0 ? "A" : "B";
                    $situacion["jugada"] = "mus";
                    $sql = "UPDATE `jugadas` SET `turno` = ?, `estado` = 'repartir', `jugada` = 'mus', `juego` = ?, `punto` = 'X' WHERE `mesa_id` = ?";
                }
            }
            $situacion["estado"] = "limpio";
        } else {
            $situacion["juego"] .= $tieneJuego ? "S" : "N";
            $situacion["turno"]++;
            $sql = "UPDATE `jugadas` SET `turno` = ?, `juego` = ? WHERE `mesa_id` = ?";
        }
        $bd = Klasto::getInstance();
        $params = [$situacion["turno"], $situacion["juego"], $situacion["mesa_id"]];
        $bd->execute($sql, $params);
        $situacion["comprobacion"] = $tieneJuego;
        return $situacion;
    }

    public function obtenerGanadorGrande($situacion)
    {
        for ($i = 0; $i < 4; $i++) {
            $cartas[$i] = $this->obtenerCartas($situacion["mesa_id"], $i);
        }
        $ganador = $situacion["mano"];
        for ($i = 1; $i < 4; $i++) {
            $jugador = ($situacion["mano"] + $i) % 4;
            if ($cartas[$jugador][0]->valor > $cartas[$ganador][0]->valor) {
                $ganador = $jugador;
            } else if ($cartas[$jugador][0]->valor == $cartas[$ganador][0]->valor) {
                if ($cartas[$jugador][1]->valor > $cartas[$ganador][1]->valor) {
                    $ganador = $jugador;
                } else if ($cartas[$jugador][1]->valor == $cartas[$ganador][1]->valor) {
                    if ($cartas[$jugador][2]->valor > $cartas[$ganador][2]->valor) {
                        $ganador = $jugador;
                    } else if (
                        $cartas[$jugador][2]->valor == $cartas[$ganador][2]->valor &&
                        $cartas[$jugador][3]->valor > $cartas[$ganador][3]->valor
                    ) {
                        $ganador = $jugador;
                    }
                }
            }
        }
        return $ganador % 2;
    }

    public function obtenerGanadorChica($situacion)
    {
        for ($i = 0; $i < 4; $i++) {
            $cartas[$i] = $this->obtenerCartas($situacion["mesa_id"], $i);
        }
        $ganador = $situacion["mano"];
        for ($i = 1; $i < 4; $i++) {
            $jugador = ($situacion["mano"] + $i) % 4;
            if ($cartas[$jugador][3]->valor < $cartas[$ganador][3]->valor) {
                $ganador = $jugador;
            } else if ($cartas[$jugador][3]->valor == $cartas[$ganador][3]->valor) {
                if ($cartas[$jugador][2]->valor < $cartas[$ganador][2]->valor) {
                    $ganador = $jugador;
                } else if ($cartas[$jugador][2]->valor == $cartas[$ganador][2]->valor) {
                    if ($cartas[$jugador][1]->valor < $cartas[$ganador][1]->valor) {
                        $ganador = $jugador;
                    } else if (
                        $cartas[$jugador][1]->valor == $cartas[$ganador][1]->valor &&
                        $cartas[$jugador][0]->valor < $cartas[$ganador][0]->valor
                    ) {
                        $ganador = $jugador;
                    }
                }
            }
        }
        return $ganador % 2;
    }

    public function obtenerValorPar($cartas)
    {
        if ($cartas[0]->valor == $cartas[1]->valor && $cartas[2]->valor == $cartas[3]->valor) {
            return 200 * $cartas[0]->valor + $cartas[3]->valor;
        }
        if ($cartas[0]->valor == $cartas[2]->valor || $cartas[1]->valor == $cartas[3]->valor) {
            return 15 * $cartas[2]->valor;
        }
        if ($cartas[0]->valor == $cartas[1]->valor) {
            return $cartas[0]->valor;
        }
        if ($cartas[2]->valor == $cartas[1]->valor) {
            return $cartas[2]->valor;
        }
        if ($cartas[2]->valor == $cartas[3]->valor) {
            return $cartas[2]->valor;
        }
    }

    public function obtenerGanadorPares($situacion)
    {
        if ($situacion["pares"] == 'A') {
            return 0;
        } else if ($situacion["pares"] == 'B') {
            return 1;
        }
        for ($i = 0; $i < 4; $i++) {
            $cartas[$i] = $this->obtenerCartas($situacion["mesa_id"], $i);
        }
        $ganador = -1;
        $valorParGanador = 0;
        for ($i = 0; $i < 4; $i++) {
            $jugador = ($situacion["mano"] + $i) % 4;
            if ($this->tienePares($cartas[$jugador])) {
                $valorPar = $this->obtenerValorPar($cartas[$jugador]);
                if ($valorPar > $valorParGanador) {
                    $ganador = $jugador;
                    $valorParGanador = $valorPar;
                }
            }
        }
        return $ganador % 2;
    }

    private function obtenerValorJuegoOPunto($cartas)
    {
        $suma = 0;
        foreach ($cartas as $carta) {
            $suma += $carta->valor > 10 ? 10 : $carta->valor;
        }
        switch ($suma) {
            case 31:
                $valor = 50;
                break;
            case 32:
                $valor = 45;
                break;
            default:
                $valor = $suma;
        }
        return $valor;
    }

    public function obtenerGanadorJuegoOPunto($situacion)
    {
        for ($i = 0; $i < 4; $i++) {
            $cartas[$i] = $this->obtenerCartas($situacion["mesa_id"], $i);
        }
        $ganador = -1;
        $valorJuegoGanador = 0;
        for ($i = 0; $i < 4; $i++) {
            $jugador = ($situacion["mano"] + $i) % 4;
            $valorJuego = $this->obtenerValorJuegoOPunto($cartas[$jugador]);
            if ($valorJuego > $valorJuegoGanador) {
                $ganador = $jugador;
                $valorJuegoGanador = $valorJuego;
            }
        }
        return $ganador % 2;
    }

    public function resolverOrdago($situacion)
    {
        switch ($situacion["jugada"]) {
            case 'grande':
                $ganador = $this->obtenerGanadorGrande($situacion);
                break;
            case 'chica':
                $ganador = $this->obtenerGanadorChica($situacion);
                break;
            case 'pares':
                $ganador = $this->obtenerGanadorPares($situacion);
                break;
            default:
                $ganador = $this->obtenerGanadorJuegoOPunto($situacion);
        }
        $bd = Klasto::getInstance();
        $bd->startTransaction();
        $mesa = $this->obtenerMesa($situacion["mesa_id"]);
        $marcadores = $this->obtenerMarcador($situacion["mesa_id"]);
        $marcadores = $this->sumarYManipularMarcadores(40, $marcadores, $mesa, $ganador);
        foreach ($marcadores as $marcador) {
            $params = [$marcador->puntos, $marcador->juegos, $marcador->vacas, $situacion["mesa_id"], $marcador->pareja_id];
            $sql = "UPDATE `marcador` SET `puntos` = ?, `juegos` = ?, `vacas` = ? WHERE `mesa_id` = ? AND `pareja_id` = ?";
            $bd->execute($sql, $params);
        }
        $bd->commit();
        $pareja = $ganador == 0 ? "Pareja A" : "ParejaB";
        if ($marcadores[$ganador]->vacas == 77) {
            $texto = "$pareja ha ganado la partida";
            $marcadores[$ganador]->vacas = 0;
            $sql = "UPDATE `marcador` SET  `vacas` = 0 WHERE `mesa_id` = ?";
            $params = [$situacion["mesa_id"]];
            $bd->execute($sql, $params);
        } else if ($marcadores[$ganador]->juegos == 0) {
            $texto = "$pareja ha ganado la vaca";
        } else {
            $texto = "$pareja ha ganado el juego";
        }
        $resultado["texto"] = $texto;
        $resultado["marcadores"] = $marcadores;
        return $resultado;
    }

    private function cerrarTodasLasJugadas($id)
    {
        $bd = Klasto::getInstance();
        $params = [$id];
        $sql = "UPDATE `jugadas` SET `grande` = 'X', `chica` = 'X', pares = 'X', juego = 'X', punto = 'X' WHERE `mesa_id` = ?";
        $bd->execute($sql, $params);
    }

    public function resolverGrande($situacion)
    {
        $bd = Klasto::getInstance();
        if ($situacion["grande"] != 'X') {
            $bd->startTransaction();
            $puntos = $situacion["grande"] == '-' ? 1 : $situacion["grande"];
            $ganador = $this->obtenerGanadorGrande($situacion);
            $mesa = $this->obtenerMesa($situacion["mesa_id"]);
            $marcadores = $this->obtenerMarcador($situacion["mesa_id"]);
            $marcadores = $this->sumarYManipularMarcadores($puntos, $marcadores, $mesa, $ganador);
            foreach ($marcadores as $marcador) {
                $params = [$marcador->puntos, $marcador->juegos, $marcador->vacas, $situacion["mesa_id"], $marcador->pareja_id];
                $sql = "UPDATE `marcador` SET `puntos` = ?, `juegos` = ?, `vacas` = ? WHERE `mesa_id` = ? AND `pareja_id` = ?";
                $bd->execute($sql, $params);
            }
            $pareja = $ganador == 0 ? "Pareja A" : "Pareja B";
            if ($marcadores[$ganador]->puntos > 0) {
                $texto = "$pareja gana $puntos de grande";
            } else {
                if ($marcadores[$ganador]->vacas == 77) {
                    $texto = "$pareja gana $puntos de grande y ha ganado la partida";
                    $marcadores[$ganador]->vacas = 0;
                    $sql = "UPDATE `marcador` SET  `vacas` = 0 WHERE `mesa_id` = ?";
                    $params = [$situacion["mesa_id"]];
                    $bd->execute($sql, $params);
                } else if ($marcadores[$ganador]->juegos == 0) {
                    $texto = "$pareja gana $puntos de grande y ha ganado la vaca";
                } else {
                    $texto = "$pareja ha ganado el juego";
                }
                $this->cerrarTodasLasJugadas($situacion["mesa_id"]);
            }
            $bd->commit();
            $res["marcador"] = $marcadores;
            $res["texto"] = $texto;
        } else {
            $res["texto"] = "nada";
        }
        return $res;
    }

    public function resolverChica($situacion)
    {
        $bd = Klasto::getInstance();
        if ($situacion["chica"] != 'X') {
            $bd->startTransaction();
            $puntos = $situacion["chica"] == '-' ? 1 : $situacion["chica"];
            $ganador = $this->obtenerGanadorChica($situacion);
            $mesa = $this->obtenerMesa($situacion["mesa_id"]);
            $marcadores = $this->obtenerMarcador($situacion["mesa_id"]);
            $marcadores = $this->sumarYManipularMarcadores($puntos, $marcadores, $mesa, $ganador);
            foreach ($marcadores as $marcador) {
                $params = [$marcador->puntos, $marcador->juegos, $marcador->vacas, $situacion["mesa_id"], $marcador->pareja_id];
                $sql = "UPDATE `marcador` SET `puntos` = ?, `juegos` = ?, `vacas` = ? WHERE `mesa_id` = ? AND `pareja_id` = ?";
                $bd->execute($sql, $params);
            }
            $pareja = $ganador == 0 ? "Pareja A" : "Pareja B";
            if ($marcadores[$ganador]->puntos > 0) {
                $texto = "$pareja gana $puntos de chica";
            } else {
                if ($marcadores[$ganador]->vacas == 77) {
                    $texto = "$pareja gana $puntos de chica y ha ganado la partida";
                    $marcadores[$ganador]->vacas = 0;
                    $sql = "UPDATE `marcador` SET  `vacas` = 0 WHERE `mesa_id` = ?";
                    $params = [$situacion["mesa_id"]];
                    $bd->execute($sql, $params);
                } else if ($marcadores[$ganador]->juegos == 0) {
                    $texto = "$pareja gana $puntos de chica y ha ganado la vaca";
                } else {
                    $texto = "$pareja gana $puntos de chica y ha ganado el juego";
                }
                $this->cerrarTodasLasJugadas($situacion["mesa_id"]);
            }
            $bd->commit();
            $res["marcador"] = $marcadores;
            $res["texto"] = $texto;
        } else {
            $res["texto"] = "nada";
        }
        return $res;
    }

    public function resolverPares($situacion)
    {
        $bd = Klasto::getInstance();
        if ($situacion["pares"] != 'X') {
            $bd->startTransaction();
            $puntos = 0;
            if ($situacion["pares"] != 'A' && $situacion["pares"] != 'B' || $situacion["pares"] != '-') {
                $puntos = (int) $situacion["pares"];
            }
            $ganador = $this->obtenerGanadorPares($situacion);
            $cartas1 = $this->obtenerCartas($situacion["mesa_id"], $ganador);
            $cartas2 = $this->obtenerCartas($situacion["mesa_id"], $ganador + 2);
            if ($this->tienePares($cartas1)) {
                $v = $this->obtenerValorPar($cartas1);
                $valor1 = $v > 200 ? 3 : ($v >= 15 ? 2 : 1);
                $puntos += $valor1;
            }
            if ($this->tienePares($cartas2)) {
                $v = $this->obtenerValorPar($cartas2);
                $valor2 = $v > 200 ? 3 : ($v >= 15 ? 2 : 1);
                $puntos += $valor2;
            }
            $mesa = $this->obtenerMesa($situacion["mesa_id"]);
            $marcadores = $this->obtenerMarcador($situacion["mesa_id"]);
            $marcadores = $this->sumarYManipularMarcadores($puntos, $marcadores, $mesa, $ganador);
            foreach ($marcadores as $marcador) {
                $params = [$marcador->puntos, $marcador->juegos, $marcador->vacas, $situacion["mesa_id"], $marcador->pareja_id];
                $sql = "UPDATE `marcador` SET `puntos` = ?, `juegos` = ?, `vacas` = ? WHERE `mesa_id` = ? AND `pareja_id` = ?";
                $bd->execute($sql, $params);
            }
            $pareja = $ganador == 0 ? "Pareja A" : "Pareja B";
            if ($marcadores[$ganador]->puntos > 0) {
                $texto = "$pareja gana $puntos de pares";
            } else {
                if ($marcadores[$ganador]->vacas == 77) {
                    $texto = "$pareja gana $puntos de pares y ha ganado la partida";
                    $marcadores[$ganador]->vacas = 0;
                    $sql = "UPDATE `marcador` SET  `vacas` = 0 WHERE `mesa_id` = ?";
                    $params = [$situacion["mesa_id"]];
                    $bd->execute($sql, $params);
                } else if ($marcadores[$ganador]->juegos == 0) {
                    $texto = "$pareja gana $puntos de pares y ha ganado la vaca";
                } else {
                    $texto = "$pareja gana $puntos de pares y ha ganado el juego";
                }
                $this->cerrarTodasLasJugadas($situacion["mesa_id"]);
            }
            $bd->commit();
            $res["marcador"] = $marcadores;
            $res["texto"] = $texto;
        } else {
            $res["texto"] = "nada";
        }
        return $res;
    }

    public function resolverJuego($situacion)
    {
        $bd = Klasto::getInstance();
        if ($situacion["juego"] != 'X') {
            $bd->startTransaction();
            $puntos = 0;
            if ($situacion["juego"] == 'A') {
                $ganador = 0;
            } else if ($situacion["juego"] == 'B') {
                $ganador = 1;
            } else if ($situacion["juego"] == '-') {
                $ganador = $this->obtenerGanadorJuegoOPunto($situacion);
            } else {
                $puntos = (int) $situacion["juego"];
                $ganador = $this->obtenerGanadorJuegoOPunto($situacion);
            }
            $cartas1 = $this->obtenerCartas($situacion["mesa_id"], $ganador);
            $cartas2 = $this->obtenerCartas($situacion["mesa_id"], $ganador + 2);
            if ($this->tieneJuego($cartas1)) {
                $v = $this->obtenerValorJuegoOPunto($cartas1);
                $valor1 = $v == 50 ? 3 : 2;
                $puntos += $valor1;
            }
            if ($this->tieneJuego($cartas2)) {
                $v = $this->obtenerValorJuegoOPunto($cartas2);
                $valor2 = $v == 50 ? 3 : 2;
                $puntos += $valor2;
            }
            $mesa = $this->obtenerMesa($situacion["mesa_id"]);
            $marcadores = $this->obtenerMarcador($situacion["mesa_id"]);
            $marcadores = $this->sumarYManipularMarcadores($puntos, $marcadores, $mesa, $ganador);
            foreach ($marcadores as $marcador) {
                $params = [$marcador->puntos, $marcador->juegos, $marcador->vacas, $situacion["mesa_id"], $marcador->pareja_id];
                $sql = "UPDATE `marcador` SET `puntos` = ?, `juegos` = ?, `vacas` = ? WHERE `mesa_id` = ? AND `pareja_id` = ?";
                $bd->execute($sql, $params);
            }
            $pareja = $ganador == 0 ? "Pareja A" : "Pareja B";
            if ($marcadores[$ganador]->puntos > 0) {
                $texto = "$pareja gana $puntos de juego";
            } else {
                if ($marcadores[$ganador]->vacas == 77) {
                    $texto = "$pareja gana $puntos de juego y ha ganado la partida";
                    $marcadores[$ganador]->vacas = 0;
                    $sql = "UPDATE `marcador` SET  `vacas` = 0 WHERE `mesa_id` = ?";
                    $params = [$situacion["mesa_id"]];
                    $bd->execute($sql, $params);
                } else if ($marcadores[$ganador]->juegos == 0) {
                    $texto = "$pareja gana $puntos de juego y ha ganado la vaca";
                } else {
                    $texto = "$pareja gana $puntos de juego y ha ganado el juego";
                }
                $this->cerrarTodasLasJugadas($situacion["mesa_id"]);
            }
            $bd->commit();
            $res["marcador"] = $marcadores;
            $res["texto"] = $texto;
        } else {
            $res["texto"] = "nada";
        }
        return $res;
    }

    public function resolverPunto($situacion)
    {
        $bd = Klasto::getInstance();
        if ($situacion["punto"] != 'X') {
            $bd->startTransaction();
            $puntos = 1;
            if ($situacion["punto"] == 'A') {
                $ganador = 0;
            } else if ($situacion["punto"] == 'B') {
                $ganador = 1;
            } else if ($situacion["punto"] == '-') {
                $ganador = $this->obtenerGanadorJuegoOPunto($situacion);
            } else {
                $puntos += (int) $situacion["punto"];
                $ganador = $this->obtenerGanadorJuegoOPunto($situacion);
            }
            $mesa = $this->obtenerMesa($situacion["mesa_id"]);
            $marcadores = $this->obtenerMarcador($situacion["mesa_id"]);
            $marcadores = $this->sumarYManipularMarcadores($puntos, $marcadores, $mesa, $ganador);
            foreach ($marcadores as $marcador) {
                $params = [$marcador->puntos, $marcador->juegos, $marcador->vacas, $situacion["mesa_id"], $marcador->pareja_id];
                $sql = "UPDATE `marcador` SET `puntos` = ?, `juegos` = ?, `vacas` = ? WHERE `mesa_id` = ? AND `pareja_id` = ?";
                $bd->execute($sql, $params);
            }
            $pareja = $ganador == 0 ? "Pareja A" : "Pareja B";
            if ($marcadores[$ganador]->puntos > 0) {
                $texto = "$pareja gana $puntos de punto";
            } else {
                if ($marcadores[$ganador]->vacas == 77) {
                    $texto = "$pareja gana $puntos de punto y ha ganado la partida";
                    $marcadores[$ganador]->vacas = 0;
                    $sql = "UPDATE `marcador` SET  `vacas` = 0 WHERE `mesa_id` = ?";
                    $params = [$situacion["mesa_id"]];
                    $bd->execute($sql, $params);
                } else if ($marcadores[$ganador]->juegos == 0) {
                    $texto = "$pareja gana $puntos de punto y ha ganado la vaca";
                } else {
                    $texto = "$pareja gana $puntos de punto y ha ganado el juego";
                }
                $this->cerrarTodasLasJugadas($situacion["mesa_id"]);
            }
            $bd->commit();
            $res["marcador"] = $marcadores;
            $res["texto"] = $texto;
        } else {
            $res["texto"] = "nada";
        }
        return $res;
    }

    private function apuntarAbandono($login)
    {
        $bd = Klasto::getInstance();
        $params = [$login];
        $sql = "UPDATE `estadisticas` SET `abandonos` = `abandonos` + 1 WHERE `login` = ?";
        return $bd->execute($sql, $params);
    }

    public function abandonarMesa($id, $login)
    {
        $bd = Klasto::getInstance();
        $bd->startTransaction();
        $posicion = $this->obtenerPosicion($login);
        $todoOk = $posicion >= 0;
        if ($posicion > 0) {
            $todoOk = $todoOk && $this->apuntarAbandono($login) && $this->cambiarEstadoPartida($id, 0);
        }
        $todoOk = $todoOk && $this->levantarseDeLaMesa($id, $posicion, $login);
        $bd->commit();
        return $todoOk;
    }
}
