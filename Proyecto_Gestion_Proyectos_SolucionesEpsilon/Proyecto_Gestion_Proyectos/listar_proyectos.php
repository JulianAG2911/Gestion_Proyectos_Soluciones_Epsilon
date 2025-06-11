<?php
session_start();
require_once 'db_config.php';
include 'Plantilla.php';
require_once 'auth.php';
requireLogin(); 

// Obtener el rol del usuario
$isAdmin = isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Obtener filtro de estado si se ha seleccionado
$filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';

// Obtener dirección del ordenamiento
$orden_fecha = isset($_GET['orden']) ? $_GET['orden'] : 'DESC';
$siguiente_orden = ($orden_fecha == 'ASC') ? 'DESC' : 'ASC';

// Construcción de la consulta SQL
$sql = "SELECT * FROM proyectos";
$params = [];

if ($filtro_estado) {
    $sql .= " WHERE estado = ?";
    $params[] = $filtro_estado;
}

// Modificar el ORDER BY para incluir la dirección
if (isset($_GET['ordenar']) && $_GET['ordenar'] == 'fecha') {
    $sql .= " ORDER BY fecha_creacion " . $orden_fecha;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proyectos</title>
    <link rel="stylesheet" href="../CSS/estilos.css"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .contenedor-principal {
            width: 100%;
            max-width: 100%;
            padding: 0 15px;
        }

        .filtros-container {
            max-width: 1320px;
            margin: 0 auto 20px;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .filtro-label {
            font-size: 1.1rem;
            color: #0b4c66;
            font-weight: 600;
            margin-bottom: 12px;
            display: block;
        }

        .filtro-select {
            width: 300px;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            color: #495057;
            background-color: #fff;
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: left;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%230b4c66' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 12px) center;
            padding-right: 35px;
        }

        .filtro-select:hover {
            border-color: #0b4c66;
        }

        .filtro-select:focus {
            border-color: #0b4c66;
            box-shadow: 0 0 0 3px rgba(11, 76, 102, 0.1);
            outline: none;
        }

        .filtro-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .acciones-container {
            max-width: 1320px;
            margin: 20px auto;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-accion-superior {
            background-color: #0b4c66;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-accion-superior:hover {
            background-color: #083d52;
            transform: translateY(-2px);
        }

        .btn-accion-superior i {
            margin-right: 5px;
            transition: transform 0.3s ease;
        }

        .orden-asc i {
            transform: rotate(180deg);
        }

        .tabla-proyectos {
            max-width: 1320px;
            margin: 0 auto;
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .tabla-proyectos th {
            background-color: #0b4c66;
            color: white;
            padding: 15px;
        }

        .tabla-proyectos td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .tabla-proyectos tr:hover {
            background-color: #f8f9fa;
        }

        .btn-accion {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 5px;
            border: none;
        }

        .btn-ver {
            background-color: #0b4c66;
            color: white;
        }

        .btn-editar {
            background-color: #ffc107;
            color: black;
        }

        .btn-eliminar {
            background-color: #dc3545;
            color: white;
        }

        .btn-ver:hover { background-color: #083d52; color: white; }
        .btn-editar:hover { background-color: #e0a800; }
        .btn-eliminar:hover { background-color: #c82333; color: white; }
    </style>
</head>
<body>

<?php MostrarNavbar(); ?>
    <div class="container mt-4">
        <h2 class="text-white p-3 rounded" style="background-color: #0b4c66; text-align: center;">Gestión de Proyectos</h2>
    </div>
    
    <div class="container">
        <div class="filtros-container">
            <form method="GET" action="<?= BASE_URL ?>listar_proyectos.php" class="filtro-form">
                <label for="filtro_estado" class="filtro-label">
                    <i class="fas fa-filter"></i> Filtrar Proyectos por Estado
                </label>
                <select name="filtro_estado" class="filtro-select" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="En progreso" <?= $filtro_estado == 'En progreso' ? 'selected' : ''; ?>>En progreso</option>
                    <option value="En revisión" <?= $filtro_estado == 'En revisión' ? 'selected' : ''; ?>>En revisión</option>
                    <option value="Finalizado" <?= $filtro_estado == 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                    <option value="Inactivo" <?= $filtro_estado == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </form>
        </div>

        <div class="acciones-container">
            <a href="?ordenar=fecha&orden=<?= $siguiente_orden ?><?= $filtro_estado ? '&filtro_estado='.$filtro_estado : '' ?>" 
               class="btn-accion-superior <?= $orden_fecha == 'ASC' ? 'orden-asc' : '' ?>">
                <i class="fas fa-sort-amount-down"></i> 
                Ordenar por fecha (<?= $orden_fecha == 'ASC' ? 'Más antiguos' : 'Más recientes' ?>)
            </a>
            <a href="board.php" class="btn-accion-superior">
                <i class="fas fa-tasks"></i> Tareas
            </a>
            <a href="registrar_proyectos.php" class="btn-accion-superior">
                <i class="fas fa-plus"></i> Registrar Proyecto
            </a>
        </div>

        <div class="table-responsive">
            <table class="tabla-proyectos">
                <tr>
                    <th>ID</th>
                    <th>Nombre del Proyecto</th>
                    <th>Cliente</th>
                    <th>Fecha de Creación</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                <?php foreach ($proyectos as $proyecto): ?>
                    <tr>
                        <td><?= htmlspecialchars($proyecto['id']); ?></td>
                        <td><?= htmlspecialchars($proyecto['nombre']); ?></td>
                        <td><?= htmlspecialchars($proyecto['cliente']); ?></td>
                        <td><?= htmlspecialchars($proyecto['fecha_creacion']); ?></td>
                        <td><?= htmlspecialchars($proyecto['estado']); ?></td>
                        <td>
                            <a href="ver_proyecto.php?id=<?= $proyecto['id']; ?>" class="btn-accion btn-ver">Ver Detalles</a>
                            <?php if ($isAdmin): ?>
                                <a href="editar_proyecto.php?id=<?= $proyecto['id']; ?>" class="btn-accion btn-editar">Editar</a>
                                <a href="javascript:void(0);" class="btn-accion btn-eliminar" onclick="confirmarEliminar(<?= $proyecto['id']; ?>);">Eliminar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

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
                // Redirigir al enlace de eliminación con el ID del proyecto
                window.location.href = "eliminar_proyecto.php?id=" + id;
            }
        });
    }
</script>

</body>
</html>
