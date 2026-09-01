<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$user_id = $_SESSION['user_id'];
$success = '';

// Procesar el envío de nuevos tickets
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_ticket'])) {
    $asunto = trim($_POST['asunto']);
    $mensaje = trim($_POST['mensaje']);

    $stmt = $pdo->prepare("INSERT INTO tickets (usuario_id, asunto, mensaje) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $asunto, $mensaje]);
    $success = "Ticket enviado correctamente.";

    // =========================================================================
    // 5. NOTIFICACIONES AUTOMÁTICAS (Wirepusher y Telegram) al crear un ticket
    // =========================================================================
    try {
        $visitor_ip = $_SERVER['REMOTE_ADDR'];
        $user_name = $_SESSION['user_name'] ?? 'Usuario';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Definimos la acción para ir directo al panel de administración si hacés clic
        $action = "http://" . $host . "/admin_dashboard.php";

        // Envío Wirepusher
        if (file_exists('wirepusher.php')) {
            include_once('wirepusher.php');
            // Enviamos la notificación push usando tus parámetros
            list($http_status, $response) = Wirepusher::send(
                "jFGKmpzJM",
                "Nuevo Ticket de " . $user_name,
                "Asunto: " . $asunto,
                'helpdesk',
                $action,
                '',
                'helpdesk'
            );
        }

        // Envío Telegram
        $urlMsg = "https://api.telegram.org/bot5929151658:AAHZ6OYqyVnhYoHfreV4KLN86FvQlp0d2jE/sendMessage";
        $msg = "<b>📩 Nuevo Ticket Creado</b>\n";
        $msg .= "<b>Usuario:</b> " . htmlspecialchars($user_name) . "\n";
        $msg .= "<b>Asunto:</b> " . htmlspecialchars($asunto) . "\n";
        $msg .= "<b>Mensaje:</b> " . htmlspecialchars($mensaje) . "\n";
        $msg .= "<b>IP:</b> " . $visitor_ip;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlMsg);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "chat_id=693173807&parse_mode=HTML&text=" . urlencode($msg));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

    } catch (Exception $e) {
        // Silencioso para no romper la experiencia del usuario común si falla la red o las API externas
    }
    // =========================================================================
}

// Obtener los tickets del usuario logueado
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE usuario_id = ? ORDER BY creado_en DESC");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Mesa de ayuda</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Basic.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<div class="container">
    <div class="row">
        <div class="col-md-12"><a class="d-inline-block" href="index.php"><img class="img-fluid" src="assets/img/bigMesa%20de%20trabajo%201.png"></a></div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p data-aos="zoom-out-right" data-aos-delay="300" class="w-lg-50" style="font-weight: bold;text-align: center;color: var(--bs-blue);">Bienvenido a la mesa de ayuda para clientes web de nuestra empresa.</p>
        </div>
    </div>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">Bienvenido, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <div class="d-flex gap-2">
            <a href="user_credentials.php" class="btn btn-warning btn-sm fw-bold px-3">
                🔑 Ver Mis Accesos y Datos Web
            </a>
            <a href="ver_publicaciones.php" class="btn btn-danger btn-sm fw-bold px-3">
                Herramientas
            </a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <?php if($success): ?>
        <div class="alert alert-success shadow-sm"><?= $success ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white"><h5>Nuevo Reclamo / Ticket</h5></div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Asunto</label>
                            <input type="text" name="asunto" class="form-control" placeholder="Ej: Error en pasarela de pago" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Detalle del Reclamo</label>
                            <textarea name="mensaje" class="form-control" rows="4" placeholder="Escribí detalladamente el problema aquí..." required></textarea>
                        </div>
                        <button type="submit" name="crear_ticket" class="btn btn-primary w-100">Enviar Ticket</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white"><h5>Mis Reclamos de Soporte</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Asunto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($tickets)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">Todavía no has abierto ningún ticket de soporte.</td></tr>
                                <?php endif; ?>
                                <?php foreach($tickets as $t): ?>
                                <tr>
                                    <td>#<?= $t['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($t['asunto']) ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?= $t['estado']=='Abierto'?'danger':($t['estado']=='En Proceso'?'warning':'success') ?>">
                                            <?= $t['estado'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($t['creado_en'])) ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-info btn-sm fw-bold text-white" data-bs-toggle="modal" data-bs-target="#modalTicket<?= $t['id'] ?>">Ver Respuestas</button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="modalTicket<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h5 class="modal-title">Ticket #<?= $t['id'] ?> - <?= htmlspecialchars($t['asunto']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                      </div>
                                      <div class="modal-body">
                                        <p class="mb-1"><strong>Tu Reclamo:</strong></p>
                                        <p class="bg-light p-2 rounded border"><?= nl2br(htmlspecialchars($t['mensaje'])) ?></p>
                                        <hr>
                                        <p class="mb-1"><strong>Respuesta del Administrador:</strong></p>
                                        <div class="p-2 rounded border <?= $t['respuesta_admin'] ? 'bg-success-subtle text-success-dark' : 'bg-warning-subtle text-warning-dark' ?>">
                                            <?= $t['respuesta_admin'] ? nl2br(htmlspecialchars($t['respuesta_admin'])) : '⚠️ Tu consulta está siendo analizada por el equipo técnico.' ?>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-init.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
