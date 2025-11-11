<?php
// app/controller/PermisoRolController.php

class PermisoRolController
{
    public function assign() {
        $roleId    = $_POST['rol_id'] ?? null;
        $permisoId = $_POST['permiso_id'] ?? null;

        if (!$roleId || !$permisoId) {
            echo json_encode(['state' => 0, 'message' => 'Faltan parámetros']);
            exit;
        }

        $permisoRolModel = new PermisoRolModel();
        $ok = $permisoRolModel->assign((int)$roleId, (int)$permisoId);

        echo json_encode([
            'state' => $ok ? 1 : 0,
            'message' => $ok ? 'Permiso asignado' : 'No se pudo asignar'
        ]);
        exit;
    }

    public function revoke() {
        $roleId    = $_POST['rol_id'] ?? null;
        $permisoId = $_POST['permiso_id'] ?? null;

        if (!$roleId || !$permisoId) {
            echo json_encode(['state' => 0, 'message' => 'Faltan parámetros']);
            exit;
        }

        $permisoRolModel = new PermisoRolModel();
        $ok = $permisoRolModel->revoke((int)$roleId, (int)$permisoId);

        echo json_encode([
            'state' => $ok ? 1 : 0,
            'message' => $ok ? 'Permiso revocado' : 'No se pudo revocar'
        ]);
        exit;
    }

    public function listByRole() {
        $roleId = $_GET['rol_id'] ?? null;
        if (!$roleId) {
            echo json_encode(['state' => 0, 'message' => 'rol_id requerido']);
            exit;
        }

        $permisoRolModel = new PermisoRolModel();
        $permisos = $permisoRolModel->getPermisosByRole((int)$roleId);

        echo json_encode(['state' => 1, 'data' => $permisos]);
        exit;
    }
}
