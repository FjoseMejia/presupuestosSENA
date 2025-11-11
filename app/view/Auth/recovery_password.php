<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
	<link href="<?= APP_URL ?>css/auth/recovery.css" rel="stylesheet">
    <!--Css Bootstrap  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
	<header>
		
	</header>
	<main>
		<div class="login">
			<div class="form-container">
				<div class="logo-container">
					<img src="<?= APP_URL ?>/assets/img/logoSena.png" alt="logo-hiperauto" class="logo">
					<h1 class="title">Recuperacion de Contraseña</h1>
				</div>

				<div class="mb-2">
					<div class="alert d-none alert-warning" role="alert">
						<p class="text"></p>
					</div>
				</div>

				<form action="" class="form-login form-recovery">
					<label for="password" class="label">Nueva Contraseña</label>
					<input type="password" class="input" id="password" name="password" placeholder="********" required>

					<label for="re-password" class="label">Confirmar Contraseña</label>
					<input type="password" class="input" id="re-password" name="re-password" placeholder="********" required>

					<button type="submit" class="primary-button login-button">Actualizar Contraseña</button>

					<button type="button" class="secondary-button" onclick="window.location.href='<?= APP_URL ?>login'">Volver al inicio</button>
				</form>
			</div>
		</div>
	</main>
	<script>		
		let token = <?= isset($_GET["token"]) ? json_encode($_GET["token"]) : 'null' ?>;
		let email = <?= isset($_GET["email"]) ? json_encode($_GET["email"]) : 'null' ?>;
	</script>
	<script src="../../js/jquery-3.7.1.js"></script>
	<script src="../../js/recovery_password.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	
</body>
</html>