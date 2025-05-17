<?php
session_start();
require_once 'db_config.php';
require_once 'auth.php'; 
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $cedula = trim($_POST['cedula']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    $rol = intval($_POST['rol']);  // Role ID (será el ID de la tabla de roles)

    // Validación de campos obligatorios
    if (empty($nombre) || empty($apellidos) || empty($fecha_nacimiento) || empty($cedula) || empty($telefono) || empty($email) || empty($password) || empty($rol)) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Todos los campos son obligatorios.'
        ];
        header("Location: registrar_usuario.php");
        exit;
    }

    // Obtener el ID del usuario (administrador) que está registrando
    $usuario_id = $_SESSION['usuario_id'];

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Validar si la cédula o el email ya existen en la base de datos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE cedula = :cedula OR email = :email");
        $stmt->execute(['cedula' => $cedula, 'email' => $email]);
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'La cédula o el correo electrónico ya están registrados.'
            ];
            header("Location: listar_usuarios.php");
            exit;
        }

        // Insertar el nuevo usuario en la tabla 'usuarios'
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, apellidos, fecha_nacimiento, cedula, telefono, email, password, role_id) 
            VALUES (:nombre, :apellidos, :fecha_nacimiento, :cedula, :telefono, :email, :password, :role_id)
        ");
        $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'fecha_nacimiento' => $fecha_nacimiento,
            'cedula' => $cedula,
            'telefono' => $telefono,
            'email' => $email,
            'password' => $password,
            'role_id' => $rol
        ]);

        // Mensaje de éxito
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Empleado registrado exitosamente.'
        ];
        header("Location: listar_usuarios.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Error al registrar el empleado: ' . $e->getMessage()
        ];
        header("Location: registrar_usuario.php");
        exit;
    }
}
?>
