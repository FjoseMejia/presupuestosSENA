<?php
namespace presupuestos\model;

use presupuestos\model\MainModel;
use PDO;
use PDOException;

class UserModel extends MainModel {	
    
    public function findByEmail(string $email) {
        $query= "SELECT u.*
            FROM user u
            WHERE email = :email";

        $params= ["email" => $email];
        $stmt = parent::executeQuery($query, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): array {
		$query = "INSERT INTO user (nombres, apellidos, numeroDocumento, email, password, idRolFK, idCentroFK)
				VALUES (:names, :lastNames, :idNumber,  :email, :password, NULL, :centro_id)";
		
		$params = [
				'names' => $data['names'],
				'lastNames' => $data['lastNames'],
				'idNumber'=> $data['idNumber'],
				'email' => $data['email'],
				'password'=> $data['password'],
                'centro_id'=> $data['centroId']
			];

		try {
			$stmt = parent::executeQuery($query, $params);
			if($stmt->rowCount() > 0){
				return ['success' => true];
			} else {
				return ['success' => false, 'error' => 'No se insertó ningún registro'];
			}
		} catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return [
                    'success' => false,
                    'error'   => 'El número de documento ya está registrado'
                ];
            }

			return ['success' => false, 'error' => $e->getMessage()];
		}
	}

    public function update(int $id, array $data): bool {

        $fields = [];
        $params = ['id' => $id];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        $query = "UPDATE user SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $stmt = parent::executeQuery($query, $params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
    }

    public function verifyAccount(int $userId): ?string{
        $query = "SELECT r.nombre AS rol, u.idRolFk AS rol_id
              FROM user u
              JOIN rol r ON u.idRolFk = r.idRol
              WHERE u.idUser = :id";
        $params = [':id' => $userId];
        $stmt = parent::executeQuery($query, $params);

        if ($stmt) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result && isset($result['rol'])) {
                // Guardar el ID del rol en sesión
                $_SESSION['user_rol_id'] = $result['rol_id'];
                return $result['rol'];
            }
        }

        return null; 
    }

    // Método específico para obtener solo el ID del rol
    public function getUserRoleId(int $userId): ?int {
        $query = "SELECT idRolFk AS rol_id FROM user WHERE idUser = :id";
        $params = [':id' => $userId];
        $stmt = parent::executeQuery($query, $params);

        if ($stmt) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? (int)$result['rol_id'] : null;
        }
        return null; 
    }

    public static function getSubdirector($centroId) {
        try {
            $query = "SELECT idUser, nombres, apellidos
                    FROM user 
                    WHERE idRolFk= 2
                    AND idCentroFk = ?
                    LIMIT 1";
            $stmt = parent::executeQuery($query, [$centroId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null; 
        } catch (PDOException $e) {
            return null;
        }
    }

    //probablemente toca eliminar
    public static function getAllUsersByCentro($centroId) {
        try {
            $query = "SELECT u.idUser, u.nombres, u.apellidos, u.email, u.esVerificado, r.nombre as nombre_rol
                    FROM user u
                    JOIN rol r ON u.idRolFk = r.idRol
                    WHERE u.idCentroFk = :idCentro";


            $stmt = parent::executeQuery($query, [":idCentro"=> $centroId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [$e->getMessage()];
        }
    }
}