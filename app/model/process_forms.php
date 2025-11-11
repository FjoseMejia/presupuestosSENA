<?php
class ProcessLogin{
	public $dataUser;
	public $cursor;
	
	public function __construct(array $dataUser, $cursor){
		$this->dataUser= $dataUser;
		$this->cursor= $cursor;
	}
	
	public function verificateUser(){
		$queryUser = "SELECT * FROM user WHERE email = '{$this->dataUser['email']}' AND verificado= '1'";
		return mysqli_query($this->cursor, $queryUser);
	}
}


Class ProcessRegister{
	public $names;
	public $lastNames;
	public $typeDocument;
	public $idNumber;
	public $numberPhone;
	public $address;
	public $email;
	public $password;
	public $cursor;
	
	public function __construct(array $dataUser, $cursor){
		$this->names= $dataUser['names'];
		$this->lastNames= $dataUser['lastNames'];
		$this->typeDocument= $dataUser['typeDocument'];
		$this->idNumber= $dataUser['idNumber'];
		$this->numberPhone= $dataUser['numberPhone'];
		$this->address= $dataUser['address'];
		$this->email= $dataUser['email'];
		$this->password= $dataUser['password'];
		$this->cursor= $cursor;
	}
	
	public function verificateData(){
		$queryUser= "SELECT * FROM user WHERE email= '{$this->email}' 
					OR numero_documento= '{$this->idNumber}'";
		return mysqli_query($this->cursor, $queryUser);
	}
		
	public function saveUser(){
		$query= "INSERT INTO user (nombres, apellidos, tipo_documento_id, numero_documento, telefono, direccion, email, password) 
				VALUES ('{$this->names}', '{$this->lastNames}', '{$this->typeDocument}', '{$this->idNumber}', '{$this->numberPhone}', 
						'{$this->address}', '{$this->email}', '{$this->password}')";
		return mysqli_query($this->cursor, $query);
	}
}

class ProcessRecovery extends ProcessLogin{
	
	
	public function verificateUser(){
		$queryUser = "SELECT user.id, user.email FROM user WHERE email = '{$this->dataUser['email']}'";
		return mysqli_query($this->cursor, $queryUser);
	}
	
	public function saveToken($id, $token, $expiracion){
		$query= "INSERT INTO tokens_recuperacion (usuario_id, token, expiracion) VALUES ($id, '$token', '$expiracion')";
		return mysqli_query($this->cursor, $query);
	}
	
	public function verificateToken($dateNow){
		$query= "SELECT tr.token, tr.expiracion
			FROM tokens_recuperacion tr
			JOIN user u ON tr.usuario_id = u.id
			WHERE u.email = '{$this->dataUser['email']}'
			AND tr.token = '{$this->dataUser['token']}'
			AND tr.expiracion> '$dateNow'
			AND tr.utilizado= 0"
		;		
		
		return mysqli_query($this->cursor, $query);		
	}
	
	public function updatePassword(){
		$updatePassword= "UPDATE user SET password = '{$this->dataUser['newPassword']}' WHERE email= '{$this->dataUser['email']}'";
		$updateToken = "
			UPDATE tokens_recuperacion tr
			JOIN user u ON tr.usuario_id = u.id
			SET tr.utilizado = 1
			WHERE u.email = '{$this->dataUser['email']}' 
			AND tr.token = '{$this->dataUser['token']}'
		";
		
		mysqli_query($this->cursor, $updateToken);
		return mysqli_query($this->cursor, $updatePassword);		
	}
}

