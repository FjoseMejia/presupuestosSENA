<?php

use presupuestos\controller\Auth\AuthController;
use presupuestos\controller\DashboardController;
use presupuestos\controller\MenuController;
use presupuestos\controller\ReportsController;
use presupuestos\controller\AnioFiscalController;
use presupuestos\controller\UserController;

return [
    '' => fn() => (new AuthController())->showLogin(),
    'login' => fn() => (new AuthController())->showLogin(),
    'login-post' => fn() => (new AuthController())->login($_POST),
    'getCentro' => fn() => (new AuthController())->getCentros(),
    'register-post' => fn() => (new AuthController())->register($_POST),
    'recovery-post' => fn() => (new AuthController())->recoveryPassword($_POST),
    'send-successful' => fn() => (new AuthController())->showSendSuccessful(),
    'recovery' => fn() => (new AuthController())->showRecoveryPassword($_POST),
    'logout-post' => fn() => (new AuthController())->logout(),
    'verify' => fn() => (new AuthController())->verify($_GET['token']),
    'dashboard' => fn() => (new DashboardController())->index(),
    'sin-rol' => fn() => (new DashboardController())->index("sin-rol"),
    'reports-post' => fn() => (new ReportsController())->index(),
    // Obtener las dependencias
    'reports/dependencias' => fn() => (new ReportsController())->dependencias(),
    // Obtener los cdp
    'reports/cdps' => fn() => (new ReportsController())->cdps(),
    // Obtener los conceptos
    'reports/conceptos' => fn() => (new ReportsController())->conceptos(),
    //Busca el informe por el filtro que solicito el usuario
    'api/buscar-informe' => fn() => (new ReportsController())->buscarInformePresupuestal(),
    // Eliminación de datos de reportes (sólo POST)
    'reports/delete-post' => fn() => (new ReportsController())->delete(),
    'dashboard/actualizar-permiso-post' => fn() => (new MenuController())->updatePermisses(),
    'crear_anio_fiscal-post' => fn() => AnioFiscalController::crearAnioFiscal(),
    'semanas/eliminar-post'=> fn()=> AnioFiscalController::eliminarSemanas(),
    //Gestión de Roles
    'gestionar/usuarios' => fn() => (new DashboardController())->index('usuarios'),
    'usuarios/update-post' => fn() => (new UserController())->update($user_id = $_POST['id'], $_POST),
    //Para probar
    'subirArchivo' => fn() => (new DashboardController())->index("subirArchivo"),

    /*
    
    'gestionar-usuarios'=> fn()=> (RoleController()->listar()),
    'roles/list' (new RoleController())->list(),
    'roles/create' (new RoleController())->create(),
    'permisos/list' (new PermisoController())->list(),
    'permisoRol/assign' (new PermisoRolController())->assign(),
    'permisoRol/revoke' (new PermisoRolController())->revoke(),
    'permisoRol/listByRole' (new PermisoRolController())->listByRole(),
    */

];
