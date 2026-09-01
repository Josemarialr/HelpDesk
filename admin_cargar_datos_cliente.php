<?php
// 1. FORZAR LÍMITES Y MOSTRAR ERRORES DESDE EL SCRIPT
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

require_once 'db.php';
// Control de sesión de admin (Descomentar si usas la variable standard)
// if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_POST['usuario_id'];
    $fecha = $_POST['fecha'];
    $datos_extras = trim($_POST['datos_extras']);
    $archivo_nombre = null;

    // Validación de longitud (4000 caracteres)
    if (mb_strlen($datos_extras) > 4000) {
        $error = "Los datos extras no pueden superar los 4000 caracteres.";
    } elseif (empty($usuario_id)) {
        $error = "Por favor, selecciona un cliente válido.";
    } else {
        // Procesar subida de cualquier tipo de archivo
        if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
            $dir_subida = 'archivos_clientes/';

            // Crear el directorio si no existe en tu servidor
            if (!is_dir($dir_subida)) {
                mkdir($dir_subida, 0755, true);
            }

            $extension = pathinfo($_FILES['adjunto']['name'], PATHINFO_EXTENSION);
            // Nombre único usando timestamp para evitar duplicados
            $archivo_nombre = time() . '_cliente_' . uniqid() . '.' . $extension;
            $ruta_destino = $dir_subida . $archivo_nombre;

            if (!move_uploaded_file($_FILES['adjunto']['tmp_name'], $ruta_destino)) {
                $archivo_nombre = null;
                $error = "Error al guardar el archivo en el servidor. Revisa los permisos de la carpeta archivos_clientes/.";
            }
        } elseif (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errorCode = $_FILES['adjunto']['error'];
            if ($errorCode === UPLOAD_ERR_INI_SIZE || $errorCode === UPLOAD_ERR_FORM_SIZE) {
                $error = "El archivo adjunto supera el tamaño máximo permitido por tu servidor.";
            } else {
                $error = "Error de subida código [" . $errorCode . "].";
            }
        }

        // Si no hay fallos previos, guardamos en la nueva tabla
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO cliente_datos_extras (usuario_id, fecha, datos_extras, archivo_adjunto) VALUES (?, ?, ?, ?)");
                $stmt->execute([$usuario_id, $fecha, $datos_extras, $archivo_nombre]);
                $success = "Información del cliente almacenada correctamente.";
            } catch (\PDOException $e) {
                $error = "Error en la base de datos: " . $e->getMessage();
            }
        }
    }
}

// Obtener el listado de todos los clientes para el desplegable del formulario
try {
    $stmt_clientes = $pdo->query("SELECT id, nombre, username FROM usuarios ORDER BY nombre ASC");
    $clientes = $stmt_clientes->fetchAll();
} catch (\PDOException $e) {
    die("Error al consultar clientes: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Datos Extras a Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-danger">
    <div class="container">
        <span class="navbar-brand">Panel Admin - Cargar Documentación de Cliente</span>
        <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">Volver al Dashboard</a>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Nueva Ficha de Información Exclusiva</h5>
                </div>
                <div class="card-body p-4">
                    <?php if($error): ?> <div class="alert alert-danger shadow-sm"><?= $error ?></div> <?php endif; ?>
                    <?php if($success): ?> <div class="alert alert-success shadow-sm"><?= $success ?></div> <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Seleccionar Cliente</label>
                            <select name="usuario_id" class="form-select" required>
                                <option value="">-- Seleccione un cliente del sistema --</option>
                                <?php foreach($clientes as $cli): ?>
                                    <option value="<?= $cli['id'] ?>">
                                        <?= htmlspecialchars($cli['nombre']) ?> (<?= htmlspecialchars($cli['username']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha del Registro</label>
                            <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Datos Extras e Historial Técnico</label>
                            <textarea name="datos_extras" id="datos_extras" class="form-control" rows="8" maxlength="4000" placeholder="Escribe aquí anotaciones, logs, configuraciones específicas o detalles del cliente (Hasta 4000 caracteres)..." required oninput="actualizarContador()"></textarea>
                            <div class="form-text text-end" id="contador">0 / 4000 caracteres</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Adjuntar Documentación Obligatoria (PDF, RAR, ZIP, Imágenes)</label>
                            <input type="file" name="adjunto" class="form-control">
                            <div class="form-text">Puedes subir respaldos, contratos o manuales técnicos de cualquier formato.</div>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 btn-lg fw-bold">💾 Asignar y Guardar en Cliente</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function actualizarContador() {
    const textarea = document.getElementById('datos_extras');
    const contador = document.getElementById('contador');
    contador.textContent = `${textarea.value.length} / 4000 caracteres`;
}
</script>
</body>
</html>
