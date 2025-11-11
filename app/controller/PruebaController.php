<?php
namespace presupuestos\controller;

use presupuestos\model\AnioFiscalModel;
use presupuestos\model\MainModel;
use presupuestos\helpers\HtmlResponse;
use Exception;


require_once __DIR__ . '../../../bootstrap.php';

class PruebaController extends MainModel{
    public static function listarSubMenu($idCentroIdSession){
        $query = "SELECT
                centros.idCentro,
                rol.idRol,
                menu.idMenu,
                menu.nombreMenu
                FROM
                centros
                INNER JOIN rol ON centros.idCentro = rol.idCentroFK
                INNER JOIN menu ON rol.idRol = menu.idRolFk
                WHERE
                centros.idCentro= '$idCentroIdSession'";

        $stmt= parent::executeQuery($query);
        $permissionsPerRol = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $permissionsPerRol;
    }

    public static function listarRoles($idCentroIdSession) {
        $query= "SELECT
        centros.idCentro,
        centros.centro,
        rol.idRol,
        rol.nombre,
        rol.ordenRol,
        rol.icon
        FROM
        centros
        INNER JOIN rol ON centros.idCentro = rol.idCentroFk
        WHERE
        centros.idCentro = '$idCentroIdSession'";

        $stmt = parent::executeQuery($query);
        $permissionsPerRol = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $permissionsPerRol;
    }
}