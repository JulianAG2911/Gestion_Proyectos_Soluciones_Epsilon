<?php
session_start();
require_once 'db_config.php';
require_once 'auth.php';
requireAdmin();
include 'Plantilla.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM proyectos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$proyecto) {
            die("Proyecto no encontrado.");
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    die("ID de proyecto no proporcionado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proyecto</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php MostrarNavbar(); ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header text-white text-center" style="background-color: #0b4c66;">
                    <h2 class="h4">Editar Proyecto</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="procesar_editar_proyecto.php">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($proyecto['id']); ?>">

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Proyecto:</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($proyecto['nombre']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="cliente" class="form-label">Cliente:</label>
                            <input type="text" id="cliente" name="cliente" class="form-control" value="<?= htmlspecialchars($proyecto['cliente']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado:</label>
                            <select id="estado" name="estado" class="form-select" required>
                                <?php
                                $estados = ['En progreso', 'En revisión', 'Finalizado', 'Inactivo'];
                                foreach ($estados as $estado): ?>
                                    <option value="<?= $estado ?>" <?= $proyecto['estado'] === $estado ? 'selected' : '' ?>><?= $estado ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success">Actualizar</button>
                            <a href="listar_proyectos.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
