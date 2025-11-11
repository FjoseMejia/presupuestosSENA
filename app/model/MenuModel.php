<?php
namespace presupuestos\model;

use presupuestos\model\MainModel;

class MenuModel extends MainModel{

    public function list($userRol){
        // Traer los permisos del usuario logueado
        $queryById = "
            SELECT 
                p.icon,
                p.url, 
                r.nombre AS nombre_rol, 
                p.nombre AS nombre_permiso
            FROM permisorol pr
            JOIN permiso p ON p.id = pr.permiso_id
            JOIN rol r ON r.idRol = pr.rol_id
            WHERE r.id = :userRol
              AND pr.estado = '1'
        ";

        $params = ["userRol" => $userRol];
        $stmtById = $this->executeQuery($queryById, $params);
        $dataPermisosByRol = $stmtById->fetchAll(\PDO::FETCH_ASSOC); 
        
        $query = "
            SELECT
                r.icon,
                r.id AS rol_id,
                r.nombre AS nombre_rol,
                p.id AS permiso_id,
                p.nombre AS permiso_nombre,
                pr.estado AS estado
            FROM permisorol pr
            JOIN permiso p ON p.id = pr.permiso_id
            JOIN rol r ON r.id = pr.rol_id
            ORDER BY r.id, p.nombre
        ";

        $stmt = $this->executeQuery($query); 
        $permissionsPerRol = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'state' => 1,
            'userRolePermissions' => $dataPermisosByRol,
            'allRolePermissions' => $permissionsPerRol
        ];
    }

    public function actualizarPermiso($idRol, $idMenu, $idSubMenu, $estado)
    {

        // 1. PRIMERO ELIMINAR EL REGISTRO EXISTENTE SI HAY
        $queryDelete = "DELETE FROM permisos 
                   WHERE idRolFk = :idRol 
                   AND idMenuFk = :idMenu 
                   AND idSubMenuFk " . ($idSubMenu ? "= :idSubMenu" : "IS NULL");

        $paramsDelete = [
            ':idRol' => $idRol,
            ':idMenu' => $idMenu
        ];

        if ($idSubMenu) {
            $paramsDelete[':idSubMenu'] = $idSubMenu;
        }

        $this->executeQuery($queryDelete, $paramsDelete);

        // 2. SI EL ESTADO ES TRUE (PERMITIDO), INSERTAR NUEVO REGISTRO
        if ($estado) {
            $queryInsert = "INSERT INTO permisos (idRolFk, idMenuFk, idSubMenuFk) 
                       VALUES (:idRol, :idMenu, :idSubMenu)";

            $paramsInsert = [
                ':idRol' => $idRol,
                ':idMenu' => $idMenu,
                ':idSubMenu' => $idSubMenu
            ];

            return $this->executeQuery($queryInsert, $paramsInsert);
        }

        return true; // Si el estado es false, solo eliminamos (no insertamos)
    }

}
