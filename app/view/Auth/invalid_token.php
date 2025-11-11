
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Token inválido o expirado</title>
    <link href="<?= APP_URL ?>css/errors/invalid_token.css" rel="stylesheet">
</head>
<body>
    <div class="error-container">
        <div class="logo-sena">
            <img src="<?= APP_URL ?>/assets/img/logoSena.png" alt="Logo SENA" height="45">
        </div>
        <div class="error-message">
            <div class="error-title">
                <h1>¡Enlace inválido o expirado!</h1>
            </div>
            <div class="error-description">
                <p>El enlace de verificación ya no es válido o ha caducado.</p>
                <a href="<?= APP_URL ?>resend-verification">Solicita uno nuevo</a>
            </div>
        </div>
    </div>
</body>
</html>
