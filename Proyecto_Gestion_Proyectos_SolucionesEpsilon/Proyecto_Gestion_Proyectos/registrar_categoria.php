<?php
Session_start();
require_once 'db_config.php';
include 'Plantilla.php';

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Variable para mostrar SweetAlert2 después del registro
$registro_exitoso = false;

// Procesar la inserción de la categoría de gasto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categoria = $_POST['categoria'];

    try {
        $sql = "INSERT INTO categorias_gastos (nombre) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$categoria])) {
            $registro_exitoso = true;
        }
    } catch (PDOException $e) {
        echo "<script>
                Swal.fire({
                    title: 'Error en la base de datos',
                    text: '" . $e->getMessage() . "',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Categoría</title>
    <link rel="stylesheet" href="../CSS/contabilidad.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Ejecutar SweetAlert2 cuando la página haya cargado si la categoría se registró con éxito
        window.onload = function () {
            <?php if ($registro_exitoso): ?>
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Categoría agregada correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'contabilidad.php';
                });
            <?php endif; ?>
        };
    </script>
</head>

<body>
    <?php MostrarNavbar(); ?>

    <style>
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            width: 50%;
            margin: 40px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 15px 20px;
            align-items: center;
        }

        .form-grid label {
            font-weight: bold;
        }

        .form-grid input {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .form-actions {
            grid-column: 2 / 3;
            text-align: right;
            margin-top: 10px;
        }

        .btn-submit {
            background-color: #0b4c66;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #083d52;
            transform: translateY(-2px);
        }

        .btn-back {
            background-color: #0b4c66;
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            cursor: pointer;
            float: right;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: #083d52;
            transform: translateY(-2px);
        }
    </style>

    <div class="form-container">
        <h2 style="text-align:center;">Agregar Nueva Categoría</h2>
        <form method="POST" class="form-grid">
            <label for="categoria">Nombre de la Categoría:</label>
            <input type="text" name="categoria" id="categoria" required>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Agregar Categoría</button>
            </div>
        </form>
    </div>

    <?php if ($registro_exitoso): ?>
        <script>
            Swal.fire({
                title: '¡Éxito!',
                text: 'Categoría agregada correctamente.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'contabilidad.php';
            });
        </script>
    <?php endif; ?>
</body>

</html>