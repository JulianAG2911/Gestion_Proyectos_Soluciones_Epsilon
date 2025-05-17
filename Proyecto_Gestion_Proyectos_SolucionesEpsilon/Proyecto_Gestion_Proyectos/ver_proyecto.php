<?php
session_start();
require_once 'db_config.php';
include 'Plantilla.php';

if (!isset($_GET['id'])) {
    die("ID de proyecto no proporcionado.");
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM proyectos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proyecto) {
        die("Proyecto no encontrado.");
    }
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles del Proyecto</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .proyecto-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .proyecto-header {
            background: linear-gradient(135deg, #0b4c66, #083d52);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }

        .proyecto-header h2 {
            color: white;
            margin: 0;
            font-size: 1.8rem;
        }

        .proyecto-info {
            display: grid;
            gap: 15px;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .info-item {
            display: flex;
            align-items: baseline;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #0b4c66;
            width: 150px;
            flex-shrink: 0;
        }

        .info-value {
            color: #495057;
            flex-grow: 1;
        }

        .estado-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            background: #0b4c66;
            color: white;
        }

        .botones-container {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            justify-content: flex-end;
        }

        .btn-accion {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-editar {
            background-color: #ffc107;
            color: black;
        }

        .btn-eliminar {
            background-color: #dc3545;
            color: white;
        }

        .btn-volver {
            background-color: #0b4c66;
            color: white;
        }

        .btn-accion:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<?php MostrarNavbar(); ?>
    <div class="container mt-4">
        <div class="proyecto-container">
            <div class="proyecto-header">
                <h2>Detalles del Proyecto</h2>
            </div>
            <div class="proyecto-info">
                <div class="info-item">
                    <span class="info-label">ID:</span>
                    <span class="info-value"><?= htmlspecialchars($proyecto['id']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value"><?= htmlspecialchars($proyecto['nombre']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cliente:</span>
                    <span class="info-value"><?= htmlspecialchars($proyecto['cliente']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha de Creación:</span>
                    <span class="info-value"><?= htmlspecialchars($proyecto['fecha_creacion']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Estado:</span>
                    <span class="estado-badge"><?= htmlspecialchars($proyecto['estado']); ?></span>
                </div>
            </div>

            <div class="botones-container">
                <a href="editar_proyecto.php?id=<?= $proyecto['id']; ?>" class="btn-accion btn-editar">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="javascript:void(0);" class="btn-accion btn-eliminar" onclick="confirmarEliminar(<?= $proyecto['id']; ?>)">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
                <a href="listar_proyectos.php" class="btn-accion btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

<script>
    // Confirmación para eliminar con SweetAlert
    function confirmarEliminar(id) {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "No podrás revertir esta acción",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `eliminar_proyecto.php?id=${id}`;
            }
        });
    }

    // Mensaje de éxito después de una acción
    function mostrarMensaje(tipo, mensaje) {
        Swal.fire({
            icon: tipo,
            title: mensaje,
            showConfirmButton: false,
            timer: 2000
        });
    }

    // Mensaje de éxito si la URL tiene ?success=true
    <?php if (isset($_GET['success'])): ?>
        mostrarMensaje("success", "Acción realizada con éxito");
    <?php endif; ?>
</script>

</body>
</html>
