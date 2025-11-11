<?php
namespace presupuestos\model\role;
use presupuestos\model\MainModel;
use PDO;

class PermisoModel extends MainModel
{
    
    public function getAllMenuWithSubmenus(): array {
        $query = "SELECT 
                m.idMenu,
                m.nombreMenu,
                m.iconoInicialMenu,
                m.backgroundMenu,
                m.ordenMenu,
                sm.idSubMenu,
                sm.nombreSubMenu,
                sm.urlSubmenu,
                sm.iconoInicialSubMenu,
                sm.ordenSubMenu
            FROM menu m
            INNER JOIN subMenu sm ON m.idMenu = sm.idMenuFk
            ORDER BY m.ordenMenu, sm.ordenSubMenu
            ";

        $stmt = parent::executeQuery($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMenuWithSubmenusByRol($idRol)
    {
        $query = "
            SELECT 
                m.idMenu,
                m.nombreMenu,
                m.iconoInicialMenu,
                m.backgroundMenu,
                m.ordenMenu,
                sm.idSubMenu,
                sm.nombreSubMenu,
                sm.urlSubmenu,
                sm.iconoInicialSubMenu,
                sm.ordenSubMenu
            FROM menu m
            INNER JOIN subMenu sm ON m.idMenu = sm.idMenuFk
            INNER JOIN permisos p ON m.idMenu = p.idMenuFk AND sm.idSubMenu = p.idSubMenuFk
            WHERE p.idRolFk = :idRol
            ORDER BY m.ordenMenu, sm.ordenSubMenu
        ";

        $params = [
            ':idRol' => $idRol
        ];
        $stmt = parent::executeQuery($query, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $nombre): bool {
        $query = "INSERT INTO menu (nombreMenu) VALUES (:nombre)";
        $stmt = parent::executeQuery($query, ['nombre' => $nombre]);
        return $stmt->rowCount() > 0;
    }
}
