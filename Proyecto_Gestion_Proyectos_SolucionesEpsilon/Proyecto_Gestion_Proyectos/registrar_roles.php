<?php
session_start();
require_once 'db_config.php';
include 'Plantilla.php';
require_once 'auth.php'; 
requireAdmin();

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Funciones para la gestión de roles
function listarRoles($pdo) {
    $sql = "
        SELECT r.id, r.nombre, COUNT(e.id) AS num_usuarios
        FROM roles r
        LEFT JOIN usuarios e ON r.id = e.role_id
        GROUP BY r.id, r.nombre
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function registrarRol($pdo, $nombre) {
    $stmt = $pdo->prepare("INSERT INTO roles (nombre) VALUES (:nombre)");
    $stmt->execute(['nombre' => $nombre]);
}

function obtenerRol($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function actualizarRol($pdo, $id, $nombre) {
    $stmt = $pdo->prepare("UPDATE roles SET nombre = :nombre WHERE id = :id");
    $stmt->execute(['nombre' => $nombre, 'id' => $id]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
    <!-- Incluir SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .main-container {
            display: flex;
            gap: 30px;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .form-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        #registro-rol {
            flex: 0 0 350px;
        }

        #lista-roles {
            flex: 1;
        }

        .btn-accion {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .btn-editar {
            background-color: #ffc107;
            color: black;
        }

        .btn-eliminar {
            background-color: #dc3545;
        }

        .btn-editar:hover {
            background-color: #e0a800;
        }

        .btn-eliminar:hover {
            background-color: #c82333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #0b4c66;
            color: white;
            padding: 12px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
<?php MostrarNavbar(); ?>
    <div class="container mt-4">
        <h2 class="text-white p-3 rounded" style="background-color: #0b4c66; text-align: center;">Gestión de Roles</h2>
    </div>
    <div class="main-container">
        <div class="form-container" id="registro-rol"> 
            <h2>Registrar Nuevo Rol</h2>
            <form method="POST" action="">
                <label for="nombre">Nombre del Rol:</label>
                <input type="text" id="nombre" name="nombre" required>
                <button type="submit" name="registrar">Registrar Rol</button>
            </form>
        </div>

        <div class="form-container" id="lista-roles"> 
            <h2>Lista de Roles</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Número de Empleados</th>
                    <th>Acciones</th>
                </tr>
                <?php
                $roles = listarRoles($pdo);
                foreach ($roles as $rol) {
                    echo "<tr>";
                    echo "<td>{$rol['id']}</td>";
                    echo "<td>{$rol['nombre']}</td>";
                    echo "<td>{$rol['num_usuarios']}</td>";
                    echo "<td>
                        <a href='editar_rol.php?id={$rol['id']}' class='btn-accion btn-editar'>
                            <i class='fas fa-edit'></i> Editar
                        </a>
                        <a href='javascript:void(0)' onclick='confirmarEliminar({$rol['id']})' class='btn-accion btn-eliminar'>
                            <i class='fas fa-trash'></i> Eliminar
                        </a>
                    </td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>

    <?php
    // Procesar registro de rol
    if (isset($_POST['registrar'])) {
        $nombre = $_POST['nombre'];
        registrarRol($pdo, $nombre);
        // Mostrar el mensaje de éxito con SweetAlert
        echo "
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Rol registrado exitosamente',
                showConfirmButton: false,
                timer: 1500 // Redirigir después de 1.5 segundos
            }).then(function() {
                window.location.href = 'listar_roles.php'; 
            });
        </script>";
    }
    ?>

    <script>
    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'eliminarRol.php?id=' + id;
            }
        });
    }
    </script>
</body>
</html>
