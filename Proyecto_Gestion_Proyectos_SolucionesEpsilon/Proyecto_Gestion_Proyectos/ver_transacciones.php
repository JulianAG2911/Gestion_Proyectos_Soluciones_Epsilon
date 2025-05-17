<?php
Session_start();
require_once 'db_config.php';
Include 'Plantilla.php';

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Obtener todas las transacciones
$sql = "SELECT t.id, t.tipo, t.monto, t.descripcion, t.fecha, c.nombre AS categoria 
        FROM transacciones t 
        LEFT JOIN categorias_gastos c ON t.categoria_id = c.id
        ORDER BY t.fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Transacciones</title>
    <link rel="stylesheet" href="../CSS/contabilidad.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 40px auto;
            width: 90%;
            max-width: 1320px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        table th {
            background-color: #0b4c66;
            color: white;
            padding: 15px;
            font-weight: 500;
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        table tr:hover {
            background-color: #f8f9fa;
        }

        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn-custom {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-edit {
            background-color: #ffc107;
            color: black;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-receipt {
            background-color: #0b4c66;
            color: white;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
        }

        .btn-edit:hover { background-color: #e0a800; }
        .btn-delete:hover { background-color: #c82333; }
        .btn-receipt:hover { background-color: #083d52; }

        .container-header {
            background: linear-gradient(135deg, #0b4c66, #083d52);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
    </style>
    <script>
        function confirmarEliminacion(id) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "No podrás revertir esta acción.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "eliminar_transaccion.php?id=" + id;
                }
            });
        }
    </script>
</head>
<body>
<?php MostrarNavbar(); ?>
    <div class="form-container">
        <div class="container-header">
            <h2>Listado de Transacciones</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Descripción</th>
                    <th>Fecha</th>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transacciones as $transaccion) : ?>
                    <tr>
                        <td><?php echo $transaccion['id']; ?></td>
                        <td><?php echo ucfirst($transaccion['tipo']); ?></td>
                        <td><?php echo number_format($transaccion['monto'], 2); ?> USD</td>
                        <td><?php echo htmlspecialchars($transaccion['descripcion']); ?></td>
                        <td><?php echo $transaccion['fecha']; ?></td>
                        <td><?php echo $transaccion['categoria'] ?? 'N/A'; ?></td>
                        <td class="actions">
                            <button class="btn-custom btn-edit" onclick="window.location.href='editar_transaccion.php?id=<?php echo $transaccion['id']; ?>'">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn-custom btn-delete" onclick="confirmarEliminacion(<?php echo $transaccion['id']; ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                            <button class="btn-custom btn-receipt" onclick="window.location.href='generar_recibo.php?id=<?php echo $transaccion['id']; ?>'">
                                <i class="fas fa-file-alt"></i> Recibo
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
