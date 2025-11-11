<?php
namespace presupuestos\model;
require_once __DIR__ . '/../../config/server.php';
use PDO;
use PDOException;
use Exception;

class MainModel{
	private static $server= DB_SERVER;
	private static $db= DB_NAME;
	private static $user= DB_USER;
	private static $password= DB_PASSWORD;
	
	protected static function getConnection(): PDO {
        try {
			$dsn= "mysql: host=". self::$server. "; dbname=". self::$db ."; charset=utf8mb4";			
            $pdo= new PDO($dsn, self::$user, self::$password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);			
            return $pdo;
        } catch (PDOException $e) {
			//No olvidar mandarlo a un archivo LOGS
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
	
	public static function executeQuery($query, array $params= []){
		$pdo= self::getConnection();
		//instruction SQL prepared
		$stmt= $pdo->prepare($query);
		$stmt->execute($params);		
		return $stmt;
	}
}