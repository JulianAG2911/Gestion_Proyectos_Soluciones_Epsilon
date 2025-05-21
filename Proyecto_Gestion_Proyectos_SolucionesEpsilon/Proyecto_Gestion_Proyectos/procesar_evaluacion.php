<?php
require_once 'db_config.php';
require_once 'auth.php'; 
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = isset($_POST['empleado']) ? intval($_POST['empleado']) : null;
    $fecha = trim($_POST['fecha'] ?? '');
    $comentarios = trim($_POST['comentarios'] ?? '');
    $puntuacion = floatval($_POST['puntuacion'] ?? 0);
    $horas = intval($_POST['horas_trabajadas'] ?? 0);
    $completadas = intval($_POST['tareas_completadas'] ?? 0);
    $progreso = intval($_POST['tareas_progreso'] ?? 0);

    error_log("POST DATA: " . print_r($_POST, true));

    if (!$usuario_id || !$fecha || !$comentarios || !$puntuacion || !$horas || !$completadas || !$progreso) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Todos los campos son obligatorios.',
                confirmButtonColor: '#0b4c66'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit;
    }

    if ($puntuacion < 1.0 || $puntuacion > 10.0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La puntuación debe estar entre 1.0 y 10.0.',
                confirmButtonColor: '#0b4c66'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit;
    }

    // Validar fecha futura
    if (strtotime($fecha) > strtotime(date('Y-m-d'))) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No puedes seleccionar una fecha futura.',
                confirmButtonColor: '#0b4c66'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit;
    }

    // Validar límites de horas y tareas
    if ($horas > 100 || $horas < 1) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las horas trabajadas deben estar entre 1 y 100.',
                confirmButtonColor: '#0b4c66'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit;
    }

    if ($completadas > 100 || $completadas < 0 || $progreso > 100 || $progreso < 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las tareas completadas y en progreso deben estar entre 0 y 100.',
                confirmButtonColor: '#0b4c66'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id = :usuario_id");
        $stmt->execute(['usuario_id' => $usuario_id]);
        $exists = $stmt->fetchColumn();

        if (!$exists) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El empleado no existe.',
                    confirmButtonColor: '#0b4c66'
                }).then(() => {
                    window.history.back();
                });
            </script>";
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO evaluaciones_desempeno 
            (usuario_id, fecha, comentarios, puntuacion, horas_trabajadas, tareas_completadas, tareas_en_progreso)
            VALUES (:usuario_id, :fecha, :comentarios, :puntuacion, :horas, :completadas, :progreso)
        ");

        $stmt->execute([
            'usuario_id' => $usuario_id,
            'fecha' => $fecha,
            'comentarios' => $comentarios,
            'puntuacion' => $puntuacion,
            'horas' => $horas,
            'completadas' => $completadas,
            'progreso' => $progreso
        ]);

        if ($stmt->rowCount() > 0) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Evaluación registrada exitosamente.',
                    confirmButtonColor: '#0b4c66'
                }).then(() => {
                    window.location.href = 'listar_evaluaciones.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo guardar la evaluación.',
                    confirmButtonColor: '#0b4c66'
                }).then(() => {
                    window.history.back();
                });
            </script>";
        }
    } catch (PDOException $e) {
        error_log("Error SQL: " . $e->getMessage());
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al guardar la evaluación.',
                confirmButtonColor: '#0b4c66'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}
?>
</body>
</html>