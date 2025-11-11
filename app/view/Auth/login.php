<?php

$typeMessage = '';
$contentMessage = '';

if (isset($_SESSION['message'])) {
	//$typeMessage= 'error';
	$contentMessage = $_SESSION['message'];
	unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<link rel="icon" href="<?= APP_URL ?>assets/img/sena.png" type="image/png">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Iniciar Sessión</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
	<link href="<?= APP_URL ?>css/auth/login.css" rel="stylesheet">

</head>

<body>
	<header>

	</header>

	<main>

		<div class="alerts">
			<div class="container-alert">
				<p class="alert-text"><?= $contentMessage ?? "" ?></p>
			</div>
		</div>

		<div class="login">
			<div class="form-container">
				<div class="logo-container">
					<img src="<?= APP_URL ?>/assets/img/logoSena.png" alt="logo-hiperauto" class="logo">
					<h1 class="title">Sistema de Presupuestos</h1>
				</div>

				<form action="" class="form-login">
					<label for="email" class="label">Correo Electrónico</label>
					<input type="text" id="email" placeholder="Usuario" required class="input input-username" value="<?= $_GET['email'] ?? '' ?>">

					<label for="password" class="label">Contraseña</label>
					<input type="password" id="password" placeholder="*********" required class="input input-password">

					<button type="submit" class="primary-button login-button">Iniciar Sesión</button>
					<a href="#" class="remember_password">Olvidé mi contraseña</a>
				</form>

				<button type="button" class="secondary-button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
					Registrarse
				</button>
			</div>
		</div>
	</main>

	<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-scrollable modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="staticBackdropLabel">Registro de Usuarios</h1>
					<button type="button" class="btn-close btns-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form action="" class="form-registro">
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<label for="names" class="form-label">Nombres</label>
									<input type="text" class="form-control inputRegister" id="names">
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<label for="last-names" class="form-label">Apellidos</label>
									<input type="text" class="form-control inputRegister" id="last-names">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<label for="id_number" class="form-label">Número de identificación</label>
									<input type="text" class="form-control inputRegister" id="id-number">
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<label for="email-sena" class="form-label">Correo SENA</label>
									<input type="email" class="form-control inputRegister" id="email-sena">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<label for="password-user" class="form-label">Contraseña</label>
									<input type="password" class="form-control inputRegister" id="password-user">
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<label for="password-user" class="form-label">Confirmar Contraseña</label>
									<input type="password" class="form-control inputRegister" id="re-password">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<label for="departmento" class="form-label">Departamento</label>
									<select class="form-select inputRegister" id="departamento">
										<option selected disabled>Seleccione una opción</option>
										<?php
										foreach ($departamentos as $departamento) {
											echo "
											<option value='{$departamento['idDepartamentos']}'>{$departamento['departamento']}</option>
										
										";
										}
										?>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<label for="centro" class="form-label">Centro de formación</label>
									<select class="form-select inputRegister" id="centro">
										<option selected disabled>Seleccione una opción</option>
									</select>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btns-close" data-bs-dismiss="modal">Cerrar</button>
					<button type="button" class="btn btn-primary signup-button" id="signup-button">Registrar</button>
				</div>
			</div>
		</div>
	</div>
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="js/auth/auth.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>

</body>

</html>