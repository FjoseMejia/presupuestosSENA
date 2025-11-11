<?php
namespace presupuestos\controller;

use presupuestos\model\MenuModel;
use presupuestos\helpers\ResponseHelper;
use presupuestos\helpers\HtmlResponse;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

class MenuController{

    public function updatePermisses()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ResponseHelper::error('Método no permitido');
        }

        // Obtener los datos JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $permisos = $input['permisos'] ?? [];

        if (empty($permisos)) {
            ResponseHelper::error('No se recibieron permisos para actualizar');
        }

        $menuModel = new \presupuestos\model\MenuModel();

        try {
            // Procesar cada permiso
            foreach ($permisos as $permiso) {
                $idRol = $permiso['idRolFk'];
                $idMenu = $permiso['idMenuFk'];
                $idSubMenu = $permiso['idSubMenuFk'] ?? null;
                $estado = $permiso['permiso']; // true/false

                // Llamar al método actualizado
                $success = $menuModel->actualizarPermiso($idRol, $idMenu, $idSubMenu, $estado);

                if (!$success) {
                    ResponseHelper::error('Error al actualizar permiso para rol: ' . $idRol);
                }
            }

            ResponseHelper::success('Todos los permisos se actualizaron correctamente');
        } catch (\Exception $e) {
            ResponseHelper::error('Error del sistema: ' . $e->getMessage());
        }
    }

}
