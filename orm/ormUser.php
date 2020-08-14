<?php

namespace orm;

use \dawfony\Klasto;

class OrmUser
{
    function existeLogin($login)
    {
        $bd = Klasto::getInstance();
        $params = [$login];
        $sql = "SELECT login FROM usuario WHERE login = ?";
        return $bd->queryOne($sql, $params);
    }

    public function registrarUsuario($login, $email, $avatar, $contrasenna)
    {
        $bd = Klasto::getInstance();
        $todoOk = false;
        $bd->startTransaction();
        $rol_id = 1;
        $params = [$login, $email, $avatar, $contrasenna, $rol_id];
        $sql = "INSERT INTO usuario VALUES (?, ?, ?, ?, ?)";
        $params2 = [$login];
        $sql2 = "INSERT INTO estadisticas (`login`) VALUES (?)";
        $params3 = [$login, $login, $login];
        $sql3 = "INSERT INTO mustruo_temporal (`login`, `tipo_mustruo`) VALUES (?, 0), (?, 1), (?, 2)";
        $todoOk = $bd->execute($sql, $params) && $bd->execute($sql2, $params2) && $bd->execute($sql3, $params3);
        $bd->commit();
        return $todoOk;
    }

    public function recibirContrasenna($login)
    {
        $bd = Klasto::getInstance();
        $params = [$login];
        $sql = "SELECT contrasenna FROM usuario WHERE login = ?";
        return $bd->queryOne($sql, $params);
    }

    public function obtenerUsuario($login)
    {
        $bd = Klasto::getInstance();
        $params = [$login];
        $sql = "SELECT login, rol_id, imagen, email FROM usuario WHERE login = ?";
        return $bd->queryOne($sql, $params, "objects\Usuario");
    }

    public function obtenerEstadisticas($login)
    {
        $bd = Klasto::getInstance();
        $params = [$login];
        $sql = "SELECT `juegos_jugados`, `juegos_ganados`, `vacas_jugadas`, `vacas_ganadas`, `abandonos`, `mustruo_semana`, `mustruo_mes`, `mustruo_anno` FROM `estadisticas` WHERE login = ?";
        return $bd->queryOne($sql, $params);
    }

    public function obtenerRanking($tipo)
    {
        $bd = Klasto::getInstance();
        $params = [$tipo];
        $sql = "SELECT `login`, `puntos` FROM `mustruo_temporal` WHERE `tipo_mustruo` = ? ORDER BY puntos DESC LIMIT 10";
        return $bd->query($sql, $params);
    }

    public function comprobarEstadoUsuario($login)
    {
        $bd = Klasto::getInstance();
        $params = [$login];
        $sql = "SELECT `login`, `posicion`, `mesa_id` FROM `usuarios_por_mesa` WHERE `login` = ?";
        $estado = $bd->queryOne($sql, $params);
        if (isset($estado["posicion"])) {
            if ($estado["posicion"] == 0) {
                $estado["jugadores_mesa"] = $bd->queryOne("SELECT COUNT(`login`) as `cuenta` from `usuarios_por_mesa` WHERE `mesa_id` = ?", [$estado["mesa_id"]])["cuenta"];
            }
        } else {
            $estado["posicion"] = -1;
        }
        return $estado;
    }

    public function modificarAvatar($login, $avatar)
    {
        $bd = Klasto::getInstance();
        $params = [$avatar, $login];
        $sql = "UPDATE usuario SET imagen = ? WHERE `login` = ?";
        return $bd->execute($sql, $params);
    }

    public function modificarEmail($login, $emailCambio)
    {
        $bd = Klasto::getInstance();
        $params = [$emailCambio, $login];
        $sql = "UPDATE usuario SET `email` = ? WHERE `login` = ?";
        return $bd->execute($sql, $params);
    }

    public function modificarContrasenna($login, $contrasenna)
    {
        $bd = Klasto::getInstance();
        $params = [$contrasenna, $login];
        $sql = "UPDATE usuario SET `contrasenna` = ? WHERE `login` = ?";
        return $bd->execute($sql, $params);
    }
}
