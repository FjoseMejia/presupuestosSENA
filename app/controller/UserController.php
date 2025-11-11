<?php
namespace presupuestos\controller;
use presupuestos\model\UserModel;
use presupuestos\helpers\HtmlResponse;

class UserController {

    /** Listar todos los usuarios de un centro */
    public static function listUsersByCentro($centroId) {
        return UserModel::getAllUsersByCentro($centroId);
    }

    /** Obtener un usuario por email */
    public static function findByEmail(string $email) {
        $userModel = new UserModel();
        return $userModel->findByEmail($email);
    }

    /** Crear un nuevo usuario */
    public static function create(array $data): array {
        $userModel = new UserModel();
        return $userModel->create($data);
    }

    /** Actualizar un usuario */
    public static function update(int $id, array $data){
        $userModel= new UserModel();
        $respuesta= $userModel->update($id, $data);
        if($respuesta){
            header("Location: http://presupuestos.local/gestionar/usuarios");
        }else{
            header("Location: http://presupuestos.local/gestionar/usuarios");
        }
        exit;
    }

    /** Verificar una cuenta de usuario */
    public static function verifyAccount(int $userId){
        $userModel = new UserModel();
        return $userModel->verifyAccount($userId);
    }

    /** Obtener al subdirector de un centro */
    public static function getSubdirector($centroId) {
        return UserModel::getSubdirector($centroId);
    }
}
