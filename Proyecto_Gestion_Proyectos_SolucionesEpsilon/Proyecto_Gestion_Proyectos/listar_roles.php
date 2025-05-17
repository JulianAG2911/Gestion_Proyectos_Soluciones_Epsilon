<?php
session_start();
require_once 'db_config.php';
Include 'Plantilla.php';
require_once 'auth.php'; 
requireAdmin();

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener roles y número de empleados en cada rol
    $stmt = $pdo->query("
        SELECT r.id, r.nombre, COUNT(e.id) AS num_usuarios
        FROM roles r
        LEFT JOIN usuarios e ON r.id = e.role_id
        GROUP BY r.id, r.nombre
    ");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Roles</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .btn-accion {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 5px;
            border: none;
        }

        .btn-registrar {
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

        .btn-registrar:hover {
            background-color: #083d52;
            color: white;
            transform: translateY(-2px);
        }

        .btn-editar:hover {
            background-color: #e0a800;
            transform: translateY(-2px);
        }

        .btn-eliminar:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-dark th {
            background-color: #0b4c66;
        }
    </style>
</head>
<body>
    <?php MostrarNavbar(); ?>
    <div class="container mt-4">
        <h2 class="text-white p-3 rounded" style="background-color: #0b4c66; text-align: center;">Roles</h2>
    </div>
    <div class="container mt-4">
        <h2 class="text-center mb-4">Lista de Roles</h2>
        <div class="text-center mt-3 mb-4">
            <a href="registrar_roles.php" class="btn-accion btn-registrar">Registrar Nuevo Rol</a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Rol</th>
                        <th>Número de Empleados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $rol): ?>
                            <tr>
                                <td><?= htmlspecialchars($rol['id']) ?></td>
                                <td><?= htmlspecialchars($rol['nombre']) ?></td>
                                <td><?= htmlspecialchars($rol['num_usuarios']) ?></td>
                                <td>
                                    <a href="editar_rol.php?id=<?= $rol['id'] ?>" class="btn-accion btn-editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="javascript:void(0);" class="btn-accion btn-eliminar" onclick="confirmarEliminar(<?= $rol['id']; ?>);">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay roles registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

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
                window.location.href = "eliminar_rol.php?id=" + id;
            }
        });
    }
</script>
