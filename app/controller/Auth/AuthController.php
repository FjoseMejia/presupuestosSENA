<?php

namespace presupuestos\controller\Auth;

use presupuestos\model\UserModel;
use presupuestos\helpers\ValidationHelper;
use presupuestos\helpers\PasswordHelper;
use presupuestos\exceptions\ValidationException;
use presupuestos\helpers\MailerHelper;
use presupuestos\helpers\TokenHelper;
use presupuestos\model\TokenModel;
use presupuestos\model\MainModel;
use presupuestos\model\AnioFiscalModel;
use presupuestos\helpers\ResponseHelper;
use PDO;


class AuthController{

    public function showLogin(){

        if (isset($_SESSION[APP_SESSION_NAME])) {
            header('Location: /dashboard');
            exit;
        }


        $getDepartaments = new MainModel();
        $queryDepartaments = "SELECT * FROM departamentos ";
        $stmt = $getDepartaments::executeQuery($queryDepartaments);
        $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../view/Auth/login.php';
    }

    public function getCentros(){
        if (isset($_GET['departamento'])) {
            $getCentro = new MainModel();
            $queryCentro = "SELECT * FROM centros WHERE idDepartamentoFK = :departamento";

            $stmt = $getCentro::executeQuery($queryCentro, [
                'departamento' => (int) $_GET['departamento']
            ]);
            $centros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($centros);
            exit;
        }
    }

    public function login(array $credentials){

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {

            $email = trim($credentials['email']);
            $password = trim($credentials['password']);

            if ($email === "" || $password === "") {
                echo json_encode([
                    'state' => 0,
                    'message' => "Todos los campos son obligatorios"
                ]);
                return;
            }

            $email = ValidationHelper::normalizeEmail($email);
            $userModel = new UserModel();
            $dataUser = $userModel->findByEmail($email);

            if (!$dataUser) {
                echo json_encode([
                    'state' => 0,
                    'message' => "Correo no registrado"
                ]);
                return;
            }

            if ($dataUser['esVerificado'] == 0) {
                echo json_encode([
                    'state' => 0,
                    'message' => "El correo no se encuentra verificado"
                ]);
                return;
            }

            if (!password_verify($password, $dataUser['password'])) {
                echo json_encode([
                    'state' => 0,
                    'message' => "Credenciales incorrectas"
                ]);
                return;
            }

            $centroId = $dataUser['idCentroFk'];
            //Obtengo todas las semanas
            $semanas = AnioFiscalModel::obtenerSemanaActiva($centroId);
            

            //Obtengo el año fiscal activo
            $anioFiscalActivo = AnioFiscalModel::getPresupuestoActivo($centroId);
            
            //Obtengo las semana por centro y la qué está activa
            $semanaActiva = AnioFiscalModel::obtenerSemanaActiva($centroId);

            //Guardar la semana activa, y el año fiscal activo
            $_SESSION[APP_SESSION_NAME] = [
                'idUsuarioSession'       => $dataUser['idUser'],
                'usuarioLogueadoSession' => $dataUser['nombres'].' '.$dataUser['apellidos'],
                'emailLoginSession'    => $dataUser['email'],
                'idCentroIdSession'     => $dataUser['idCentroFk'],
                'idRolSession' => $dataUser['idRolFk'],
                'idSemanaActivaSession' => $semanaActiva['idSemana'],
                'idAnioFiscalActivoSession' => $anioFiscalActivo,
            ];

            // GUARDAR EL ID DEL ROL EN SESIÓN PARA USO EN EL MODAL
            $_SESSION['user_rol_id'] = $dataUser['idRolFk'];

            echo json_encode([
                'state' => 1,
                'message' => "Login exitoso",
                'redirect' => APP_URL . "dashboard"
            ]);
            return;
        } catch (ValidationException $e) {
            echo json_encode([
                'state' => 0,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'state' => 0,
                'message'=> 'Error 505. Consulte con el administrador',
            ]);
        }
    }

