<?php
session_start();
require_once 'db_config.php';
require_once 'auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $cliente = trim($_POST['cliente']);
    $estado = trim($_POST['estado']);

    if (empty($nombre) || empty($cliente) || empty($estado)) {
        echo "<script>alert('Error: Todos los campos son obligatorios.'); window.history.back();</script>";
        exit();
    }

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("UPDATE proyectos SET nombre = :nombre, cliente = :cliente, estado = :estado WHERE id = :id");
        $stmt->execute([
            'nombre' => $nombre,
            'cliente' => $cliente,
            'estado' => $estado,
            'id' => $id
        ]);

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Proyecto actualizado correctamente.'); window.location.href = 'listar_proyectos.php';</script>";
        } else {
            echo "<script>alert('No se encontró el proyecto o no se realizaron cambios.'); window.history.back();</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error al actualizar el proyecto: " . $e->getMessage() . "'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Error: Método no permitido.'); window.location.href = 'listar_proyectos.php';</script>";
}
?>
