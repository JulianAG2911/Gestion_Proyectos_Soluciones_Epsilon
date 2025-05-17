<?php
session_start();
require_once 'db_config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Consultar los usuarios y sus roles
try {
    $stmt = $pdo->prepare("SELECT u.id, u.nombre, u.apellidos, u.cedula, u.telefono, u.email, r.nombre AS rol 
                           FROM usuarios u 
                           LEFT JOIN roles r ON u.role_id = r.id 
                           ORDER BY u.id DESC");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener usuarios: " . $e->getMessage());
}

include 'Plantilla.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Empleados</title>
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

        .btn-editar {
            background-color: #ffc107;
            color: black;
        }

        .btn-eliminar {
            background-color: #dc3545;
            color: white;
        }

        .btn-editar:hover {
            background-color: #e0a800;
        }

        .btn-eliminar:hover {
            background-color: #c82333;
        }

        .btn-custom {
            background-color: #0b4c66 !important;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #083d52 !important;
            color: white;
        }
    </style>
</head>
<body>
<?php MostrarNavbar(); ?>
    <div class="container mt-4">
        <h2 class="text-white p-3 rounded" style="background-color: #0b4c66; text-align: center;">Empleados</h2>
    </div>
<div class="container mt-4">
    <h2 class="text-center mb-4">Lista de Empleados</h2>
    <div class="text-center mt-3">
        <a href="registrar_usuario.php" class="btn btn-custom">Registrar Nuevo Usuario</a>
        <a href="listar_roles.php" class="btn btn-custom ms-2">Ver Roles</a>
    </div>
    
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Cédula</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario) : ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['apellidos']) ?></td>
                    <td><?= htmlspecialchars($usuario['cedula']) ?></td>
                    <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= htmlspecialchars($usuario['rol']) ?></td>
                    <td>
                        <a href="editar_usuario.php?id=<?= $usuario['id'] ?>" class="btn-accion btn-editar">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <button class="btn-accion btn-eliminar" onclick="confirmarEliminacion(<?= $usuario['id'] ?>)">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Función para confirmar eliminación con SweetAlert
function confirmarEliminacion(id) {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "eliminar_usuario.php?id=" + id;
        }
    });
}

</script>

</body>
</html>
