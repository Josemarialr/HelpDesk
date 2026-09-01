<?php
// 1. FORZAR LÍMITES Y MOSTRAR ERRORES DESDE EL SCRIPT
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

require_once 'db.php';
// Si manejás sesión de admin, podés descomentar la siguiente línea:
// if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $titulo = trim($_POST['titulo']);
    $cuerpo = trim($_POST['cuerpo']);
    $video_url = trim($_POST['video_url']);
    $archivo_nombre = null;

    // Validación de longitud en el servidor
    if (mb_strlen($cuerpo) > 4000) {
        $error = "El cuerpo no puede superar los 4000 caracteres.";
    } else {
        // Procesar subida de archivo si existe y no hay errores de subida
        if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
            $dir_subida = 'archivos_subidos/';

            // Crear el directorio si no existe
            if (!is_dir($dir_subida)) {
                mkdir($dir_subida, 0755, true);
            }

            $extension = pathinfo($_FILES['adjunto']['name'], PATHINFO_EXTENSION);
            // Generamos un nombre único para evitar que se pisen archivos
            $archivo_nombre = time() . '_' . uniqid() . '.' . $extension;
            $ruta_destino = $dir_subida . $archivo_nombre;

            // CORREGIDO: move_uploaded_file (en singular)
            if (!move_uploaded_file($_FILES['adjunto']['tmp_name'], $ruta_destino)) {
                $archivo_nombre = null;
                $error = "Error al mover el archivo adjunto al servidor. Verifica los permisos de la carpeta archivos_subidos/.";
            }
        } elseif (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Si hubo un error en la subida (por ejemplo, superó el tamaño de php.ini)
            $errorCode = $_FILES['adjunto']['error'];
            if ($errorCode === UPLOAD_ERR_INI_SIZE || $errorCode === UPLOAD_ERR_FORM_SIZE) {
                $error = "El archivo es demasiado grande para los límites actuales de tu servidor PHP.";
            } else {
                $error = "Error código [" . $errorCode . "] al intentar procesar el archivo adjunto.";
            }
        }

        // Si no hay errores, procedemos a insertar en la base de datos
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO publicaciones (fecha, titulo, cuerpo, video_url, archivo_adjunto) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$fecha, $titulo, $cuerpo, $video_url, $archivo_nombre]);
                $success = "Publicación guardada correctamente.";
            } catch (\PDOException $e) {
                $error = "Error al guardar en la base de datos: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Nueva Publicación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">Panel de Contenido - Nueva Publicación</span>
        <a href="ver_publicaciones1.php" class="btn btn-outline-light btn-sm">Ver Publicaciones</a>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Formulario de Carga</h5>
                </div>
                <div class="card-body p-4">
                    <?php if($error): ?> <div class="alert alert-danger shadow-sm"><?= $error ?></div> <?php endif; ?>
                    <?php if($success): ?> <div class="alert alert-success shadow-sm"><?= $success ?></div> <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Título</label>
                                <input type="text" name="titulo" class="form-control" placeholder="Escribe un título llamativo" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Cuerpo / Contenido</label>
                            <textarea name="cuerpo" id="cuerpo" class="form-control" rows="8" maxlength="4000" placeholder="Escribe el contenido aquí (Máximo 4000 caracteres)..." required oninput="actualizarContador()"></textarea>
                            <div class="form-text text-end" id="contador">0 / 4000 caracteres</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">URL de Video (YouTube)</label>
                            <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=XXXXXX o https://youtu.be/XXXXXX">
                            <div class="form-text">Copia y pega la dirección completa del video directamente del navegador.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Subir Archivo Adjunto (Imágenes, PDF, ZIP, etc.)</label>
                            <input type="file" name="adjunto" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold">🚀 Guardar y Publicar</button>
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
</script>
</body>
</html>
