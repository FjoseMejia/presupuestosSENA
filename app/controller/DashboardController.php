<?php

namespace presupuestos\controller;

use presupuestos\helpers\Auth;
use presupuestos\helpers\HtmlResponse;
use presupuestos\controller\UserController;
use presupuestos\model\AnioFiscalModel;
use presupuestos\controller\role\RoleController;
use presupuestos\controller\role\PermisoController;
use presupuestos\controller\ReportsController;


class DashboardController {

    public function index($page = "dashboard"){
        Auth::check();
        $title = ucfirst($page);
        $title = str_replace("/", " ", $title);

        $idCentroIdSession = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];
        $idSemanaSession= $_SESSION[APP_SESSION_NAME]['idSemanaActivaSession'];
        $subdirector = UserController::getSubdirector($idCentroIdSession);

        //obtengo los roles para listarlos en la vista
        $roleController = new RoleController();
        $roles = $roleController->list();

        //Obtengo todos los usuarios
        $users = UserController::listUsersByCentro($idCentroIdSession);


        //Obtengo el año fiscal activo
        $anioFiscalActivo = AnioFiscalModel::getPresupuestoActivo($idCentroIdSession);
        $hayAnioFiscal = !empty($anioFiscalActivo);

        //Obtengo todos los permisos para su rol.
        $permisoController = new PermisoController();
        $menuDataUser= $permisoController->getRoleMenuWithSubmenusByRol($_SESSION[APP_SESSION_NAME]['idRolSession']);

        $menuData = $permisoController->getAllMenuWithSubmenus($_SESSION[APP_SESSION_NAME]['idRolSession']);
        
        //Obtengo todas las semanas
        $semanaActiva= AnioFiscalModel::obtenerSemanaActiva($idCentroIdSession);

        $controller = new ReportsController();
        $informe = $controller->getInformePresupuestalPorSemana($idSemanaSession);
        // echo "<pre>";
        // print_r($informe);
        // exit;

        // echo "<pre>";
        // var_dump($_SESSION[APP_SESSION_NAME]);
        // exit;
        //Obtengo las semana por centro y qué está activa
        // echo "<pre>";
        // var_dump( $semanas);
        // exit;
        
    
        $views = [
            "dashboards"      => __DIR__ . '/../view/content/dashboard.php',
            "dashboard"       => __DIR__ . '/../view/content/reports.php',
            "usuarios"        => __DIR__ . '/../view/content/manage_users.php',
            "page_not_found"  => __DIR__ . '/../app/view/errors/404.php',
            "sin-rol"         => __DIR__ . '/../app/view/errors/sin_rol.php',
        ];

        $stylesByView = [
            //"dashboard" => ["css/dashboard/dashboard.css"],
            "reportes"  => ["css/reports/reports.css"],
            "usuarios"  => ["css/role/manage_users.css"]
        ];

        $scriptsByView = [
            "dashboard" => ["js/dashboard/dashboard.js"],
            "usuarios"  => ["js/manage_users.js"]
        ];

        $userRol = UserController::verifyAccount($_SESSION[APP_SESSION_NAME]['idUsuarioSession']);

        if (empty($userRol)) {
            require_once __DIR__ . '/../view/content/sin_rol.php';
            exit;
        }

        

       
        $view    = $views[$page] ?? $views["dashboard"];
        $styles  = $stylesByView[$page] ?? [];
        $scripts = $scriptsByView[$page] ?? [];

        require __DIR__ . '/../view/layout/layout.php';
    }
}
