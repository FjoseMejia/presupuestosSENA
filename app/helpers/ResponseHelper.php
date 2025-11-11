<?php
namespace presupuestos\helpers;

class ResponseHelper{
    public static function success(string $message = "Operación exitosa", array $data = []): void
    {
        echo json_encode([
            'state'   => 1,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message = "Ocurrió un error", array $data = []): void
    {
        echo json_encode([
            'state'   => 0,
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }

    public static function response(int $state, string $message, array $data = []): void
    {
        echo json_encode([
            'state'   => $state,
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }
}
