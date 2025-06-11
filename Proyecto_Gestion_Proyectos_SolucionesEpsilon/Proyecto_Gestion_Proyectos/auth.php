<?php
function requireAdmin() {

    if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] !== 1) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Acceso denegado. Solo administradores pueden acceder a esta sección.',
            'redirect' => 'login.php'
        ];
        header("Location: no_autorizado.php");
        exit();
    }
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Debe iniciar sesión para acceder a esta página.',
            'redirect' => 'login.php'
        ];
        header("Location: login.php");
        exit();
    }
}
