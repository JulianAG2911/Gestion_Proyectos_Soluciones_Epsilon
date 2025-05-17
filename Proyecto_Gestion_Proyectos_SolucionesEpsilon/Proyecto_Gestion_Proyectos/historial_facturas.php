<?php
session_start();
require_once 'db_config.php';
include 'Plantilla.php';

// Conexión
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener historial
$sql = "SELECT f.*, c.nombre FROM facturas f JOIN clientes c ON f.cliente_id = c.id ORDER BY f.fecha_emision DESC";
$facturas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial de Facturas</title>
    <link rel="stylesheet" href="../CSS/contabilidad.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 40px auto;
            width: 90%;
            max-width: 1320px;
        }

        .page-header {
            background: linear-gradient(135deg, #0b4c66, #083d52);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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

        select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 2px solid #e9ecef;
            background-color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 120px;
            font-weight: 500;
        }

        select:hover {
            border-color: #0b4c66;
        }

        select:focus {
            outline: none;
            border-color: #0b4c66;
            box-shadow: 0 0 0 3px rgba(11, 76, 102, 0.1);
        }

        .estado-pagada {
            color: #28a745;
            border-color: #28a745;
        }

        .estado-impaga {
            color: #dc3545;
            border-color: #dc3545;
        }

        .acciones-container {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <?php MostrarNavbar(); ?>

    <div class="container mt-4">
        <h2 class="text-white p-3 rounded" style="background-color: #0b4c66; text-align: center;">Historial de Facturas</h2>
    </div>

    <div class="form-container">

        <table>
            <tr>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Descripción</th>
                <th>Estado</th>
            </tr>
            <?php foreach ($facturas as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['nombre']) ?></td>
                    <td><?= date('d-m-Y', strtotime($f['fecha_emision'])) ?></td>
                    <td>₡<?= number_format($f['monto'], 2) ?></td>
                    <td><?= htmlspecialchars($f['descripcion']) ?></td>
                    <td>
                        <select onchange="cambiarEstadoFactura(<?= $f['id'] ?>, this.value, this)" 
                                class="<?= $f['pagada'] ? 'estado-pagada' : 'estado-impaga' ?>">
                            <option value="1" <?= $f['pagada'] ? 'selected' : '' ?>>Pagada</option>
                            <option value="0" <?= !$f['pagada'] ? 'selected' : '' ?>>Impaga</option>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <script>
        function cambiarEstadoFactura(id, nuevoEstado, selectElement) {
            // Actualizar el estilo inmediatamente
            selectElement.className = nuevoEstado === '1' ? 'estado-pagada' : 'estado-impaga';

            fetch('actualizar_estado_factura.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&estado=${nuevoEstado}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    // Revertir el cambio si hubo error
                    selectElement.value = nuevoEstado === '1' ? '0' : '1';
                    selectElement.className = nuevoEstado === '1' ? 'estado-impaga' : 'estado-pagada';
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'No se pudo actualizar.',
                        icon: 'error'
                    });
                }
            });
        }
    </script>
</body>

</html>