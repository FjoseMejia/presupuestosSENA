<?php
namespace presupuestos\model\role;
use presupuestos\model\MainModel;
use PDO;

class PermisoRolModel extends MainModel{
    
    public function assign(int $roleId, int $permisoId): bool {
        $query = "INSERT INTO permiso_rol (rol_id, permiso_id) VALUES (:rol, :permiso)";
        $stmt = parent::executeQuery($query, ['rol' => $roleId, 'permiso' => $permisoId]);
        return $stmt->rowCount() > 0;
    }

    public function revoke(int $roleId, int $permisoId): bool {
        $query = "DELETE FROM permiso_rol WHERE rol_id = :rol AND permiso_id = :permiso";
        $stmt = parent::executeQuery($query, ['rol' => $roleId, 'permiso' => $permisoId]);
        return $stmt->rowCount() > 0;
    }

    public function getPermisosByRole(int $roleId): array {
        $query = "SELECT p.* 
                  FROM permiso p
                  INNER JOIN permiso_rol pr ON p.id = pr.permiso_id
                  WHERE pr.rol_id = :rol";
        $stmt = parent::executeQuery($query, ['rol' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
