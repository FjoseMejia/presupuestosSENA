<?php
namespace presupuestos\helpers;

use presupuestos\exceptions\ValidationException;

class ValidationHelper {
    public static function normalizeEmail(string $email): string {
        $email = strtolower(trim($email));
        $regex = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/';

        if (!preg_match($regex, $email)) {
            throw new ValidationException("Digite un correo válido");
        }
        return $email;
    }
    
    public static function normalizePassword(string $password):string{
		$password = strtolower(trim($password));
		$regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&+=]).{8,24}$/";
		//hacer el preg_match
	}
}


