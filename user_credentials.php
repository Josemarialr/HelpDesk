<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM usuario_emails_adicionales WHERE usuario_id = ?");
$stmt->execute([$user_id]);
$emails_adicionales = $stmt->fetchAll();

function formatearFecha($fecha) {
    return (!empty($fecha) && $fecha !== '0000-00-00') ? date('d/m/Y', strtotime($fecha)) : 'No cargada';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>

      <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
      
      <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
      <link rel="stylesheet" href="assets/css/Login-Form-Basic.css">
      <link rel="stylesheet" href="assets/css/styles.css">

    <meta charset="UTF-8">
    <title>Mis Credenciales de Soporte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark no-print">
    <div class="container">
        <span class="navbar-brand">Mis Datos y Accesos Técnicos</span>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Volver a Mis Tickets</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                    <div>
                        <h3>Ficha Credencial de Soporte Técnico</h3>
                        <p class="text-muted mb-0">Cliente: <strong><?= htmlspecialchars($user['nombre']) ?></strong></p>
                    </div>
                    <button onclick="window.print();" class="btn btn-primary no-print fw-bold">🖨 Imprimir / Guardar PDF</button>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="p-3 border rounded bg-white shadow-sm">
                            <h5 class="text-primary border-bottom pb-2">🌐 Detalles de Dominio y Alojamiento</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Dominio:</strong></p>
                                    <a href="http://<?= htmlspecialchars($user['dominio'] ?? '') ?>" target="_blank" class="fw-bold fs-5"><?= htmlspecialchars($user['dominio'] ?? 'No asignado') ?></a>
                                </div>
                                <div class="col-md-4 border-start">
                                    <p class="mb-1 text-muted"><small>CONTRATO DOMINIO</small></p>
                                    <p class="mb-1"><strong>Alta:</strong> <?= formatearFecha($user['alta_dominio'] ?? null) ?></p>
                                    <p class="mb-0 text-danger"><strong>Vence:</strong> <?= formatearFecha($user['vencimiento_dominio'] ?? null) ?></p>
                                </div>
                                <div class="col-md-4 border-start">
                                    <p class="mb-1 text-muted"><small>CONTRATO HOSTING</small></p>
                                    <p class="mb-1"><strong>Alta:</strong> <?= formatearFecha($user['alta_hosting'] ?? null) ?></p>
                                    <p class="mb-0 text-danger"><strong>Vence:</strong> <?= formatearFecha($user['vencimiento_hosting'] ?? null) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <h6 class="fw-bold text-secondary">📂 Conexión FTP (Servidor de Archivos)</h6>
                            <hr class="mt-1">
                            <p class="mb-1"><strong>Servidor / Host:</strong> <code><?= htmlspecialchars($user['ftp_server'] ?? 'No asignado') ?></code></p>
                            <p class="mb-1"><strong>Puerto:</strong> <code><?= htmlspecialchars($user['ftp_puerto'] ?? '21') ?></code></p>
                            <p class="mb-1"><strong>Usuario FTP:</strong> <?= htmlspecialchars($user['ftp_usuario'] ?? 'No asignado') ?></p>
                            <p class="mb-0"><strong>Contraseña:</strong> <code><?= htmlspecialchars($user['ftp_password'] ?? 'No asignado') ?></code></p>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <h6 class="fw-bold text-secondary">📧 Correos Electrónicos Adicionales</h6>
                            <hr class="mt-1">
                            <ul class="list-group list-group-flush bg-transparent">
                                <?php if(empty($emails_adicionales)): ?>
                                    <li class="list-group-item bg-transparent text-muted px-0 py-1">No se cargaron correos extras.</li>
                                <?php endif; ?>
                                <?php foreach($emails_adicionales as $em): ?>
                                    <li class="list-group-item bg-transparent px-0 py-1">▪ <?= htmlspecialchars($em['email_adicional']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h6 class="fw-bold text-secondary">📩 Acceso a Correo Web (Webmail)</h6>
                            <hr class="mt-1">
                            <p class="mb-1"><strong>Link Login:</strong> <a href="<?= htmlspecialchars($user['login_email_url'] ?? '#') ?>" target="_blank"><?= htmlspecialchars($user['login_email_url'] ?? 'No asignado') ?></a></p>
                            <p class="mb-1"><strong>Usuario:</strong> <?= htmlspecialchars($user['user_email'] ?? 'No asignado') ?></p>
                            <p class="mb-0"><strong>Contraseña:</strong> <code><?= htmlspecialchars($user['password_email'] ?? 'No asignado') ?></code></p>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h6 class="fw-bold text-secondary">⚙ Acceso Administrativo (CMS / Web Panel)</h6>
                            <hr class="mt-1">
                            <p class="mb-1"><strong>Link Admin:</strong> <a href="<?= htmlspecialchars($user['login_admin_url'] ?? '#') ?>" target="_blank"><?= htmlspecialchars($user['login_admin_url'] ?? 'No asignado') ?></a></p>
                            <p class="mb-1"><strong>Usuario Admin:</strong> <?= htmlspecialchars($user['user_admin'] ?? 'No asignado') ?></p>
                            <p class="mb-0"><strong>Contraseña Admin:</strong> <code><?= htmlspecialchars($user['password_admin'] ?? 'No asignado') ?></code></p>
                        </div>
                    </div>
                </div>

                <?php if(!empty($user['website_extra'])): ?>
                    <div class="mt-3 no-print text-center">
                        <small class="text-muted">Enlace complementario cargado: <a href="<?= htmlspecialchars($user['website_extra']) ?>" target="_blank"><?= htmlspecialchars($user['website_extra']) ?></a></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
