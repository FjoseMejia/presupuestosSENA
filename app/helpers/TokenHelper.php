<?php
namespace presupuestos\helpers;

class TokenHelper {

    public static function generateToken(int $length = 64): string {
        return bin2hex(random_bytes($length / 2)); // genera token seguro
    }

    public static function expiration(int $hours = 1): string {
        return date('Y-m-d H:i:s', strtotime("+$hours hour"));
    }

    public static function isExpired(string $expiresAt): bool {
        return strtotime($expiresAt) < time();
    }
}
