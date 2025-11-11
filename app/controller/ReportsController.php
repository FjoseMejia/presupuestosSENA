<?php

namespace presupuestos\controller;

use presupuestos\helpers\Auth;
use presupuestos\model\ReportsModel;
use presupuestos\helpers\ReportsHelper;
use Exception;
use PDO;

class ReportsController
{
    /**
     * POST /reports -> subir Excel semana 1 (cdp, rp, pagos)
     */
    public function index()
    {
        Auth::check();

        try {
            if (empty($_POST['week']) || empty($_POST['semana_id'])) {
                ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'tipo'   => 'simple',
                    'titulo' => 'Datos incompletos',
                    'texto'  => 'Debes indicar la semana y el ID de semana.',
                    'icono'  => 'warning',
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $files = $_FILES;
            $semanaId = (int)$_POST['semana_id'];
            $centroId = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];

            $results = ReportsModel::processWeek1Excels($files, $semanaId, $centroId);


            error_log("=== INICIANDO DIAGNÓSTICO DETALLADO ===");
            ReportsModel::debugCdpDependenciaRelations($semanaId);
            ReportsModel::debugInformeConditions($semanaId);
            error_log("=== FIN DIAGNÓSTICO DETALLADO ===");

            $informeResult = ReportsModel::fillInformePresupuestal($semanaId, $centroId);

            if ($informeResult !== false && $informeResult > 0) {
                $results[] = "Datos procesados en 'informepresupuestal' ($informeResult registros).";
            } else {
                $results[] = "Advertencia: No se pudieron procesar datos en 'informepresupuestal'. Resultado: " . ($informeResult === false ? 'false' : $informeResult);
            }

            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'tipo'   => 'simple',
                'titulo' => '¡Éxito!',
                'texto'  => implode("\n", $results),
                'icono'  => 'success',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'tipo'   => 'simple',
                'titulo' => 'Error al subir',
                'texto'  => $e->getMessage(),
                "archivo" => $e->getFile(),
                "linea"  => $e->getLine(),
                "traza"  => $e->getTraceAsString(),
                'icono'  => 'error',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function getInformePresupuestalPorSemana(int $semanaId)
    {
        Auth::check();

        $idCentroIdSession = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];

        // Obtener datos del modelo
        $datosInforme = ReportsModel::getInformePresupuestalPorSemana(
            $idCentroIdSession,
            $semanaId
        );

        return $datosInforme;

        // Calcular resúmenes
        $resumenes = $this->calcularResumenes($datosInforme);

        // Preparar datos para la vista
        $vistaDatos = [
            'datos' => $datosInforme,
            'resumenes' => $resumenes,
            'semanaId' => $semanaId,
            'totalRegistros' => count($datosInforme)
        ];
    }

    private function calcularResumenes(array $datos): array
    {
        $totalPresupuesto = 0;
        $totalPagado = 0;
        $totalSaldo = 0;
        $totalComprometido = 0;

        foreach ($datos as $fila) {
            $totalPresupuesto += floatval($fila['valorActual'] ?? 0);
            $totalPagado += floatval($fila['valorPagado'] ?? 0);
            $totalSaldo += floatval($fila['saldoPorComprometer'] ?? 0);
            $totalComprometido += floatval($fila['valorComprometido'] ?? 0);
        }

        return [
            'totalPresupuesto' => $totalPresupuesto,
            'totalPagado' => $totalPagado,
            'totalSaldo' => $totalSaldo,
            'totalComprometido' => $totalComprometido
        ];
    }

    /**
     * GET /reports/dependencias -> lista dependencias
     */
    public function dependencias()
    {
        $centroId = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];
        $dependencias = ReportsModel::getDependencias($centroId);

        // Aplicar helper a cada dependencia
        foreach ($dependencias as &$dep) {
            $dep['nombre'] = ReportsHelper::extraerDependencia($dep['nombre']);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dependencias, JSON_UNESCAPED_UNICODE);
        exit;
    }


    /**
     * GET /reports/cdps -> lista números CDP únicos
     */
    public function cdps()
    {
        $idCentro = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];
        $semanaActiva = $_SESSION[APP_SESSION_NAME]['idSemanaActivaSession'];

        header('Content-Type: application/json; charset=utf-8');
        $cdps = ReportsModel::getCDPs($idCentro, $semanaActiva);
        echo json_encode($cdps, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /reports/conceptos -> lista conceptos internos únicos
     */
    public function conceptos()
    {
        $idCentro = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];
        $semanaActiva = $_SESSION[APP_SESSION_NAME]['idSemanaActivaSession'];

        header('Content-Type: application/json; charset=utf-8');
        $conceptos = ReportsModel::getConceptos($idCentro, $semanaActiva);
        echo json_encode($conceptos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /reports/consulta?dependencia=...&numero_cdp=...&concepto_interno=...
     */
    public function buscarInformePresupuestal()
    {
        Auth::check();

        $tipoFiltro = $_GET['tipoFiltro'];
        $valor = $_GET['valor'];
        $semana = $_GET['semana'];

        $centroId = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];

        // Preparar valor según tipo de filtro
        switch ($tipoFiltro) {
            case '1': // Dependencia
                // Extraer solo el número inicial
                $valorFiltro = preg_match('/^\d+/', $valor, $match) ? $match[0] : $valor;
                break;
            case '2': // CDP
                $valorFiltro = trim($valor); // puede ser el número de CDP
                break;
            case '3': // Objeto
                $valorFiltro = trim($valor); // texto completo del objeto
                break;
            default:
                $valorFiltro = $valor;
                break;
        }

        // Traer datos filtrados
        $datos = ReportsModel::buscarInformePresupuestal($centroId, $semana, $tipoFiltro, $valorFiltro);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }


    /**
     * POST /reports/delete -> limpia datos cargados (TRUNCATE) para la semana indicada
     */
    public function delete()
    {
        Auth::check();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $week = $_POST['week'] ?? '';
            if ($week === '') {
                echo json_encode([
                    'tipo' => 'simple',
                    'titulo' => 'Semana requerida',
                    'texto' => 'No se recibió la semana a eliminar.',
                    'icono' => 'warning'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            ReportsModel::clearWeekData($week);

            echo json_encode([
                'tipo' => 'simple',
                'titulo' => 'Datos eliminados',
                'texto' => 'Se eliminaron los datos asociados a la semana seleccionada.',
                'icono' => 'success'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            echo json_encode([
                'tipo' => 'simple',
                'titulo' => 'Error al eliminar',
                'texto' => $e->getMessage(),
                'icono' => 'error'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
