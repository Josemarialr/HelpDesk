<?php
require_once 'db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO administradores (username, password, nombre) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $nombre]);
        $success = "Nuevo administrador registrado correctamente.";
    } catch (\PDOException $e) {
        $error = "El nombre de usuario ya está registrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-danger">
    <div class="container">
        <span class="navbar-brand">Panel Admin - Nuevo Administrador</span>
        <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">Volver al Panel</a>
    </div>
</nav>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-dark text-white"><h5>Crear Cuenta de Administrador</h5></div>
                <div class="card-body">
                    <?php if($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>
                    <?php if($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario (Username)</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Registrar Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
