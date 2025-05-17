<?php
session_start();
require_once 'db_config.php';
require_once 'auth.php'; 
requireAdmin();
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    die("Error: Usuario no autenticado.");
}

$usuario_id = intval($_SESSION['user_id']);
$es_admin = isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;

// Filtros
$filtro_empleado = $es_admin && isset($_GET['empleado_id']) && $_GET['empleado_id'] !== '' ? intval($_GET['empleado_id']) : null;
$filtro_fecha = isset($_GET['fecha']) && !empty($_GET['fecha']) ? $_GET['fecha'] : null;

// Construcción SQL
$sql = "SELECT e.id, e.fecha, e.comentarios, e.puntuacion, e.horas_trabajadas, 
               e.tareas_completadas, e.tareas_en_progreso,
               u.nombre, u.apellidos
        FROM evaluaciones_desempeno e
        JOIN usuarios u ON e.usuario_id = u.id";

$params = [];

if ($es_admin && $filtro_empleado) {
    $sql .= " WHERE e.usuario_id = :usuario_id";
    $params['usuario_id'] = $filtro_empleado;
} else {
    $sql .= " WHERE e.usuario_id = :usuario_id";
    $params['usuario_id'] = $usuario_id;
}

if ($filtro_fecha) {
    $sql .= " AND DATE(e.fecha) = :fecha";
    $params['fecha'] = $filtro_fecha;
}

$sql .= " ORDER BY e.fecha DESC LIMIT 10";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $evaluaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener evaluaciones: " . $e->getMessage());
}

// Obtener empleados si es admin
$usuarios = [];
if ($es_admin) {
    try {
        $stmt = $pdo->query("SELECT id, nombre, apellidos FROM usuarios");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener empleados: " . $e->getMessage());
    }
}
include 'plantilla.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Evaluaciones</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <?php MostrarNavbar(); ?>

    <div class="container mt-4">
        <h2 class="text-white p-3 rounded" style="background-color: #0b4c66; text-align:center;">Mis Evaluaciones</h2>
        <div class="card p-3 mb-3">
            <form method="GET" action="listar_evaluaciones.php" class="row g-3">
                <?php if ($es_admin): ?>
                    <div class="col-md-6">
                        <label for="empleado_id" class="form-label">Empleado:</label>
                        <select id="empleado_id" name="empleado_id" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= htmlspecialchars($usuario['id']); ?>" <?= ($filtro_empleado == $usuario['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($usuario['nombre'] . " " . $usuario['apellidos']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="col-md-4">
                    <label for="fecha" class="form-label">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars($filtro_fecha); ?>" class="form-control">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" style="background-color: #0b4c66;">Filtrar</button>
                </div>
            </form>
        </div>
        <button class="btn btn-primary mb-3" style="background-color: #0b4c66;" onclick="window.location.href='registrar_evaluacion.php'">Registrar Nueva Evaluación</button>

        <!-- Evaluaciones -->
        <?php foreach ($evaluaciones as $evaluacion): ?>
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><?= date('F Y', strtotime($evaluacion['fecha'])); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Horas Trabajadas:</strong> <?= htmlspecialchars($evaluacion['horas_trabajadas']); ?> horas</p>
                            <p><strong>Tareas Completadas:</strong> <?= htmlspecialchars($evaluacion['tareas_completadas']); ?> tareas</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Tareas En Progreso:</strong> <?= htmlspecialchars($evaluacion['tareas_en_progreso']); ?> tareas</p>
                        </div>
                            <?php
                            $puntuacion = floatval($evaluacion['puntuacion']);
                            $colorClase = 'bg-success'; // default

                            if ($puntuacion < 5.0) {
                                $colorClase = 'bg-danger';
                            } elseif ($puntuacion < 7.0) {
                                $colorClase = 'bg-warning text-dark';
                            }
                            ?>
                            <div class="col-md-4 text-end">
                                <span class="badge <?= $colorClase ?> fs-5">
                                <?= number_format($puntuacion, 1); ?>/10
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <strong>Comentario Adicional:</strong>
                        <p><?= htmlspecialchars($evaluacion['comentarios']); ?></p>
                        <?php if ($es_admin): ?>
                            <div class="text-end">
                                <a href="editar_evaluacion.php?id=<?= $evaluacion['id']; ?>" class="btn">Editar</a>
                                <a href="javascript:void(0);" class="btn btn-eliminar"  onclick="confirmarEliminar(<?= $evaluacion['id']; ?>);">Eliminar</a>
                            </div>
                        <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>

<script>
    function confirmarEliminar(id) {
        event.preventDefault();  // Prevenir que el enlace se siga
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Este proyecto se eliminará permanentemente!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "eliminar_evaluacion.php?id=" + id;
            }
        });
    }
</script>
</html>

