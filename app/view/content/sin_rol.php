<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>No tienes rol asignado | <?= APP_NAME ?> </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center" style="height:100vh;">

    <div class="card text-center p-5 shadow">
        <h2 class="mb-3 text-warning">⚠️ Sin rol asignado</h2>
        <p class="mb-4">Tu usuario aún no tiene un rol asignado en el sistema.</p>
        <p class="mb-4">Contacta al administrador para poder acceder al dashboard.</p>
        <a href="<?= APP_URL ?>logout-post" class="btn btn-primary">Volver al login</a>
    </div>

</body>

</html>