<?php
require_once 'db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

// Procesar actualización de tickets
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_ticket'])) {
    $ticket_id = $_POST['ticket_id'];
    $estado = $_POST['estado'];
    $respuesta = trim($_POST['respuesta_admin']);

    $stmt = $pdo->prepare("UPDATE tickets SET estado = ?, respuesta_admin = ? WHERE id = ?");
    $stmt->execute([$estado, $respuesta, $ticket_id]);
}

// 1. Obtener todos los tickets con info del usuario (Historial de Soporte)
$stmt_tickets = $pdo->query("SELECT t.*, u.nombre, u.picture
                             FROM tickets t
                             JOIN usuarios u ON t.usuario_id = u.id
                             ORDER BY t.creado_en DESC");
$tickets = $stmt_tickets->fetchAll();

// 2. NUEVO: Obtener el listado completo de todos los usuarios registrados en el sistema
$stmt_usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nombre ASC");
$usuarios = $stmt_usuarios->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <div class="container border-0">
        <span class="navbar-brand">Panel Admin: <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
        <div class="d-flex gap-2">
            <a href="admin_register.php" class="btn btn-warning btn-sm fw-bold">➕ Registrar Nuevo Admin</a>
            <a href="publicar.php" class="btn btn-warning btn-sm fw-bold">➕ Tools</a>
            <a href="ver_publicaciones1.php" class="btn btn-warning btn-sm fw-bold">Ver Edit</a>
  <a href="admin_cargar_datos_cliente.php" class="btn btn-warning btn-sm fw-bold">➕ Carga Datos</a>
  <a href="admin_ver_datos_clientes.php" class="btn btn-warning btn-sm fw-bold">➕ Ver mas</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Salir</a>
        </div>
    </div>
</nav>

<div class="container-fluid my-4">
    <div class="row">

        <div class="col-12 mb-5">
            <div class="card shadow">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">👥 Clientes Registrados en el Sistema</h5>
                    <span class="badge bg-secondary"><?= count($usuarios) ?> Usuarios totales</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-dark border-0">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario / Cliente</th>
                                    <th>Contacto Base</th>
                                    <th>Fecha Registro</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($usuarios)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No hay usuarios comunes registrados en el sistema todavía.</td></tr>
                                <?php endif; ?>
                                <?php foreach($usuarios as $u): ?>
                                <tr>
                                    <td>#<?= $u['id'] ?></td>
                                    <td>
                                        <img src="./uploads/<?= htmlspecialchars($u['picture']) ?>" class="rounded-circle me-2" width="38" height="38" style="object-fit: cover;" alt="Avatar">
                                        <strong><?= htmlspecialchars($u['nombre']) ?></strong> <small class="text-muted">(<?= htmlspecialchars($u['username']) ?>)</small>
                                    </td>
                                    <td>
                                        <small>
                                            📞 <?= htmlspecialchars($u['telefono']) ?><br>
                                            📧 <?= htmlspecialchars($u['email']) ?>
                                        </small>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($u['creado_en'])) ?></td>
                                    <td class="text-end">
                                        <a href="admin_edit_user.php?id=<?= $u['id'] ?>" class="btn btn-warning btn-sm fw-bold">
                                            ⚙️ Gestionar Info / Credenciales
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">📩 Historial de Tickets / Reclamos de Soporte</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark border-0">
                                <tr>
                                    <th>Ticket</th>
                                    <th>Usuario</th>
                                    <th>Asunto</th>
                                    <th>Estado Actual</th>
                                    <th>Fecha Envió</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($tickets)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-3">No se abrieron tickets de soporte por el momento.</td></tr>
                                <?php endif; ?>
                                <?php foreach($tickets as $t): ?>
                                <tr>
                                    <td>#<?= $t['id'] ?></td>
                                    <td>
                                        <img src="./uploads/<?= htmlspecialchars($t['picture']) ?>" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                        <strong><?= htmlspecialchars($t['nombre']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($t['asunto']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $t['estado']=='Abierto'?'danger':($t['estado']=='En Proceso'?'warning':'success') ?>">
                                            <?= $t['estado'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($t['creado_en'])) ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#atenderTicket<?= $t['id'] ?>">Atender Ticket</button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="atenderTicket<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                                  <div class="modal-dialog modal-lg">
                                    <div class="modal-content border-0">
                                      <form action="" method="POST">
                                          <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title">Gestionar Ticket #<?= $t['id'] ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                          </div>
                                          <div class="modal-body">
                                            <p class="mb-1"><strong>Detalle del Reclamo:</strong></p>
                                            <div class="bg-light p-3 rounded mb-3 border">
                                                <?= nl2br(htmlspecialchars($t['mensaje'])) ?>
                                            </div>

                                            <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Cambiar Estado</label>
                                                <select name="estado" class="form-select">
                                                    <option value="Abierto" <?= $t['estado'] == 'Abierto' ? 'selected' : '' ?>>Abierto</option>
                                                    <option value="En Proceso" <?= $t['estado'] == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                                                    <option value="Cerrado" <?= $t['estado'] == 'Cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Escribir Respuesta al Usuario</label>
                                                <textarea name="respuesta_admin" class="form-control" rows="5" required><?= htmlspecialchars($t['respuesta_admin'] ?? '') ?></textarea>
                                            </div>
                                          </div>
                                          <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" name="actualizar_ticket" class="btn btn-success shadow-sm">Responder y Guardar</button>
                                          </div>
                                      </form>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
