<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';
include 'Plantilla.php';

// Obtener el nombre del usuario y las tareas asignadas
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch();

    // Obtener las tareas asignadas al usuario
    $stmt = $pdo->prepare("
        SELECT t.*, p.nombre as proyecto_nombre, e.nombre as estado_nombre 
        FROM tareas t 
        JOIN proyectos p ON t.proyecto_id = p.id 
        JOIN estados e ON t.estado_id = e.id 
        WHERE t.usuario_id = ? 
        ORDER BY t.estado_id");
    $stmt->execute([$_SESSION['user_id']]);
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usuario['nombre'] = 'Usuario';
    $tareas = [];
}

// Organizar tareas por estado
$tareasPorEstado = [
    1 => [], // Por Hacer
    2 => [], // En Progreso
    3 => []  // Completado
];

foreach ($tareas as $tarea) {
    $tareasPorEstado[$tarea['estado_id']][] = $tarea;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600&family=Roboto+Slab:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .welcome-banner {
            background: linear-gradient(135deg, #0b4c66, #083d52);
            color: white;
            padding: 8px 15px;
            border-radius: 0 0 8px 0;
            position: fixed;
            top: 60px;
            left: 0;
            z-index: 999;
            width: auto;
            font-size: 0.9rem;
        }

        .welcome-banner h1 {
            color: white;
            font-size: 1rem;
            margin: 0;
            font-weight: 500;
        }

        #container {
            max-width: 1320px;
            margin: 80px auto 40px;
            padding: 30px;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .archivos-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .tareas-container {
            border-left: 1px solid #e9ecef;
            padding-left: 25px;
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card h3 {
            color: #0b4c66;
            margin-bottom: 20px;
            font-size: 1.4rem;
        }

        .add-button {
            background: #0b4c66;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .add-button:hover {
            background: #083d52;
            transform: translateY(-2px);
        }

        #file-list {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            transition: background 0.3s;
        }

        .file-item:hover {
            background: #f0f0f0;
        }

        .file-item i {
            font-size: 20px;
            color: #007bff;
            margin-right: 10px;
        }

        .file-item a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            flex-grow: 1;
        }

        .file-item a:hover {
            text-decoration: underline;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .estado-columna {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .estado-titulo {
            color: #0b4c66;
            font-size: 1.2rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0b4c66;
        }

        .tarea-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .tarea-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .tarea-proyecto {
            font-size: 0.9rem;
            color: #0b4c66;
            font-weight: 600;
        }

        .tarea-titulo {
            font-size: 1.1rem;
            margin: 5px 0;
        }

        .tarea-descripcion {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php MostrarNavbar(); ?>

    <div class="welcome-banner">
        <h1>Bienvenido, <?= htmlspecialchars($usuario['nombre']); ?></h1>
    </div>

    <div id="container">
        <div class="dashboard">
            <div class="archivos-container">
                <div class="card">
                    <h3>Subir Archivos</h3>
                    <form id="upload-form" enctype="multipart/form-data">
                        <input type="file" name="file" id="file" required>
                        <button class="add-button" type="submit">Subir</button>
                    </form>
                    <div id="upload-status"></div>
                </div>
                <div class="card">
                    <h3>Archivos Subidos</h3>
                    <div id="file-list">
                        <!-- Aquí se mostrarán los archivos subidos -->
                    </div>
                </div>
            </div>
            
            <div class="tareas-container">
                <h3>Mis Tareas Asignadas</h3>
                <?php
                $estados = [1 => "Por Hacer", 2 => "En Progreso", 3 => "Completado"];
                foreach ($estados as $estadoId => $estadoNombre): ?>
                    <div class="estado-columna">
                        <h4 class="estado-titulo"><?= $estadoNombre ?></h4>
                        <?php if (!empty($tareasPorEstado[$estadoId])): ?>
                            <?php foreach ($tareasPorEstado[$estadoId] as $tarea): ?>
                                <div class="tarea-item">
                                    <div class="tarea-proyecto">
                                        <?= htmlspecialchars($tarea['proyecto_nombre']) ?>
                                    </div>
                                    <div class="tarea-titulo">
                                        <?= htmlspecialchars($tarea['nombre']) ?>
                                    </div>
                                    <div class="tarea-descripcion">
                                        <?= htmlspecialchars($tarea['descripcion']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay tareas en este estado</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("upload-form").addEventListener("submit", function(e) {
            e.preventDefault();
            
            let formData = new FormData();
            formData.append("file", document.getElementById("file").files[0]);

            fetch("upload.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById("upload-status").innerHTML = data;
                loadFiles();
            })
            .catch(error => console.error("Error:", error));
        });

        function loadFiles() {
            fetch("list_files.php")
            .then(response => response.text())
            .then(data => {
                document.getElementById("file-list").innerHTML = data;
            });
        }

        function deleteFile(filename) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "Esta acción eliminará el archivo de forma permanente.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("delete_file.php?file=" + filename, {
                        method: "GET"
                    })
                    .then(response => response.text())
                    .then(data => {
                        Swal.fire("Eliminado", data, "success");
                        loadFiles();
                    })
                    .catch(error => console.error("Error:", error));
                }
            });
        }

        loadFiles();
    </script>

</body>
</html>
