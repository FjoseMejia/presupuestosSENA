<?php

// if(!isset($_GET["email"])){
// 	header("location: page_not_found.php");
// }
// $email= $_GET["email"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correo enviado</title>
    <!--Css Bootstrap  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
	<link href="<?= APP_URL ?>css/auth/send.css" rel="stylesheet">
	
	<style>
		/* Fallback mínimo por si fallara la carga del CSS */
		*{ font-family: Calibri, 'Segoe UI', Arial, Helvetica, sans-serif; }
	</style>
</head>
<body>
    <main>
		<div class="login">
			<div class="form-container">
				<div class="logo-container">
					<img src="<?= APP_URL ?>/assets/img/logoSena.png" alt="logo-hiperauto" class="logo">
					<h1 class="title">Correo Enviado</h1>
				</div>

				<div class="text-center mb-4">
					<p class="m-0"><small>Revisa tu bandeja de entrada para ver las instrucciones</small></p>
				</div>

				<div class="text-center mb-4">
					<i class="bi bi-envelope-check text-success" style="font-size: 5rem;"></i>
				</div>

				<div class="mb-4">
					<a class="primary-button login-button" href="<?= APP_URL ?>login" role="button">Volver al inicio</a>
				</div>

				<div class="text-center">
					<span>¿No recibió el correo?</span>
					<a class="link-success link-opacity-75 link-underline-light" id="resend-email" href="#">Reenviar</a>
				</div>
			</div>
		</div>
	</main>
	<script>
		let email= <?= json_encode($email)?>;
	</script>
	<script src="../../js/jquery-3.7.1.js"></script>
	<script src="../../js/reesend_email.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>