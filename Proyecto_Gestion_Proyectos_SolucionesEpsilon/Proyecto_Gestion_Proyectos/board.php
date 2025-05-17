<?php
session_start();
require_once 'db_config.php';
include 'plantilla.php';
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Obtener proyectos
$stmt = $pdo->query("SELECT id, nombre FROM proyectos");
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el proyecto seleccionado
$proyecto_id = isset($_GET['proyecto_id']) ? (int)$_GET['proyecto_id'] : null;

// Obtener tareas del proyecto seleccionado
$sql = "SELECT t.id, t.nombre, t.descripcion, t.estado_id, e.nombre AS estado, u.email AS responsable
        FROM tareas t
        JOIN estados e ON t.estado_id = e.id
        LEFT JOIN usuarios u ON t.usuario_id = u.id
        WHERE t.proyecto_id = :proyecto_id
        ORDER BY t.estado_id, t.fecha_vencimiento";
$stmt = $pdo->prepare($sql);
$stmt->execute(['proyecto_id' => $proyecto_id]);
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$estados = [
    1 => "Por Hacer",
    2 => "En Progreso",
    3 => "Completado"
];

$tareasPorEstado = [
    1 => [], // Por Hacer
    2 => [], // En Progreso
    3 => []  // Completado
];

// Organizar tareas en sus respectivos estados
foreach ($tareas as $tarea) {
    $estadoId = $tarea['estado_id'];
    $tareasPorEstado[$estadoId][] = $tarea;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tablero de Tareas</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600&family=Roboto+Slab:wght@400&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.css">
    <style>
    .container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 20px;
    }

    .kanban-board {
        display: flex;
        gap: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
    }

    .kanban-column {
        flex: 1;
        background: white;
        padding: 20px;
        border-radius: 12px;
        min-height: 500px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #e9ecef;
    }

    .kanban-column h4 {
        text-align: center;
        font-weight: 600;
        font-size: 1.2rem;
        color: #0b4c66;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #0b4c66;
    }

    .kanban-item {
        background: white;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .kanban-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .kanban-item strong {
        font-size: 1.1rem;
        color: #0b4c66;
        font-weight: 600;
        display: block;
        margin-bottom: 8px;
    }

    .kanban-item p {
        font-size: 0.95rem;
        color: #495057;
        line-height: 1.5;
        margin: 10px 0;
    }

    .kanban-item small {
        display: block;
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 12px;
        padding-top: 8px;
        border-top: 1px solid #e9ecef;
    }

    #agregar-tarea-btn {
        background-color: #0b4c66;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        margin: 20px 0;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(11, 76, 102, 0.2);
    }

    #agregar-tarea-btn:hover {
        background-color: #083d52;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(11, 76, 102, 0.3);
    }

    #formulario-tarea {
        background: white;
        padding: 25px;
        border-radius: 12px;
        margin: 20px 0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
    }

    #formulario-tarea input,
    #formulario-tarea textarea,
    #formulario-tarea select {
        width: 100%;
        padding: 12px;
        margin: 8px 0 16px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    #formulario-tarea input:focus,
    #formulario-tarea textarea:focus,
    #formulario-tarea select:focus {
        border-color: #0b4c66;
        box-shadow: 0 0 0 3px rgba(11, 76, 102, 0.1);
        outline: none;
    }

    #formulario-tarea button {
        background: #0b4c66;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    #formulario-tarea button:hover {
        background: #083d52;
        transform: translateY(-1px);
    }

    select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23555' viewBox='0 0 16 16'%3E%3Cpath d='M8 10.5l4-4H4l4 4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 35px !important;
    }

    .text-center {
        text-align: center;
    }

    .my-4 {
        margin: 2rem 0;
    }

    /* Estilos para el selector de proyectos */
    .proyecto-selector {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin: 20px 0;
    }

    .proyecto-selector label {
        display: block;
        font-size: 1.1rem;
        font-weight: 600;
        color: #0b4c66;
        margin-bottom: 12px;
    }

    .proyecto-selector select {
        width: 100%;
        padding: 12px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        color: #495057;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }

    .proyecto-selector select:hover {
        border-color: #0b4c66;
    }

    .proyecto-selector select:focus {
        border-color: #0b4c66;
        box-shadow: 0 0 0 3px rgba(11, 76, 102, 0.1);
        outline: none;
    }
    </style>

</head>
<body>
    <?php
    MostrarNavbar();
    ?>
    <div class="container mt-4">
        <h2 class="text-white p-3 rounded" style="background-color: #0b4c66; text-align: center;">
            <?php if ($proyecto_id): ?>
                <?php 
                $nombreProyecto = '';
                foreach ($proyectos as $proyecto) {
                    if ($proyecto['id'] == $proyecto_id) {
                        $nombreProyecto = $proyecto['nombre'];
                        break;
                    }
                }
                ?>
                Tareas del Proyecto: <?= htmlspecialchars($nombreProyecto) ?>
            <?php else: ?>
                Seleccione un Proyecto
            <?php endif; ?>
        </h2>
    </div>
    <div class="container">
        <!-- Selección de Proyecto -->
        <form method="GET" action="board.php" class="proyecto-selector">
            <label for="proyecto">
                <i class="fas fa-project-diagram"></i> 
                Seleccionar Proyecto
            </label>
            <select name="proyecto_id" id="proyecto" onchange="this.form.submit()">
                <option value="">Seleccione un proyecto</option>
                <?php foreach ($proyectos as $proyecto): ?>
                    <option value="<?= $proyecto['id'] ?>" <?= $proyecto['id'] == $proyecto_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($proyecto['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Mostrar formulario solo si hay un proyecto seleccionado -->
        <?php if ($proyecto_id): ?>
            <button id="agregar-tarea-btn" onclick="mostrarFormulario()">Agregar Tarea</button>

            <div id="formulario-tarea" style="display: none;">
                <input type="text" id="nombre" placeholder="Nombre de la tarea" required>
                <textarea id="descripcion" placeholder="Descripción" required></textarea>
                <select id="estado">
                    <option value="1">Por Hacer</option>
                    <option value="2">En Progreso</option>
                    <option value="3">Completado</option>
                </select>
                <select id="usuario">
                    <option value="">Seleccione un responsable</option>
                    <?php
                    $stmt = $pdo->query("SELECT id, email FROM usuarios");
                    while ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$usuario['id']}'>{$usuario['email']}</option>";
                    }
                    ?>
                </select>
                <button onclick="registrar_tarea(<?= $proyecto_id ?>)">Guardar</button>
            </div>
        <?php endif; ?>

        <div class="kanban-board">
            <?php foreach ($tareasPorEstado as $estadoId => $tareas): ?>
                <div class="kanban-column" data-estado="<?= $estadoId ?>">
                    <h4 class="text-center"><?= $estados[$estadoId] ?></h4>
                    <?php foreach ($tareas as $tarea): ?>
                        <div class="kanban-item" data-id="<?= $tarea['id'] ?>">
                            <strong><?= htmlspecialchars($tarea['nombre']) ?></strong>
                            <p><?= htmlspecialchars($tarea['descripcion']) ?></p>
                            <small>Responsable: <?= htmlspecialchars($tarea['responsable'] ?? 'Sin asignar') ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>

        function mostrarFormulario() {
            document.getElementById("formulario-tarea").style.display = "block";
            }

        function registrar_tarea(proyectoId) {
            let nombre = document.getElementById("nombre").value.trim();
            let descripcion = document.getElementById("descripcion").value.trim();
            let estado = document.getElementById("estado").value;
            let usuario = document.getElementById("usuario").value;

            if (!nombre || !descripcion) {
                alert("Todos los campos son obligatorios.");
                return;
            }

            fetch("registrar_tarea.php", {
                method: "POST",
                body: new URLSearchParams({ nombre, descripcion, estado, usuario, proyecto_id: proyectoId }),
                headers: { "Content-Type": "application/x-www-form-urlencoded" }
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    location.reload(); // Recargar la página para actualizar la lista de tareas
                }
            })
            .catch(error => console.error("Error:", error));
        }
        document.addEventListener("DOMContentLoaded", function () {
        let columns = document.querySelectorAll('.kanban-column'); // Obtener todas las columnas
        let drake = dragula([...columns]); // Inicializar Dragula en las columnas

            drake.on('drop', function (el, target, source, sibling) {
                let taskId = el.getAttribute('data-id'); // ID de la tarea arrastrada
                let estadoId = target.getAttribute('data-estado'); // Nuevo estado 

                // Enviar la actualización del estado a la base de datos
                fetch('actualizar_estado_tareas.php', {
                    method: 'POST',
                    body: JSON.stringify({ id: taskId, estado_id: estadoId }),
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Respuesta del servidor:", data); 

                    if (data.error) {
                        alert("Error al actualizar la tarea: " + data.error);
                    } else {
                        console.log("Estado actualizado correctamente.");
                    }
                })
                .catch(error => {
                    console.error("Error en la petición fetch:", error);
                });
            });
        });
    </script>
</body>
</html>