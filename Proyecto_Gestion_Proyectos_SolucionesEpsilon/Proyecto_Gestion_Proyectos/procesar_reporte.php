<?php
session_start();
require '../../vendor/autoload.php'; // Modificado para apuntar al directorio padre
require 'pdf_reporte_generator.php'; 
require 'db_config.php';
require_once 'auth.php';
require_once 'reportes_config.php';
requireAdmin();
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
$reporte = $_GET['reporte'] ?? '';
$formato = $_GET['formato'] ?? 'pdf';
$mode = $_GET['mode'] ?? 'download';

if (!isset($REPORTES[$reporte])) {
    die('Reporte no válido.');
}

$conf = $REPORTES[$reporte];

// Ejecutar consulta
$stmt = $pdo->prepare($conf['query']);
if ($conf['params']) {
    $stmt->bindParam(1, $filtro, PDO::PARAM_INT); // Usamos bindParam y aseguramos el tipo de dato
}
$stmt->execute();

// Cambiamos el método get_result() a fetchAll()
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Exportar según formato
switch (strtolower($formato)) {
    case 'pdf':
        $generator = new PDFReportGenerator($conf['titulo']);
        $generator->addTable($conf['headers'], $data);
        if ($mode === 'preview') {
            $generator->preview();
        } else {
            $generator->output("reporte_{$reporte}.pdf");
        }
        break;

    case 'excel':
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($conf['headers'], NULL, 'A1');
        $sheet->fromArray($data, NULL, 'A2');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=reporte_{$reporte}.xlsx");
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        break;

    case 'csv':
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=reporte_{$reporte}.csv");
        $fp = fopen('php://output', 'w');
        fputcsv($fp, $conf['headers']);
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        break;

    default:
        die('Formato no soportado.');
}
?>