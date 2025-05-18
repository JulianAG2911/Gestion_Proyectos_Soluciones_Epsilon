<?php
Session_start();
require_once 'db_config.php';
Include 'Plantilla.php';
include 'reportes_config.php';
// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sección de Reportes</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
    <style>
        .submit-btn {
            background-color: #0b4c66 !important;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s ease, transform 0.3s ease; 
        }

        .submit-btn:hover {
            background-color: #083344 !important;
            transform: scale(1.05); 
        }

        .submit-btn:active {
            background-color: #062733 !important;
            transform: scale(1); 
        }

        /* Estilo del contenedor principal */
        .main-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }

        .form-section {
            flex: 0 0 400px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .preview-section {
            flex: 1;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            min-height: 600px;
        }

        /* Estilo del iframe */
        #reporte-preview {
            width: 100%;
            height: 100%;
            min-height: 560px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php MostrarNavbar(); ?>
    <div class="container mt-4">
        <h2 class="text-white p-3 rounded" style="background-color: #0b4c66; text-align: center;">Reportes</h2>
    </div>
    <div class="main-container">
        <div class="form-section">
            <h2>Seleccionar Reporte</h2>
            <form id="reporte-form" action="procesar_reporte.php" method="GET" target="reporte-preview">
                <input type="hidden" name="mode" value="download">
                <label for="reporte">Tipo de Reporte:</label>
                <select name="reporte" required>
                    <?php foreach ($REPORTES as $key => $reporte): ?>
                        <option value="<?= $key ?>"><?= $reporte['titulo'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="formato">Formato de Exportación:</label>
                <select name="formato" required>
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel (.xlsx)</option>
                    <option value="csv">CSV</option>
                </select>

                <button type="button" class="submit-btn" onclick="previewReport()">Vista Previa</button>
                <button type="submit" class="submit-btn">Descargar Reporte</button>
            </form>
        </div>
        <div class="preview-section">
            <iframe id="reporte-preview" name="reporte-preview" src=""></iframe>
        </div>
    </div>
    <script>
        function previewReport() {
            const form = document.getElementById('reporte-form');
            form.target = 'reporte-preview';
            // Cambiar el modo a preview
            const modeInput = form.querySelector('input[name="mode"]');
            modeInput.value = 'preview';
            form.submit();
            // Restaurar el modo a download
            modeInput.value = 'download';
        }
    </script>
</body>
</html>
