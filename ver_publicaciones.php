<?php
require_once 'db.php';

// Obtener todas las publicaciones, la más reciente primero
$stmt = $pdo->query("SELECT * FROM publicaciones ORDER BY fecha DESC, id DESC");
$publicaciones = $stmt->fetchAll();

/**
 * Función Helper para convertir URLs normales de YouTube en enlaces "embed" incrustables
 */
function obtenerEmbedYoutube($url) {
    if (empty($url)) return null;

    $video_id = '';
    // Formato estándar o shorts: youtube.com/watch?v=ID o youtube.com/shorts/ID
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match)) {
        $video_id = $match[1];
    }

    if (!empty($video_id)) {
        return '<div class="ratio ratio-16x9 mb-3">
                    <iframe src="https://www.youtube.com/embed/' . $video_id . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>';
    }
    return '<div class="alert alert-warning p-2">⚠️ Formato de enlace de YouTube no válido.</div>';
}
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
              <a href="dashboard.php" class="btn btn-outline-light btn-sm">Volver a Mis Tickets</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-9">

            <?php if(empty($publicaciones)): ?>
                <div class="alert alert-info text-center py-4">
                    <h5>Aún no hay publicaciones cargadas</h5>
                    <p class="mb-0">Utiliza el botón superior para realizar tu primera carga.</p>
                </div>
            <?php endif; ?>

            <?php foreach($publicaciones as $p): ?>
                <div class="card shadow-sm mb-5 border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h3 class="text-dark mb-0 fw-bold"><?= htmlspecialchars($p['titulo']) ?></h3>
                            <span class="badge bg-secondary fs-6">📅 <?= date('d/m/Y', strtotime($p['fecha'])) ?></span>
                        </div>

                        <div class="mb-4 text-secondary" style="white-space: pre-line; line-height: 1.6; font-size: 1.1rem;">
                            <?= htmlspecialchars($p['cuerpo']) ?>
                        </div>

                        <?php if(!empty($p['video_url'])): ?>
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">🎥 Video Relacionado:</h6>
                                <?= obtenerEmbedYoutube($p['video_url']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($p['archivo_adjunto'])): ?>
                            <div class="p-3 bg-light rounded border mt-3">
                                <h6 class="fw-bold text-dark mb-2">📁 Archivo o Material Adjunto:</h6>
                                <?php
                                $ext = strtolower(pathinfo($p['archivo_adjunto'], PATHINFO_EXTENSION));
                                $ruta_completa = 'archivos_subidos/' . $p['archivo_adjunto'];
                                $formatos_imagen = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

                                // Si es una imagen, la mostramos directamente
                                if(in_array($ext, $formatos_imagen)):
                                ?>
                                    <div class="text-center my-2">
                                        <img src="<?= $ruta_completa ?>" class="img-fluid rounded shadow-sm" style="max-height: 450px; object-fit: contain;" alt="Imagen Adjunta">
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>Documento de extensión: <span class="badge bg-dark"><?= strtoupper($ext) ?></span></span>
                                        <a href="<?= $ruta_completa ?>" download class="btn btn-outline-primary btn-sm fw-bold px-3">
                                            📥 Descargar Archivo Adjunto
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
<?php require_once 'footer.php'; ?>
        </div>
    </div>
</div>
</body>
</html>
