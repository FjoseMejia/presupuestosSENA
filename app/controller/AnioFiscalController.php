<?php

namespace presupuestos\controller;

use presupuestos\model\AnioFiscalModel;
use presupuestos\model\MainModel;
use presupuestos\helpers\HtmlResponse;
use presupuestos\helpers\ResponseHelper;
use Exception;
use Symfony\Component\Mime\Message;

class AnioFiscalController{

    public static function crearAnioFiscal(){
        if (
            empty($_POST['year_fiscal']) ||
            empty($_POST['valor_presupuesto']) ||
            empty($_POST['fecha_inicio']) ||
            empty($_POST['fecha_cierre']) ||
            empty($_POST['subdirector_id'])
        ) {
            echo json_encode([
                "tipo"   => "simple",
                "titulo" => "Error",
                "texto"  => "Faltan datos obligatorios",
                "icono"  => "error"
            ]);
            return;
        }

        $anioFiscal    = (int) $_POST['year_fiscal'];
        $presupuesto   = $_POST['valor_presupuesto'];
        $presupuesto   = str_replace(['$', '.'], '', $presupuesto);
        $presupuesto   = (float) $presupuesto;
        $estado        = ($_POST['estado'] ?? '') === 'activo' ? 1 : 0;
        $fechaInicio   = $_POST['fecha_inicio'];
        $fechaCierre   = $_POST['fecha_cierre'];
        $subdirectorId = (int) $_POST['subdirector_id'];
       
        $usuarioId = $_SESSION[APP_SESSION_NAME]['idUsuarioSession'];
        $centroId  = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];

        if (!$usuarioId || !$centroId) {
            echo json_encode([
                "tipo"   => "simple",
                "titulo" => "Error",
                "texto"  => "No se encontró información de sesión (usuario o centro)",
                "icono"  => "error"
            ]);
            return;
        }

        try {
            // Preparar datos para el año fiscal
            $datosAnioFiscal = [
                $subdirectorId,   // subdirector_id
                $usuarioId,       // user_id
                $anioFiscal,      // anio_fiscal
                $presupuesto,     // valor_anio_fiscal
                $presupuesto,     // presupuesto_actual
                $fechaInicio,     // fecha_inicio
                $fechaCierre,     // fecha_cierre
                $estado,          // estado
                $centroId         // id_centro
            ];

            // Generar semanas
            $semanas = self::generarSemanas($fechaInicio, $fechaCierre);

            // Llamar al modelo para guardar todo
            AnioFiscalModel::crearAnioFiscalConSemanas($datosAnioFiscal, $semanas, $centroId);

            echo json_encode([
                "tipo"   => "redireccionar",
                "titulo" => "Éxito",
                "texto"  => "Año fiscal creado correctamente con " . count($semanas) . " semanas generadas",
                "icono"  => "success",
                "url"    => "dashboard",
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "tipo"   => "simple",
                "titulo" => "Error",
                "texto"  => "Error 505. Contacte con el administrador",//.$e->getMessage(),                            
                "icono"  => "error"
            ], JSON_PRETTY_PRINT);
        }
    }

    public static function generarSemanas($fechaInicio, $fechaFin)
    {
        $inicio = new \DateTime($fechaInicio);
        $fin = new \DateTime($fechaFin);

        // Si la fecha de inicio no es lunes, llevarla al lunes anterior
        if ($inicio->format('N') != 1) {
            $inicio->modify('last monday');
        }

        $semanas = [];
        $numeroSemana = 1;

        while ($inicio <= $fin) {
            $semanaInicio = clone $inicio;
            $semanaFin = clone $inicio;
            $semanaFin->modify('sunday this week');

            if ($semanaFin > $fin) {
                $semanaFin = clone $fin;
            }

            $semanas[] = [
                'numero_semana' => $numeroSemana,
                'inicio' => $semanaInicio->format('Y-m-d'),
                'fin'    => $semanaFin->format('Y-m-d')
            ];

            $inicio->modify('+1 week');
            $numeroSemana++;
        }

        return $semanas;
    }

    public static function eliminarSemanas()
    {

        try {
            $idSemana = $_POST['idSemana'];
            $idCentro= $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];

            // Validar que la semana existe
            $stmtSemana = AnioFiscalModel::buscarSemana($idSemana);
            $semana= $stmtSemana->fetchAll(\PDO::FETCH_ASSOC);

            if (!$semana) {
                return ResponseHelper::error('La semana '. $idSemana .' no existe.');
            }

            // Verificar permisos si es necesario
            // if (!auth()->user()->can('eliminar_semanas')) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'No tienes permisos para eliminar semanas'
            //     ], 403);
            // }

            // Eliminar la semana
            AnioFiscalModel::eliminarSemana($idSemana, $idCentro);

            return ResponseHelper::success('Semana ' . $idSemana . ' eliminada correctamente');

        } catch (\Exception $e) {
            return ResponseHelper::error('Error al eliminar la semana '. $e->getMessage());
        }
    }

    public static function modificar()
    {
        try {
            if (empty($_POST['anio_fiscal_id']) || empty($_POST['tipo_modificacion']) || empty($_POST['monto'])) {
                throw new Exception("Datos incompletos");
            }

            $id     = $_POST['anio_fiscal_id'];
            $tipo   = $_POST['tipo_modificacion'];
            $monto  = (float) $_POST['monto'];
            $motivo = $_POST['motivo'] ?? "";
            $usuario = $_POST['usuario_id'];

            // Presupuesto actual
            $actual = AnioFiscalModel::getPresupuestoActual($id);
            if (!$actual) {
                throw new Exception("Año fiscal no encontrado");
            }

            $anterior = (float) $actual['presupuesto_actual'];
            $nuevo    = self::calcularNuevoPresupuesto($anterior, $monto, $tipo);

            // Registrar modificación
            AnioFiscalModel::insertModificacion([
                $id,
                $tipo,
                $anterior,
                $monto,
                $nuevo,
                $motivo,
                $usuario
            ]);

            // Actualizar
            AnioFiscalModel::updatePresupuesto($id, $nuevo);

            echo json_encode([
                "tipo" => "recargar",
                "titulo" => "Éxito",
                "texto" => "Presupuesto actualizado correctamente",
                "icono" => "success"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "tipo" => "simple",
                "titulo" => "Error",
                "texto" => $e->getMessage(),
                "icono" => "error"
            ]);
        }
    }

    private static function calcularNuevoPresupuesto($anterior, $monto, $tipo)
    {
        switch ($tipo) {
            case 'incremento':
                return $anterior + $monto;
            case 'decremento':
                $nuevo = $anterior - $monto;
                if ($nuevo < 0) throw new Exception('El presupuesto no puede ser negativo');
                return $nuevo;
            case 'ajuste':
                if ($monto < 0) throw new Exception('El presupuesto no puede ser negativo');
                return $monto;
            default:
                throw new Exception('Tipo de modificación no válido');
        }
    }
}
