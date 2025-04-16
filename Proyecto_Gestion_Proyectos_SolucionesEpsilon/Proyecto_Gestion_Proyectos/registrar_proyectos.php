<?php
session_start();
require_once 'db_config.php';
include 'Plantilla.php';

$proyecto_creado = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $nombre = $_POST['nombre'];
        $cliente = $_POST['cliente'];
        $estado = $_POST['estado']; // Nuevo campo para estado
        $fecha_creacion = date('Y-m-d');

        // Insertar el proyecto con estado en la base de datos
        $stmt = $pdo->prepare("INSERT INTO proyectos (nombre, cliente, estado, fecha_creacion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $cliente, $estado, $fecha_creacion]);

        // Marcar que el proyecto fue creado
        $proyecto_creado = true;

    } catch (PDOException $e) {
        die("Error al crear el proyecto: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proyectos</title>
    <link rel="stylesheet" href="../CSS/estilos.css"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php MostrarNavbar(); ?>

<div class="form-container">
    <h2>Crear Nuevo Proyecto</h2>
    <form action="registrar_proyectos.php" method="POST">
        <label for="nombre">Nombre del Proyecto:</label>
        <input type="text" name="nombre" id="nombre" required>

        <label for="cliente">Cliente:</label>
        <input type="text" name="cliente" id="cliente" required>

        <!-- Nuevo campo para seleccionar estado -->
        <label for="estado">Estado del Proyecto:</label>
        <select name="estado" id="estado">
            <option value="En progreso">En progreso</option>
            <option value="En revisión">En revisión</option>
            <option value="Finalizado">Finalizado</option>
            <option value="Inactivo">Inactivo</option>
        </select>

        <button type="submit" class="btn">Crear Proyecto</button>
    </form>

    <!-- Contenedor para los botones "Ver Proyectos Creados" y "Ver Calendario" -->
    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
        <a href="listar_proyectos.php" class="btn">Ver Proyectos Creados</a>
        <a href="calendario.php" class="btn">Ver Calendario</a>
    </div>
</div>

<?php if ($proyecto_creado): ?>
    <script>
        Swal.fire({
            title: '¡Éxito!',
            text: 'Proyecto creado exitosamente.',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'listar_proyectos.php'; // Redirigir a la página de proyectos
            }
        });
    </script>
<?php endif; ?>

</body>
</html>
