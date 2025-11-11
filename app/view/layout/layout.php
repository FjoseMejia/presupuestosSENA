<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <meta name="description" content="Sistema de gestión de presupuestos">

    <link rel="icon" href="<?= APP_URL ?>assets/img/sena.png" type="image/png">

    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </noscript>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="<?= APP_URL ?>css/menustyles.css">
    <link rel="stylesheet" type="text/css" href="<?= APP_URL ?>css/roles/roles.css">
    <link rel="stylesheet" type="text/css" href="<?= APP_URL ?>css/permissions/permissions.css">

    <?php foreach ($styles as $css): ?>
        <link rel="stylesheet" href="<?= APP_URL . '/' . $css ?>">
    <?php endforeach; ?>

    <style>
        .page-wrapper {
            background-color: #f8f9fa;
            min-height: calc(100vh - 200px);
            margin-top: 0;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .btn {
            border-radius: 6px;
        }

        .header {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1030;
        }

        .subheader-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            position: relative;
            z-index: 1029;
        }

        .user-photo img {
            border: 2px solid #39A900;
        }

        .sena-section .titulo h1 {
            color: #39A900;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <!-- ========== HEADER ========== -->
    <header class="header" role="banner">
        <div class="container-fluid">
            <div class="row align-items-center py-2">
                <div class="col-md-6">
                    <div class="user-section d-flex align-items-center">
                        <button class="btn btn-success btn-menu-toggle me-3" id="toggleMenu"
                            aria-label="Abrir menú" aria-expanded="false" aria-controls="sidebarMenu">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="user-info d-flex align-items-center">
                            <div class="user-photo me-3">
                                <img src="<?= APP_URL ?>assets/img/default.png" alt="Foto de usuario"
                                    class="rounded-circle" width="50" height="50">
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold fs-6">
                                    <?= $_SESSION[APP_SESSION_NAME]['usuarioLogueadoSession'] ?>
                                </span>
                                <span class="badge bg-success mt-1"><?= $userRol ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="sena-section d-flex align-items-center justify-content-end">
                        <div class="logo-sena me-3">
                            <img src="<?= APP_URL ?>assets/img/logoSena.png" alt="Logo SENA" height="50">
                        </div>
                        <div class="titulo">
                            <h1 class="h4 mb-0 fw-bold">Sistema de Presupuestos</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ========== MENÚ LATERAL ========== -->
    <nav class="sidebar" id="sidebarMenu" role="navigation" aria-label="Menú principal">
        <div class="accordion accordion-flush" id="menuAccordion">
            <div class="menu-item bg-success text-white">
                <a href="<?= APP_URL ?>dashboard" class="d-flex align-items-center text-white text-decoration-none p-2 rounded">
                    <i class="fas fa-home me-2"></i>Inicio
                </a>
            </div>


            <?php
            $menuHierarchy = [];
            foreach ($menuDataUser as $item) {
                $menuId = $item['idMenu'];
                if (!isset($menuHierarchy[$menuId])) {
                    $menuHierarchy[$menuId] = [
                        'nombreMenu' => $item['nombreMenu'],
                        'iconoInicialMenu' => $item['iconoInicialMenu'],
                        'ordenMenu' => $item['ordenMenu'],
                        'submenus' => []
                    ];
                }
                if (!empty($item['idSubMenu'])) {
                    $menuHierarchy[$menuId]['submenus'][] = [
                        'idSubMenu' => $item['idSubMenu'],
                        'nombreSubMenu' => $item['nombreSubMenu'],
                        'urlSubmenu' => $item['urlSubmenu'],
                        'iconoInicialSubMenu' => $item['iconoInicialSubMenu']
                    ];
                }
            }

            foreach ($menuHierarchy as $menuId => $menu):
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $menuId ?>">
                        <button class="accordion-button collapsed single-link bg-success text-white" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse<?= $menuId ?>"
                            aria-expanded="false" aria-controls="collapse<?= $menuId ?>">
                            <i class="<?= $menu['iconoInicialMenu'] ?> me-2"></i>
                            <?= $menu['nombreMenu'] ?>
                        </button>
                    </h2>

                    <?php if (!empty($menu['submenus'])): ?>
                        <div id="collapse<?= $menuId ?>" class="accordion-collapse collapse"
                            aria-labelledby="heading<?= $menuId ?>" data-bs-parent="#menuAccordion">
                            <div class="accordion-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($menu['submenus'] as $submenu): ?>
                                        <?php if (stripos($submenu['nombreSubMenu'], 'permiso') !== false): ?>
                                            <a href="#" class="list-group-item list-group-item-action roles-modal-trigger"
                                                data-bs-toggle="modal" data-bs-target="#rolesModal">
                                                <i class="<?= !empty($submenu['iconoInicialSubMenu']) ? $submenu['iconoInicialSubMenu'] : 'fas fa-user-shield' ?> me-2"></i>
                                                <?= $submenu['nombreSubMenu'] ?>
                                            </a>

                                        <?php else: ?>
                                            <a href="<?= !empty($submenu['urlSubmenu']) ? APP_URL . $submenu['urlSubmenu'] : '#' ?>"
                                                class="list-group-item list-group-item-action">
                                                <i class="<?= !empty($submenu['iconoInicialSubMenu']) ? $submenu['iconoInicialSubMenu'] : 'fas fa-circle' ?> me-2"></i>
                                                <?= $submenu['nombreSubMenu'] ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="menu-item bg-success text-white">
                <form action="<?= APP_URL ?>logout" method="POST" class="w-100 m-0">
                    <button type="submit" class="d-flex align-items-center bg-success text-white w-100 text-start border-0 p-2 rounded">
                        <i class="fas fa-sign-out-alt me-2"></i>Salir
                    </button>
                </form>
            </div>

        </div>
    </nav>

    <div class="backdrop-sidebar" id="sidebarBackdrop" aria-hidden="true"></div>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main class="page-wrapper" id="main-content" role="main">
        <div class="container-fluid py-4">
            <?php require $view; ?>
        </div>
    </main>

    <!-- ========== MODAL AÑO FISCAL ========== -->
    <div class="modal fade modal-yearfiscal" id="modalAnioFiscal" tabindex="-1"
        aria-labelledby="modalAnioFiscalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h2 class="modal-title h5 mb-0">Crear Año Fiscal</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="yf-alerts" id="yf-alerts" role="alert" aria-live="polite"></div>

                    <form action="<?= APP_URL ?>crear_anio_fiscal" class="form-Fiscal yearfiscal-form FormularioAjax" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <?php if ($subdirector): ?>
                                        <label for="subdirector" class="form-label fw-semibold">Subdirector</label>
                                        <input type="text" id="subdirector" name="subdirector" class="form-control"
                                            value="<?= htmlspecialchars($subdirector['nombres'] . ' ' . $subdirector['apellidos']) ?>" readonly>
                                        <input type="hidden" name="subdirector_id" value="<?= $subdirector['idUser'] ?>">
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            No hay un subdirector asignado actualmente.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valor_presupuesto" class="form-label fw-semibold">Valor Presupuesto</label>
                                    <input type="text" name="valor_presupuesto" class="form-control inputFiscal"
                                        id="valor_presupuesto" placeholder="$0" required>
                                    <input type="hidden" id="monto_hidden" name="monto_hidden">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="year-fiscal" class="form-label fw-semibold">Año Fiscal</label>
                                    <input type="number" id="year-fiscal" class="form-control"
                                        value="<?= date('Y') ?>" name="year_fiscal" min="2000" max="2030" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado" class="form-label fw-semibold">Estado</label>
                                    <select class="form-select inputFiscal" id="estado" name="estado" required>
                                        <option value="" disabled selected>Seleccione una opción</option>
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label fw-semibold">Fecha Inicio</label>
                                    <input type="date" class="form-control inputFiscal" name="fecha_inicio" id="fecha_inicio" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_cierre" class="form-label fw-semibold">Fecha Cierre</label>
                                    <input type="date" class="form-control inputFiscal" name="fecha_cierre" id="fecha_cierre" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary yf-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success yf-btn-create">Crear Año Fiscal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== MODAL GESTIÓN DE ROLES ========== -->
    <div class="modal fade modal-roles" id="rolesModal" tabindex="-1"
        aria-labelledby="rolesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h2 class="modal-title h5 mb-0">Gestión de Permisos</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="accordion accordion-flush" id="accordionRoles">
                        <?php
                        $menusUnicos = [];
                        foreach ($menuData as $item) {
                            if (!in_array($item['idMenu'], array_column($menusUnicos, 'idMenu'))) {
                                $menusUnicos[] = [
                                    'idMenu' => $item['idMenu'],
                                    'nombreMenu' => $item['nombreMenu'],
                                    'iconoInicialMenu' => $item['iconoInicialMenu']
                                ];
                            }
                        }

                        $varContR = 1;
                        foreach ($roles as $rol): ?>
                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header" id="heading<?= $varContR ?>">
                                    <button class="accordion-button collapsed single-link bg-success text-white" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse<?= $varContR ?>"
                                        aria-expanded="false" aria-controls="collapse<?= $varContR ?>">
                                        <input type="hidden" id="idRol<?= $varContR ?>" value="<?= $rol['idRol'] ?>">
                                        <i class="fas fa-user-shield me-2"></i>
                                        <strong><?= $rol['nombre'] ?></strong>
                                    </button>
                                </h2>
                                <div id="collapse<?= $varContR ?>" class="accordion-collapse collapse"
                                    aria-labelledby="heading<?= $varContR ?>" data-bs-parent="#accordionRoles">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($menusUnicos as $menu): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-label mb-0 fw-bold text-success">
                                                            <i class="<?= $menu['iconoInicialMenu'] ?> me-2"></i>
                                                            <?= $menu['nombreMenu'] ?>
                                                        </label>
                                                        <input type="hidden" id="idMenu<?= $varContR ?>_<?= $menu['idMenu'] ?>" value="<?= $menu['idMenu'] ?>">
                                                    </div>
                                                    <div class="ps-3">
                                                        <?php
                                                        $submenusDelMenu = array_filter($menuData, function ($item) use ($menu) {
                                                            return $item['idMenu'] == $menu['idMenu'] && !empty($item['idSubMenu']);
                                                        });

                                                        foreach ($submenusDelMenu as $submenu): ?>
                                                            <div class="d-flex justify-content-between align-items-center mb-2 py-1">
                                                                <label class="form-label mb-0 small">
                                                                    <i class="<?= !empty($submenu['iconoInicialSubMenu']) ? $submenu['iconoInicialSubMenu'] : 'fas fa-circle' ?> me-2 text-muted"></i>
                                                                    <?= $submenu['nombreSubMenu'] ?>
                                                                </label>
                                                                <input type="hidden" id="idSubMenu<?= $varContR ?>_<?= $submenu['idSubMenu'] ?>" value="<?= $submenu['idSubMenu'] ?>">
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <input type="radio" class="btn-check" name="permiso_sub_<?= $varContR ?>_<?= $submenu['idSubMenu'] ?>" value="1" id="si_sub_<?= $varContR ?>_<?= $submenu['idSubMenu'] ?>">
                                                                    <label class="btn btn-outline-success" for="si_sub_<?= $varContR ?>_<?= $submenu['idSubMenu'] ?>">Sí</label>
                                                                    <input type="radio" class="btn-check" name="permiso_sub_<?= $varContR ?>_<?= $submenu['idSubMenu'] ?>" value="0" id="no_sub_<?= $varContR ?>_<?= $submenu['idSubMenu'] ?>">
                                                                    <label class="btn btn-outline-danger" for="no_sub_<?= $varContR ?>_<?= $submenu['idSubMenu'] ?>">No</label>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php $varContR++; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary roles-btn-cancel" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success roles-btn-save">Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== TOASTS ========== -->
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 100000;"></div>

    <!-- ========== SCRIPTS ========== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="<?= APP_URL ?>js/menuctr.js"></script>

    <?php foreach ($scripts as $script): ?>
        <script src="<?= APP_URL . '/' . $script ?>"></script>
    <?php endforeach; ?>

    <?php if (!empty($pageScripts)): ?>
        <?= $pageScripts ?>
    <?php endif; ?>

    <script>
        const BASE_URL = "<?= APP_URL ?>";
        const hayAnioFiscal = <?= $hayAnioFiscal ? 'true' : 'false' ?>;
    </script>

    <script src="<?= rtrim(APP_URL, '/') ?>/js/dashboard/dashboard.js"></script>
    <script src="<?= rtrim(APP_URL, '/') ?>/js/sweetalert2.all.min.js"></script>
    <script src="<?= rtrim(APP_URL, '/') ?>/js/alerts.js"></script>
    <script src="<?= rtrim(APP_URL, '/') ?>/js/app.js"></script>
</body>

</html>