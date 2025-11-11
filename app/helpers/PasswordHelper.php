<?php
namespace presupuestos\helpers;

class PasswordHelper{
	public static function hashPassword(string $password): string{
		$password= trim($password);
        $password= password_hash($password, PASSWORD_BCRYPT, ["cost"=>11]);
		
        return $password;
	}
}