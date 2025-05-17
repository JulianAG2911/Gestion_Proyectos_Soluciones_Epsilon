<?php
session_start();
require_once 'db_config.php';
include 'Plantilla.php';
require_once 'auth.php'; 

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirigir al login si no está logueado
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener la información del usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Perfil</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #0b4c66, #083d52);
            padding: 20px;
        }

        .card-body {
            padding: 30px;
            background: #f8f9fa;
        }

        .info-item {
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: baseline;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #0b4c66;
            width: 180px;
            flex-shrink: 0;
        }

        .info-value {
            color: #495057;
            flex-grow: 1;
        }

        .btn-custom {
            background-color: #0b4c66;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #083d52;
            transform: translateY(-2px);
            color: white;
        }
    </style>
</head>
<body>
    <?php MostrarNavbar(); ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-header text-white text-center">
                        <h2 class="h4">Perfil de Usuario</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value"><?= htmlspecialchars($usuario['nombre']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Apellidos:</span>
                            <span class="info-value"><?= htmlspecialchars($usuario['apellidos']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fecha de Nacimiento:</span>
                            <span class="info-value"><?= htmlspecialchars($usuario['fecha_nacimiento']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Cédula:</span>
                            <span class="info-value"><?= htmlspecialchars($usuario['cedula']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-value"><?= htmlspecialchars($usuario['telefono']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Correo Electrónico:</span>
                            <span class="info-value"><?= htmlspecialchars($usuario['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Rol:</span>
                            <span class="info-value"><?= htmlspecialchars($usuario['role_id']); ?></span>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="editar_perfil.php?id=<?= htmlspecialchars($usuario['id']); ?>" class="btn btn-custom">
                                <i class="fas fa-edit"></i> Editar Perfil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
