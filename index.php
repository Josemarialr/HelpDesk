<?php
session_start();
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];

        // --- INICIO ENVÍO WIREPUSHER ---
        $visitor_ip = $_SERVER['REMOTE_ADDR'];
        $host = $_SERVER['HTTP_HOST'];

        // Variables predeterminadas (ajusta los IDs o imágenes si vienen de la BD)
        $idv = isset($user['id']) ? $user['id'] : '';
        $image = 'default.jpg';

        $url1 = "/xvg24/view.php?idv=";
        $url2 = "/xvg24/thumb/";
        $action = "http://" . $host . $url1 . $idv;
        $image_url = "http://" . $host . $url2 . $image;

        include_once('wirepusher.php');
        list($http_status, $response) = Wirepusher::send(
            "jFGKmpzJM",
            "ingreso a Ver video",
            $visitor_ip,
            'xvg24',
            $action,
            $image_url,
            'xvg24'
        );
        // --- FIN ENVÍO WIREPUSHER ---

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Credenciales incorrectas.";
    }
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
<div class="container">
    <div class="row">
        <div class="col-md-12"><a class="d-inline-block" href="index.php"><img class="img-fluid" src="assets/img/bigMesa%20de%20trabajo%201.png"></a></div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p data-aos="zoom-out-right" data-aos-delay="300" class="w-lg-50" style="font-weight: bold;text-align: center;color: var(--bs-blue);">Bienvenido a la mesa de ayuda para clientes web de nuestra empresa.</p>
        </div>
    </div>
</div>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Soporte Técnico - Usuarios</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                    </form>
                    <hr>
                    <div class="text-center">
                        <a href="registro.php">¿No tenés cuenta? Registrate acá</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-init.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
</body>
</html>
