<?php
// 1. INICIAR SESIÓN Y VERIFICAR AUTENTICACIÓN
session_start();

if (!isset($_SESSION['admin_id'])) {
    // Guardamos el ID que intentaba editar para redirigir tras el login (opcional pero muy útil)
    $id_redirect = isset($_GET['id']) ? '?id=' . intval($_GET['id']) : '';
    header("Location: admin_login.php" . $id_redirect);
    exit;
}

// 2. FORZAR LÍMITES Y MOSTRAR ERRORES DESDE EL SCRIPT
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

require_once 'db.php';

$error = '';
$success = '';

// 2. VALIDAR QUE SE RECIBA UN ID VÁLIDO
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: ver_publicaciones1.php");
    exit;
}

// 3. OBTENER LOS DATOS ACTUALES DE LA PUBLICACIÓN
try {
    $stmt = $pdo->prepare("SELECT * FROM publicaciones WHERE id = ?");
    $stmt->execute([$id]);
    $publicacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$publicacion) {
        die("La publicación solicitada no existe.");
    }
} catch (\PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}

// 4. PROCESAR LA ACTUALIZACIÓN CUANDO SE ENVÍA EL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $titulo = trim($_POST['titulo']);
    $cuerpo = trim($_POST['cuerpo']);
    $video_url = trim($_POST['video_url']);

    // Mantener el archivo actual por defecto
    $archivo_nombre = $publicacion['archivo_adjunto'];

    // Validación de longitud en el servidor
    if (mb_strlen($cuerpo) > 4000) {
        $error = "El cuerpo no puede superar los 4000 caracteres.";
    } else {
        // Procesar subida de NUEVO archivo si fue enviado
        if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
            $dir_subida = 'archivos_subidos/';

            if (!is_dir($dir_subida)) {
                mkdir($dir_subida, 0755, true);
            }

            $extension = pathinfo($_FILES['adjunto']['name'], PATHINFO_EXTENSION);
            $nuevo_archivo_nombre = time() . '_' . uniqid() . '.' . $extension;
            $ruta_destino = $dir_subida . $nuevo_archivo_nombre;

            if (move_uploaded_file($_FILES['adjunto']['tmp_name'], $ruta_destino)) {
                // Si había un archivo anterior, lo borramos del servidor
                if (!empty($publicacion['archivo_adjunto']) && file_exists($dir_subida . $publicacion['archivo_adjunto'])) {
                    unlink($dir_subida . $publicacion['archivo_adjunto']);
                }
                // Asignamos el nuevo nombre para guardar en la BD
                $archivo_nombre = $nuevo_archivo_nombre;
            } else {
                $error = "Error al mover el nuevo archivo adjunto al servidor.";
            }
        } elseif (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errorCode = $_FILES['adjunto']['error'];
            if ($errorCode === UPLOAD_ERR_INI_SIZE || $errorCode === UPLOAD_ERR_FORM_SIZE) {
                $error = "El archivo es demasiado grande para los límites actuales de tu servidor PHP.";
            } else {
                $error = "Error código [" . $errorCode . "] al intentar procesar el archivo adjunto.";
            }
        }

        // Si no hay errores, actualizamos en la base de datos
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("UPDATE publicaciones SET fecha = ?, titulo = ?, cuerpo = ?, video_url = ?, archivo_adjunto = ? WHERE id = ?");
                $stmt->execute([$fecha, $titulo, $cuerpo, $video_url, $archivo_nombre, $id]);

                $success = "Publicación actualizada correctamente.";

                // Actualizar la variable local para refrescar la vista
                $publicacion['fecha'] = $fecha;
                $publicacion['titulo'] = $titulo;
                $publicacion['cuerpo'] = $cuerpo;
                $publicacion['video_url'] = $video_url;
                $publicacion['archivo_adjunto'] = $archivo_nombre;
            } catch (\PDOException $e) {
                $error = "Error al actualizar en la base de datos: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Publicación #<?= htmlspecialchars($publicacion['id']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">Panel de Contenido - Editar Publicación</span>
        <a href="ver_publicaciones1.php" class="btn btn-outline-light btn-sm">Ver Publicaciones</a>
        <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">Volver</a>

    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0 fw-bold">✏️ Editando Publicación #<?= htmlspecialchars($publicacion['id']) ?></h5>
                </div>
                <div class="card-body p-4">
                    <?php if($error): ?> <div class="alert alert-danger shadow-sm"><?= $error ?></div> <?php endif; ?>
                    <?php if($success): ?> <div class="alert alert-success shadow-sm"><?= $success ?></div> <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($publicacion['fecha']) ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Título</label>
                                <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($publicacion['titulo']) ?>" placeholder="Escribe un título llamativo" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Cuerpo / Contenido</label>
                            <textarea name="cuerpo" id="cuerpo" class="form-control" rows="8" maxlength="4000" placeholder="Escribe el contenido aquí (Máximo 4000 caracteres)..." required oninput="actualizarContador()"><?= htmlspecialchars($publicacion['cuerpo']) ?></textarea>
                            <div class="form-text text-end" id="contador">0 / 4000 caracteres</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">URL de Video (YouTube)</label>
                            <input type="url" name="video_url" class="form-control" value="<?= htmlspecialchars($publicacion['video_url']) ?>" placeholder="https://www.youtube.com/watch?v=XXXXXX o https://youtu.be/XXXXXX">
                            <div class="form-text">Copia y pega la dirección completa del video directamente del navegador.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Archivo Adjunto</label>
                            <?php if(!empty($publicacion['archivo_adjunto'])): ?>
                                <div class="mb-2">
                                    <span class="badge bg-secondary">Archivo actual:</span>
                                    <a href="archivos_subidos/<?= htmlspecialchars($publicacion['archivo_adjunto']) ?>" target="_blank">
                                        <?= htmlspecialchars($publicacion['archivo_adjunto']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="adjunto" class="form-control">
                            <div class="form-text">Selecciona un archivo solo si deseas reemplazar el actual. Si lo dejas vacío, se conservará el archivo previo.</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="ver_publicaciones.php" class="btn btn-secondary btn-lg fw-bold">Cancelar</a>
                            <button type="submit" class="btn btn-warning btn-lg fw-bold">💾 Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function actualizarContador() {
    const textarea = document.getElementById('cuerpo');
    const contador = document.getElementById('contador');
    contador.textContent = `${textarea.value.length} / 4000 caracteres`;
}

// Inicializar el contador con el contenido cargado
document.addEventListener('DOMContentLoaded', actualizarContador);
</script>
</body>
</html>
