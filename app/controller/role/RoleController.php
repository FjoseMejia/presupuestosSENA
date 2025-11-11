<?php
namespace presupuestos\controller\role;
use presupuestos\controller\role\manage;
use presupuestos\model\role\RoleModel;
use presupuestos\model\role\PermisoModel;

class RoleController{
   
    public function list() {
        $roleModel = new RoleModel();
        $roles = $roleModel->getAll();

        return $roles;
        exit;
    }

    public function create() {
        $nombre = $_POST['nombre'];

        if (!$nombre) {
            echo json_encode(['state' => 0, 'message' => 'Nombre de rol requerido']);
            exit;
        }

        $roleModel = new RoleModel();
        $ok = $roleModel->create($nombre);

        echo json_encode([
            'state' => $ok ? 1 : 0,
            'message' => $ok ? 'Rol creado correctamente' : 'Error al crear rol'
        ]);
        exit;
    }
}
