<?php
namespace presupuestos\model\role;
use presupuestos\model\MainModel;
use PDO;

class RoleModel extends MainModel{
    
    public function getAll(): array {
        $query = "SELECT * FROM rol ORDER BY nombre";
        $stmt = parent::executeQuery($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $nombre): bool {
        $query = "INSERT INTO rol (nombre) VALUES (:nombre)";
        $stmt = parent::executeQuery($query, ['nombre' => $nombre]);
        return $stmt->rowCount() > 0;
    }
}
