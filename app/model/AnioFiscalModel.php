<?php

namespace presupuestos\model;

require_once __DIR__ . "/MainModel.php";

use PDO;
use Exception;

class AnioFiscalModel extends MainModel
{
    public static function crearAnioFiscalConSemanas(array $datosAnioFiscal, array $semanas, int $centroId): bool
    {

        $pdo = self::getConnection();
        $pdo->beginTransaction();

        try {
            // 1. Insertar año fiscal
            $ok = self::insert($datosAnioFiscal);
            if (!$ok) {
                throw new Exception("No se pudo registrar el año fiscal");
            }

            // 2. Si está activo, desactivar otros
            if ($datosAnioFiscal[7] === 1) {
                $idNuevo = self::getLastInsertId();
                self::desactivarOtros($idNuevo, $datosAnioFiscal[2]);
            }

            // 3. Guardar semanas
            foreach ($semanas as $semana) {
                $existe = self::verificarSemanaExistente($semana['numerosemana'], $centroId);
                if (!$existe) {
                    self::insertarSemanaCarga(
                        $semana['numerosemana'],
                        $semana['inicio'],
                        $semana['fin'],
                        $centroId
                    );
                }
            }

            self::activarSemanaActualAutomaticamente($centroId);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function insert($params){
        $query = "INSERT INTO aniosfiscales 
                 (idSubdirectorFk, idCreadoPorFk, anioFiscal, valorAnioFiscal, presupuestoActual, fechaInicio, fechaCierre, estado, idVigenciaFiscalFk idCentroFk) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return self::executeQuery($query, $params);
    }

    public static function updatePresupuesto($id, $nuevoMonto)
    {
        $query = "UPDATE aniosfiscales SET presupuestoActual = ? WHERE idAniFiscal = ?";
        return self::executeQuery($query, [$nuevoMonto, $id]);
    }

    public static function getPresupuestoActual($id)
    {
        $query = "SELECT presupuestoActual FROM aniosfiscales WHERE idAniFiscal = ?";
        $stmt  = self::executeQuery($query, [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function insertModificacion($params)
    {
        $query = "INSERT INTO modificacionespresupuesto 
                 (anio_fiscal_id, tipo_modificacion, monto_anterior, monto_modificacion, monto_nuevo, motivo, usuario_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        return self::executeQuery($query, $params);
    }

    public static function desactivarOtros($id_excluir, $year)
    {
        $query = "UPDATE aniosfiscales SET estado = 0 WHERE idAniFiscal != ? AND anioFiscal != ? AND estado = 1";
        return self::executeQuery($query, [$id_excluir, $year]);
    }

    public static function getSubdirectores()
    {
        $query = "SELECT idUser, nombres, apellidos FROM user WHERE estado = 1 AND rol_id= 2";
        $stmt  = self::executeQuery($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== Método público para obtener el último ID insertado =====
    public static function getLastInsertId(){
        return self::getConnection()->lastInsertId();
    }

    public static function getPresupuestoActivo($id_centro)
    {
        $query = "SELECT * FROM aniosfiscales WHERE estado = 1 AND idCentroFk = ? LIMIT 1";
        $stmt  = self::executeQuery($query, [$id_centro]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function verificarSemanaExistente(int $numeroSemana, int $centroId): bool
    {
        $sql = "SELECT COUNT(*) FROM semanascarga WHERE numeroSemana = ? AND idCentroFk = ?";
        $stmt = self::executeQuery($sql, [$numeroSemana, $centroId]);
        return $stmt->fetchColumn() > 0;
    }

    public static function insertarSemanaCarga(int $numeroSemana, string $fechaInicio, string $fechaFin, int $centroId): void
    {

        $sql = "INSERT INTO semanascarga (numeroSemana, fechaInicio, fechaFin, idCentroFk) VALUES (?, ?, ?, ?)";
        self::executeQuery($sql, [$numeroSemana, $fechaInicio, $fechaFin, $centroId]);
    }

    public static function buscarSemana($idSemana)
    {
        $query= "SELECT * 
                FROM semanascarga sc
                WHERE sc.idSemana= :idSemana
        ";

        $param=[
            ':idSemana'=> $idSemana
        ];

        return self::executeQuery($query, $param);

    }

    public static function eliminarSemana($idSemana, $idCentro)
    {
        $query= "UPDATE semanascarga
            SET archivoCdp = '',
                archivoRp = '',
                archivoPagos= ''
            WHERE idSemana = :idSemana
            AND idCentroFk= :idCentro
        ";

        $param= [
            ':idSemana'=> $idSemana,
            ':idCentro'=> $idCentro
        ];
        return self::executeQuery($query, $param);
    }

    public static function activarSemanaActualAutomaticamente(int $centroId): bool
    {
        $pdo = self::getConnection();
        $pdo->beginTransaction();

        try {
            $fechaActual = date('Y-m-d');

            $sql = "SELECT id, numeroSemana 
                FROM semanascarga 
                WHERE idCentroFk = ? 
                AND ? BETWEEN inicio AND fin 
                AND activa = 0
                LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$centroId, $fechaActual]);
            $semanaActual = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($semanaActual) {
                // Primero desactivar todas las semanas del centro
                $sqlDesactivar = "UPDATE semanascarga SET activa = 0 WHERE centro_id = ?";
                $stmtDesactivar = $pdo->prepare($sqlDesactivar);
                $stmtDesactivar->execute([$centroId]);

                // Luego activar la semana actual
                $sqlActivar = "UPDATE semanascarga SET activa = 1 WHERE id = ?";
                $stmtActivar = $pdo->prepare($sqlActivar);
                $stmtActivar->execute([$semanaActual['id']]);

                error_log("Semana " . $semanaActual['numeroSemana'] . " activada automáticamente para el centro " . $centroId);
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error al activar semana automáticamente: " . $e->getMessage());
            return false;
        }
    }
    public static function verificarYActivarSemanaActual(int $centroId): bool
    {
        $pdo = self::getConnection();
        $pdo->beginTransaction();

        try {
            $fechaActual = date('Y-m-d');

            // 1. Obtener la semana actualmente activa
            $sqlSemanaActiva = "SELECT id, numero_semana, inicio, fin 
                           FROM semanascarga 
                           WHERE centro_id = ? AND activa = 1
                           LIMIT 1";
            $stmtActiva = $pdo->prepare($sqlSemanaActiva);
            $stmtActiva->execute([$centroId]);
            $semanaActivaActual = $stmtActiva->fetch(PDO::FETCH_ASSOC);

            // 2. Verificar si la semana activa actual todavía es válida
            $debeCambiar = false;
            if ($semanaActivaActual) {
                // Si la fecha actual NO está en el rango de la semana activa, cambiar
                if ($fechaActual < $semanaActivaActual['inicio'] || $fechaActual > $semanaActivaActual['fin']) {
                    $debeCambiar = true;
                }
            } else {
                // Si no hay semana activa, buscar una
                $debeCambiar = true;
            }

            // 3. Si es necesario cambiar, buscar y activar la nueva semana
            if ($debeCambiar) {
                // Buscar la semana que corresponde a la fecha actual
                $sqlNuevaSemana = "SELECT id, numero_semana 
                              FROM semanascarga 
                              WHERE centro_id = ? 
                              AND ? BETWEEN inicio AND fin 
                              LIMIT 1";

                $stmtNueva = $pdo->prepare($sqlNuevaSemana);
                $stmtNueva->execute([$centroId, $fechaActual]);
                $nuevaSemana = $stmtNueva->fetch(PDO::FETCH_ASSOC);

                if ($nuevaSemana) {
                    // Desactivar todas las semanas del centro
                    $sqlDesactivar = "UPDATE semanascarga SET activa = 0 WHERE centro_id = ?";
                    $stmtDesactivar = $pdo->prepare($sqlDesactivar);
                    $stmtDesactivar->execute([$centroId]);

                    // Activar la nueva semana
                    $sqlActivar = "UPDATE semanascarga SET activa = 1 WHERE id = ?";
                    $stmtActivar = $pdo->prepare($sqlActivar);
                    $stmtActivar->execute([$nuevaSemana['id']]);

                    error_log("Semana cambiada automáticamente: " . $nuevaSemana['numero_semana'] . " para centro " . $centroId);
                } else {
                    error_log("No se encontró semana para la fecha " . $fechaActual . " en centro " . $centroId);
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error al verificar semana actual: " . $e->getMessage());
            return false;
        }
    }

    public static function obtenerSemanaActiva(int $centroId): ?array
    {
        $pdo = self::getConnection();

        // Primero, verificar y activar la semana actual si es necesario
        self::verificarYActivarSemanaActual($centroId);

        // Luego obtener la semana activa
        $sql = "SELECT sc.* 
            FROM semanascarga sc
            WHERE sc.idCentroFk = ? AND sc.semanaActiva = 1
            LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$centroId]);
        $semanaActiva = $stmt->fetch(PDO::FETCH_ASSOC);

        return $semanaActiva ?: null;
    }
}
