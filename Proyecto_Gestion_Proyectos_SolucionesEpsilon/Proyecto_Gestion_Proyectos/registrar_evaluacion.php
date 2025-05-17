<?php 
    session_start();
    require_once 'db_config.php';
    include 'Plantilla.php';
    require_once 'auth.php'; 
    requireAdmin();     
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }

    // Obtener empleados para asignar la evaluación
    try {
        $stmt = $pdo->prepare("SELECT id, nombre, apellidos FROM usuarios"); 
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener empleados: " . $e->getMessage());
    }
    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registrar Evaluación</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .container-box {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                max-width: 600px;
                margin: auto;
                margin-top: 50px;
            }
        .btn-custom {
            background-color: #0b4c66 !important;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            min-width: 200px;
            cursor: pointer;
        }

        .btn-custom:hover {
            background-color: #083d52 !important;
            color: white;
            transform: translateY(-2px);
        }
        </style>
    </head>
    <body>
    <?php
        MostrarNavbar();
        ?>
       <div class="container mt-5">
    <div class="container-box">
        <h2 class="text-center mb-4">Registrar Nueva Evaluación</h2>
        <form method="POST" action="procesar_evaluacion.php">
            <div class="mb-3">
                <label for="empleado" class="form-label">Empleado</label>
                <select id="empleado" name="empleado" class="form-select" required>
                    <option value="">Seleccione un empleado</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= htmlspecialchars($usuario['id']); ?>">
                            <?= htmlspecialchars($usuario['nombre'] . " " . $usuario['apellidos']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha de Evaluación</label>
                <input type="date" id="fecha" name="fecha" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="comentarios" class="form-label">Comentarios</label>
                <textarea id="comentarios" name="comentarios" class="form-control" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label for="puntuacion" class="form-label">Puntuación (1.0 - 10.0)</label>
                <input type="number" id="puntuacion" name="puntuacion" class="form-control" step="0.1" min="1.0" max="10.0" required>
            </div>

            <div class="mb-3">
                <label for="horas_trabajadas" class="form-label">Horas Trabajadas</label>
                <input type="number" id="horas_trabajadas" name="horas_trabajadas" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="tareas_completadas" class="form-label">Tareas Completadas</label>
                <input type="number" id="tareas_completadas" name="tareas_completadas" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="tareas_progreso" class="form-label">Tareas en Progreso</label>
                <input type="number" id="tareas_progreso" name="tareas_progreso" class="form-control" required>
            </div>

            <div class="d-flex justify-content-between">
                <a href="listar_evaluaciones.php" class="btn btn-custom">Atrás</a>
                <button type="submit" class="btn btn-custom">Registrar Evaluación</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>