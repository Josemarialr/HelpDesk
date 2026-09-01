<?php
require_once 'db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$user_id = $_GET['id'] ?? null;
if (!$user_id) { header("Location: admin_dashboard.php"); exit; }
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_info'])) {
        // Guardar credenciales de Email, Admin y también Dominio/Hosting/FTP
        $stmt = $pdo->prepare("UPDATE usuarios SET
            website_extra = ?, email_extra = ?, login_email_url = ?,
            user_email = ?, password_email = ?, login_admin_url = ?,
            user_admin = ?, password_admin = ?,
            dominio = ?, alta_dominio = ?, vencimiento_dominio = ?,
            alta_hosting = ?, vencimiento_hosting = ?,
            ftp_server = ?, ftp_usuario = ?, ftp_password = ?, ftp_puerto = ?
            WHERE id = ?");

        $stmt->execute([
            $_POST['website_extra'], $_POST['email_extra'], $_POST['login_email_url'],
            $_POST['user_email'], $_POST['password_email'], $_POST['login_admin_url'],
            $_POST['user_admin'], $_POST['password_admin'],
            $_POST['dominio'], !empty($_POST['alta_dominio']) ? $_POST['alta_dominio'] : null, !empty($_POST['vencimiento_dominio']) ? $_POST['vencimiento_dominio'] : null,
            !empty($_POST['alta_hosting']) ? $_POST['alta_hosting'] : null, !empty($_POST['vencimiento_hosting']) ? $_POST['vencimiento_hosting'] : null,
            $_POST['ftp_server'], $_POST['ftp_usuario'], $_POST['ftp_password'], $_POST['ftp_puerto'],
            $user_id
        ]);
        $success = "Toda la información y datos de infraestructura fueron actualizados.";
    } elseif (isset($_POST['agregar_email'])) {
        $email_adicional = trim($_POST['email_adicional']);
        if (!empty($email_adicional)) {
            $stmt = $pdo->prepare("INSERT INTO usuario_emails_adicionales (usuario_id, email_adicional) VALUES (?, ?)");
            $stmt->execute([$user_id, $email_adicional]);
            $success = "Email adicional agregado.";
        }
    } elseif (isset($_POST['eliminar_email'])) {
        $stmt = $pdo->prepare("DELETE FROM usuario_emails_adicionales WHERE id = ?");
        $stmt->execute([$_POST['email_id']]);
        $success = "Email adicional removido.";
    }
}

// Consultar datos actuales del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM usuario_emails_adicionales WHERE usuario_id = ?");
$stmt->execute([$user_id]);
$emails_adicionales = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cargar Info Completa - Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-danger">
    <div class="container">
        <span class="navbar-brand">Cargar Info Técnica para: <?= htmlspecialchars($user['nombre']) ?></span>
        <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">Volver al Panel</a>
    </div>
</nav>

<div class="container my-5">
    <?php if($success): ?> <div class="alert alert-success shadow-sm"><?= $success ?></div> <?php endif; ?>

    <form action="" method="POST">
        <div class="row">

            <div class="col-md-8 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-dark text-white"><h5>🌐 Infraestructura (Dominio, Hosting y FTP)</h5></div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Dominio Principal</label>
                                <input type="text" name="dominio" class="form-control" placeholder="ejemplo.com" value="<?= htmlspecialchars($user['dominio'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Alta Dominio</label>
                                <input type="date" name="alta_dominio" class="form-control" value="<?= $user['alta_dominio'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vencimiento Dominio</label>
                                <input type="date" name="vencimiento_dominio" class="form-control" value="<?= $user['vencimiento_dominio'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Alta Hosting</label>
                                <input type="date" name="alta_hosting" class="form-control" value="<?= $user['alta_hosting'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vencimiento Hosting</label>
                                <input type="date" name="vencimiento_hosting" class="form-control" value="<?= $user['vencimiento_hosting'] ?? '' ?>">
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-secondary mb-3">📂 Servidor de Archivos (FTP)</h6>
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label">FTP Server / Host</label>
                                <input type="text" name="ftp_server" class="form-control" placeholder="ftp.ejemplo.com o IP" value="<?= htmlspecialchars($user['ftp_server'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Puerto</label>
                                <input type="text" name="ftp_puerto" class="form-control" placeholder="21" value="<?= htmlspecialchars($user['ftp_puerto'] ?? '21') ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">FTP Usuario</label>
                                <input type="text" name="ftp_usuario" class="form-control" value="<?= htmlspecialchars($user['ftp_usuario'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">FTP Contraseña</label>
                                <input type="text" name="ftp_password" class="form-control" value="<?= htmlspecialchars($user['ftp_password'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-secondary text-white"><h5>🔑 Credenciales de Accesos (Emails y Paneles)</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Website Extra</label>
                                <input type="text" name="website_extra" class="form-control" value="<?= htmlspecialchars($user['website_extra'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Principal Vinc.</label>
                                <input type="email" name="email_extra" class="form-control" value="<?= htmlspecialchars($user['email_extra'] ?? '') ?>">
                            </div>
                        </div>
                        <hr>
                        <h6>E-mail Access</h6>
                        <div class="mb-3">
                            <label class="form-label">Login Email (URL)</label>
                            <input type="text" name="login_email_url" class="form-control" value="<?= htmlspecialchars($user['login_email_url'] ?? '') ?>">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">User Email</label>
                                <input type="text" name="user_email" class="form-control" value="<?= htmlspecialchars($user['user_email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Email</label>
                                <input type="text" name="password_email" class="form-control" value="<?= htmlspecialchars($user['password_email'] ?? '') ?>">
                            </div>
                        </div>
                        <hr>
                        <h6>Admin Panel Access</h6>
                        <div class="mb-3">
                            <label class="form-label">Login Admin (URL)</label>
                            <input type="text" name="login_admin_url" class="form-control" value="<?= htmlspecialchars($user['login_admin_url'] ?? '') ?>">
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">User Admin</label>
                                <input type="text" name="user_admin" class="form-control" value="<?= htmlspecialchars($user['user_admin'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Admin</label>
                                <input type="text" name="password_admin" class="form-control" value="<?= htmlspecialchars($user['password_admin'] ?? '') ?>">
                            </div>
                        </div>

                        <button type="submit" name="guardar_info" class="btn btn-success btn-lg w-100 fw-bold">💾 GUARDAR TODA LA INFORMACIÓN</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white"><h5>Agregar Más Emails</h5></div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <input type="email" name="email_adicional" id="email_input" class="form-control" placeholder="correo@dominio.com">
                            <button type="submit" name="agregar_email" onclick="return document.getElementById('email_input').value !== '';" class="btn btn-primary">Añadir</button>
                        </div>
                    </div>
                </div>
                <div class="card shadow">
                    <div class="card-header bg-light"><h5>Emails Adicionales Cargados</h5></div>
                    <ul class="list-group list-group-flush">
                        <?php if(empty($emails_adicionales)): ?> <li class="list-group-item text-muted text-center">No hay correos extras.</li> <?php endif; ?>
                        <?php foreach($emails_adicionales as $em): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <small><?= htmlspecialchars($em['email_adicional']) ?></small>
                                <button type="submit" name="eliminar_email" value="1" onclick="this.form.appendChild(Object.assign(document.createElement('input'),{type:'hidden',name:'email_id',value:'<?= $em['id'] ?>'}));" class="btn btn-danger btn-sm">❌</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

        </div>
    </form>
</div>
</body>
</html>
