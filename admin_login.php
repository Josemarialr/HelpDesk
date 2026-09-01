<?php
require_once 'db.php';
// Línea temporal para forzar la actualización de la clave del admin
//$pdo->query("UPDATE administradores SET password = '" . password_hash('admin123', PASSWORD_BCRYPT) . "' WHERE username = 'admin'");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['nombre'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Credenciales de administrador inválidas.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-lg mt-5">
                <div class="card-header bg-danger text-white text-center">
                    <h4>HELP DESK - ADMIN</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-dark">Usuario Administrador</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Acceder al Panel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
