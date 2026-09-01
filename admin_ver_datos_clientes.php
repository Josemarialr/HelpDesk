<?php
// 1. CONTROL DE ERRORES Y CONFIGURACIÓN
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';
// Control de sesión de admin (Descomentar si usas la variable standard)
// if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$filtro_usuario = $_GET['usuario_id'] ?? '';

// Procesar eliminación de un registro si se solicita
if (isset($_POST['eliminar_registro'])) {
    $id_eliminar = $_POST['id_registro'];

    // Primero buscamos si tiene un archivo para borrarlo del servidor físico
    $stmt_file = $pdo->prepare("SELECT archivo_adjunto FROM cliente_datos_extras WHERE id = ?");
    $stmt_file->execute([$id_eliminar]);
    $reg_file = $stmt_file->fetch();
    if (!empty($reg_file['archivo_adjunto'])) {
        $ruta_archivo = 'archivos_clientes/' . $reg_file['archivo_adjunto'];
        if (file_exists($ruta_archivo)) {
            unlink($ruta_archivo); // Borra el archivo
        }
    }

    // Borramos el registro de la base de datos
    $stmt_del = $pdo->prepare("DELETE FROM cliente_datos_extras WHERE id = ?");
    $stmt_del->execute([$id_eliminar]);
    header("Location: admin_ver_datos_clientes.php?usuario_id=" . $filtro_usuario);
    exit;
}

// 2. OBTENER LISTADO DE CLIENTES PARA EL FILTRO DESPLEGABLE
$stmt_cli = $pdo->query("SELECT id, nombre, username FROM usuarios ORDER BY nombre ASC");
$clientes = $stmt_cli->fetchAll();

// 3. CONSTRUIR CONSULTA DE LOS DATOS CARGADOS (CON O SIN FILTRO)
$sql = "SELECT cde.*, u.nombre as cliente_nombre, u.username as cliente_user
        FROM cliente_datos_extras cde
        JOIN usuarios u ON cde.usuario_id = u.id";

if (!empty($filtro_usuario)) {
    $sql .= " WHERE cde.usuario_id = ? ORDER BY cde.fecha DESC, cde.id DESC";
    $stmt_datos = $pdo->prepare($sql);
    $stmt_datos->execute([$filtro_usuario]);
} else {
    $sql .= " ORDER BY cde.fecha DESC, cde.id DESC LIMIT 100"; // Límite de cortesía si hay demasiados
    $stmt_datos = $pdo->query($sql);
}
$registros = $stmt_datos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial y Datos de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-danger">
    <div class="container">
        <span class="navbar-brand">Panel Admin - Historial Técnico de Clientes</span>
        <div class="d-flex gap-2">
            <a href="admin_cargar_datos_cliente.php" class="btn btn-warning btn-sm fw-bold">➕ Cargar Nuevos Datos</a>
            <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">Volver al Dashboard</a>
        </div>
    </div>
</nav>

<div class="container-fluid my-4">
    <div class="row justify-content-center">
        <div class="col-md-11">

            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body bg-dark rounded text-white p-3">
                    <form method="GET" action="" class="row align-items-center g-3">
                        <div class="col-auto">
                            <h6 class="mb-0 fw-bold">🔍 Filtrar Historial por Cliente:</h6>
                        </div>
                        <div class="col-md-5">
                            <select name="usuario_id" class="form-select form-select-sm">
                                <option value="">-- Ver todos los clientes (Últimos 100 registros) --</option>
                                <?php foreach($clientes as $cli): ?>
                                    <option value="<?= $cli['id'] ?>" <?= $filtro_usuario == $cli['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cli['nombre']) ?> (<?= htmlspecialchars($cli['username']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-danger btn-sm px-4 fw-bold">Buscar / Filtrar</button>
                            <?php if(!empty($filtro_usuario)): ?>
                                <a href="admin_ver_datos_clientes.php" class="btn btn-secondary btn-sm">Limpiar Filtro</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Registros Internos / Documentación Almacenada</h5>
                    <span class="badge bg-light text-dark fw-bold"><?= count($registros) ?> Fichas encontradas</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 8%;">Fecha</th>
                                    <th style="width: 20%;">Cliente</th>
                                    <th style="width: 42%;">Datos Extras / Bitácora Técnica</th>
                                    <th style="width: 22%;">Archivos Adjuntos</th>
                                    <th style="width: 8%;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($registros)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <h5>No se encontraron registros cargados para los criterios seleccionados.</h5>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach($registros as $reg): ?>
                                <tr>
                                    <td class="fw-bold text-secondary">
                                        <?= date('d/m/Y', strtotime($reg['fecha'])) ?>
                                    </td>

                                    <td>
                                        <strong class="text-primary"><?= htmlspecialchars($reg['cliente_nombre']) ?></strong><br>
                                        <small class="text-muted">@<?= htmlspecialchars($reg['cliente_user']) ?></small>
                                    </td>

                                    <td style="white-space: pre-line; max-height: 200px; font-size: 0.95rem; line-height: 1.4;" class="text-dark py-3">
                                        <?= htmlspecialchars($reg['datos_extras']) ?>
                                    </td>

                                    <td>
                                        <?php if(!empty($reg['archivo_adjunto'])): ?>
                                            <?php
                                            $ext = strtolower(pathinfo($reg['archivo_adjunto'], PATHINFO_EXTENSION));
                                            $ruta_archivo = 'archivos_clientes/' . $reg['archivo_adjunto'];
                                            $es_imagen = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                            if($es_imagen):
                                            ?>
                                                <div class="mb-1">
                                                    <a href="<?= $ruta_archivo ?>" target="_blank">
                                                        <img src="<?= $ruta_archivo ?>" class="img-thumbnail rounded shadow-sm" style="max-height: 80px; max-width: 120px; object-fit: cover;" alt="Miniatura">
                                                    </a>
                                                </div>
                                                <small class="text-muted d-block">Formato: <?= strtoupper($ext) ?></small>
                                            <?php else: ?>
                                                <div class="d-grid">
                                                    <a href="<?= $ruta_archivo ?>" download class="btn btn-outline-dark btn-sm text-start fw-bold overflow-hidden" style="text-overflow: ellipsis; white-space: nowrap;">
                                                        📥 Descargar .<?= strtoupper($ext) ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted italic"><small>Sin archivos adjuntos</small></span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <form action="" method="POST" onsubmit="return confirm('¿Estás completamente seguro de que deseas eliminar este registro técnico y su archivo asociado? Esta acción no se puede deshacer.');">
                                            <input type="hidden" name="id_registro" value="<?= $reg['id'] ?>">
                                            <button type="submit" name="eliminar_registro" class="btn btn-outline-danger btn-sm">
                                                🗑️ Borrar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