    public function register(array $data){
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {

            $names = trim($data['names'] ?? '');
            $lastNames = trim($data['lastNames'] ?? '');
            $idNumber = trim($data['idNumber'] ?? '');
            $email = trim($data['emailSena']);
            $password = trim($data['password']);
            $rePassword = trim($data['rePassword']);
            $idCentro = trim($data['idCentro']);

            if (!$names || !$lastNames || !$email || !$password || !$rePassword || !$idCentro) {
                echo json_encode([
                    'state' => 0,
                    'message' => "Todos los campos son obligatorios"
                ]);
                return;
            }

            if ($password !== $rePassword) {
                echo json_encode([
                    'state' => 0,
                    'message' => "Las contraseñas no coinciden"
                ]);
                return;
            }

            $email = ValidationHelper::normalizeEmail($email);

            $userModel = new UserModel();
            if ($userModel->findByEmail($email)) {
                echo json_encode([
                    'state' => 0,
                    'message' => "El correo ya está registrado"
                ]);
                return;
            }

            #Ingresar la validación del Rol Por defecto está como 4. 
            $hashed = PasswordHelper::hashPassword($password);
            $result = $userModel->create([
                'names' => $names,
                'lastNames' => $lastNames,
                'idNumber' => $idNumber,
                'email' => $email,
                'password' => $hashed,
                'centroId' => $idCentro
            ]);

            if ($result['success']) {

                $mailer = new MailerHelper();
                $tokenHelper = new TokenHelper();
                $tokenModel = new TokenModel();

                $token = $tokenHelper::generateToken();
                $expiresAt = $tokenHelper::expiration();

                $user = $userModel->findByEmail($email);

                $tokenModel->create($user['idUser'], $token, 'verification', $expiresAt);


                $sent = $mailer->sendVerificationEmail([
                    'name' => $user['nombres'],
                    'lastName' => $user['apellidos'] ?? $lastNames,
                    'email' => $user['email'] ?? $email
                ], $token);

                echo json_encode([
                    'state' => $sent === true ? 1 : 0,
                    'message' => $sent === true
                        ? "Registro exitoso. Verifica tu correo para activar tu cuenta."
                        : "Registro exitoso, pero hubo un error enviando el correo: $sent"

                        //Imprimir $sent para ver el error
                ]);
            } else {
                echo json_encode([
                    'state' => 0,
                    'message' => "Error: " . $result['error']
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'state' => 0,
                'message' => "Error del sistema. Intenta más tarde." . $e->getMessage()
            ]);
        }
    }

    public function recoveryPassword(array $data){
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        $email = trim($data['email']);
        $email = ValidationHelper::normalizeEmail($email);

        $userModel = new UserModel();
        $dataUser = $userModel->findByEmail($email);

        if (!$dataUser) {
            echo json_encode([
                'state' => 0,
                'message' => "Correo no registrado"
            ]);
            return;
        }

        $mailer = new MailerHelper();
        $tokenHelper = new TokenHelper();
        $tokenModel = new TokenModel();

        $token = $tokenHelper::generateToken();
        $expiresAt = tokenHelper::expiration();

        $tokenModel->create($dataUser['idUser'], $token, 'recovery', $expiresAt);
      
        $sent = $mailer->sendRecoveryEmail([
            'name' => $dataUser['nombres'],
            'lastName' => $dataUser['apellidos'],
            'email' => $dataUser['email'] ?? $email
        ], $token);

        if($sent== true){
            ResponseHelper::success("Correo de recuperación enviado correctamente.");
        } else {
            ResponseHelper::error("Error al enviar el correo: $sent");
        }
        
        return;
    }

    public function showSendSuccessful(){
        $email = $_GET['email'] ?? '';
        require __DIR__ . '/../../view/Auth/send_successful.php';
        exit;
    }

    public function showRecoveryPassword(){

        require __DIR__ . '/../../view/Auth/recovery_password.php';
    }

    public function verify(){
        $token = $_GET['token'] ?? null;
        if (!$token) {
            require __DIR__ . "/../../view/errors/404.php";
            exit;
        }

        $tokenModel = new TokenModel();
        $tokenData = $tokenModel->findByToken($token);

        if (!$tokenData) {
            require __DIR__ . "/../../view/errors/404.php";
            exit;
        }

        if ($tokenData['type'] == 'verification') {
            if (strtotime($tokenData['expiresAt']) < time()) {
                //$tokenModel->deleteByToken($token);
                require __DIR__ . "/../../view/errors/invalid_token.php";
                exit;
            } else {
                $userModel = new UserModel();
                $userModel->verifyAccount((int)$tokenData['idUser']);
                //$tokenModel->deleteByToken($token);

                $_SESSION["message"] = "Correo Actualizado Correctamente";
                header("Location: /login");
                exit;
            }
        } elseif ($tokenData['type'] == 'recovery') {
            if (strtotime($tokenData['expiresAt']) < time()) {
                $tokenModel->deleteByToken($token);
                require __DIR__ . "/../../view/errors/404.php";
                exit;
            }
        }
    }

    public function logout(){
        session_start();
        session_unset();
        session_destroy();
        header("Location: " . APP_URL . "login");
        exit;
    }
}