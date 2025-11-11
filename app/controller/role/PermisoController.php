<?php
namespace presupuestos\controller\role;
use presupuestos\model\role\PermisoModel;

class PermisoController{
    public function getAllMenuWithSubmenus() {
        $permisoModel = new PermisoModel();
        $permisos = $permisoModel->getAllMenuWithSubmenus();
        return $permisos;
        exit;
    }

    public function getRoleMenuWithSubmenusByRol($idRol){
        $permisoModel = new PermisoModel();
        $permisos= $permisoModel->getMenuWithSubmenusByRol($idRol);
        return $permisos;
    }

    public function create() {
        $nombre = $_POST['nombre'] ?? '';

        if (!$nombre) {
            echo json_encode(['state' => 0, 'message' => 'Nombre de permiso requerido']);
            exit;
        }

        $permisoModel = new PermisoModel();
        $ok = $permisoModel->create($nombre);

        echo json_encode([
            'state' => $ok ? 1 : 0,
            'message' => $ok ? 'Permiso creado correctamente' : 'Error al crear permiso'
        ]);
        exit;
    }
}
