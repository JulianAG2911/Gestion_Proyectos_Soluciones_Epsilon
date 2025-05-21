<?php
Session_start();
require_once 'db_config.php';
require_once 'dompdf/autoload.inc.php';
include 'Plantilla.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

if (isset($_GET['mes_anio'])) {
    [$anio, $mes] = explode('-', $_GET['mes_anio']);
} else {
    $mes = date('m');
    $anio = date('Y');
}

$estado = $_GET['estado'] ?? 'todas';
$resultados = [];

if (isset($_GET['mes_anio'])) {
    $query = "SELECT f.*, c.nombre FROM facturas f 
              JOIN clientes c ON f.cliente_id = c.id 
              WHERE MONTH(f.fecha_emision) = :mes AND YEAR(f.fecha_emision) = :anio";

    if ($estado == 'pagadas') {
        $query .= " AND f.pagada = 1";
    } elseif ($estado == 'impagas') {
        $query .= " AND f.pagada = 0";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':mes' => $mes,
        ':anio' => $anio
    ]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Clase para generar reportes PDF con el formato estándar
class PDFReportGenerator {
    private $dompdf;
    private $html;
    private $title;

    public function __construct($title) {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('chroot', realpath(__DIR__ . '/../'));

        $this->dompdf = new Dompdf($options);
        $this->title = $title;
        $this->html = '<html><head>';
        $this->html .= '<style>
            body { 
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
            }
            .header-container {
                width: 100%;
                border-bottom: 2px solid #0b4c66;
                padding-bottom: 20px;
                margin-bottom: 30px;
                overflow: hidden; 
                display: table; 
                clear: both; 
            }
            .logo-text {
                color: #0b4c66;
                font-size: 24px;
                font-weight: bold;
                margin: 10px 0;
                text-align: center;
            }
            .logo-container {
                float: left;
                width: 25%;
                text-align: center;
                display: table-cell; 
                vertical-align: middle;
            }
            .logo-image {
                width: 100px;
                margin: 10px auto;
            }
            .company-info {
                float: left;
                width: 50%;
                text-align: center;
                display: table-cell; 
                vertical-align: middle;
            }
            .company-info p {
                margin: 5px 0;
                font-size: 14px;
            }
            .report-container {
                float: right;
                width: 25%;
                text-align: center;
                display: table-cell; 
                vertical-align: middle;
            }
            .report-box {
                border: 2px solid #0b4c66;
                display: inline-block;
                padding: 10px 30px;
                margin-top: 15px;
            }
            .report-box h3 {
                margin: 0;
                color: #0b4c66;
                font-size: 16px;
                font-weight: bold;
            }
            .report-box p {
                margin: 5px 0 0 0;
                font-size: 14px;
            }
            table { 
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                margin-bottom: 40px;
            }
            th { 
                background-color: #0b4c66;
                color: white;
                padding: 10px;
                text-align: left;
            }
            td { 
                padding: 8px;
                border: 1px solid #ddd;
            }
            tr:nth-child(even) { 
                background-color: #f9f9f9;
            }
            .footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                text-align: center;
                padding: 10px;
                font-size: 12px;
                color: #666;
                border-top: 1px solid #ddd;
            }
            .summary {
                margin-top: 30px;
                margin-bottom: 30px;
                padding: 15px;
                background-color: #f9f9f9;
                border-left: 5px solid #0b4c66;
            }
            .summary h3 {
                margin-top: 0;
                color: #0b4c66;
            }
        </style></head><body>';

        // Estructura del encabezado con distribución horizontal
        $this->html .= '<div class="header-container">';
        
        // Logo a la izquierda
        $this->html .= '<div class="logo-container">';
        $this->html .= '<img src="' . realpath(__DIR__ . '/../IMG/Logo_SE.png') . '" class="logo-image">';
        $this->html .= '</div>';
        
        // Información de la empresa en el centro
        $this->html .= '<div class="company-info">';
        $this->html .= '<div class="logo-text">Soluciones Epsilon</div>';
        $this->html .= '<p>Desarrollos Tecnológicos Empresariales</p>';
        $this->html .= '<p>Tel: +506 2222-2222</p>';
        $this->html .= '<p>Email: contacto@epsilon.com</p>';
        $this->html .= '<p>San José, Costa Rica</p>';
        $this->html .= '</div>';
        
        // Cuadro de reporte a la derecha
        $this->html .= '<div class="report-container">';
        $this->html .= '<div class="report-box">';
        $this->html .= '<h3>REPORTE</h3>';
        $this->html .= '<p>' . htmlspecialchars($title) . '</p>';
        $this->html .= '</div>';
        $this->html .= '</div>';
        
        $this->html .= '</div>';

        // Agregar el footer
        $this->html .= '<div class="footer">';
        $this->html .= 'Soluciones Epsilon © ' . date('Y') . ' - Página 1';
        $this->html .= '</div>';
    }

    public function addTable($headers, $data) {
        $this->html .= '<div style="clear: both;"></div>'; // Asegura que no haya elementos flotantes
        $this->html .= '<table>';
        
        // Encabezados
        $this->html .= '<tr>';
        foreach ($headers as $header) {
            $this->html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $this->html .= '</tr>';

        // Datos
        foreach ($data as $row) {
            $this->html .= '<tr>';
            foreach ($row as $cell) {
                $this->html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $this->html .= '</tr>';
        }
        
        $this->html .= '</table>';
    }

    public function addSummary($title, $content) {
        $this->html .= '<div class="summary">';
        $this->html .= '<h3>' . htmlspecialchars($title) . '</h3>';
        $this->html .= $content;
        $this->html .= '</div>';
    }

    public function output($filename) {
        $this->html .= '</body></html>';
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('A4', 'landscape');
        $this->dompdf->render();
        $this->dompdf->stream($filename, array("Attachment" => false));
    }
}

// Exportar a PDF 
if (isset($_GET['exportar']) && $_GET['exportar'] == 'pdf') {
    // Título del reporte basado en el estado seleccionado
    $estado_titulo = '';
    if ($estado == 'pagadas') {
        $estado_titulo = 'Facturas Pagadas';
    } elseif ($estado == 'impagas') {
        $estado_titulo = 'Facturas Pendientes';
    } else {
        $estado_titulo = 'Todas las Facturas';
    }

    // Crear instancia del generador de reportes
    $reportGenerator = new PDFReportGenerator("Reporte Mensual - $estado_titulo - $mes/$anio");
    
    // Preparar datos para la tabla en el formato esperado
    $tableData = [];
    $totalMonto = 0;
    
    foreach ($resultados as $r) {
        $estado_txt = $r['pagada'] ? 'Pagada' : 'Impaga';
        $tableData[] = [
            $r['nombre'],
            $r['fecha_emision'],
            '₡' . number_format($r['monto'], 2),
            $estado_txt,
            $r['descripcion']
        ];
        
        $totalMonto += $r['monto'];
    }
    
    // Agregar tabla al reporte
    $reportGenerator->addTable(
        ['Cliente', 'Fecha', 'Monto', 'Estado', 'Descripción'],
        $tableData
    );
    
    // Agregar resumen con totales
    $summaryContent = '<p>Total de facturas: ' . count($resultados) . '</p>';
    $summaryContent .= '<p>Monto total: ₡' . number_format($totalMonto, 2) . '</p>';
    
    // Contar facturas pagadas e impagas
    $pagadas = array_filter($resultados, function($item) {
        return $item['pagada'] == 1;
    });
    
    $impagas = array_filter($resultados, function($item) {
        return $item['pagada'] == 0;
    });
    
    $summaryContent .= '<p>Facturas pagadas: ' . count($pagadas) . '</p>';
    $summaryContent .= '<p>Facturas pendientes: ' . count($impagas) . '</p>';
    
    $reportGenerator->addSummary('Resumen', $summaryContent);
    
    // Generar el PDF
    $reportGenerator->output("Reporte_Mensual_{$mes}_{$anio}.pdf");
    exit;
}

// Exportar a CSV 
if (isset($_GET['exportar']) && $_GET['exportar'] == 'csv') {
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=Reporte_Mensual_{$mes}_{$anio}.csv");

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Cliente', 'Fecha', 'Monto', 'Estado', 'Descripción']);

    foreach ($resultados as $r) {
        $estado_txt = $r['pagada'] ? 'Pagada' : 'Impaga';
        fputcsv($output, [$r['nombre'], $r['fecha_emision'], $r['monto'], $estado_txt, $r['descripcion']]);
    }

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual</title>
    <link rel="stylesheet" href="../CSS/contabilidad.css">
</head>
<body>
<?php MostrarNavbar(); ?>

<style>
    .form-container {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin: 40px auto;
        width: 85%;
        max-width: 1320px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 20px;
        align-items: center;
        margin-bottom: 30px;
    }

    .form-grid label {
        color: #0b4c66;
        font-weight: 600;
    }

    .form-grid input,
    .form-grid select {
        width: 100%;
        padding: 12px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .form-grid input:focus,
    .form-grid select:focus {
        border-color: #0b4c66;
        box-shadow: 0 0 0 3px rgba(11, 76, 102, 0.1);
        outline: none;
    }

    .btn-custom {
        background-color: #0b4c66;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-custom:hover {
        background-color: #083d52;
        transform: translateY(-2px);
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
        text-align: left;
    }

    table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }

    table tr:hover {
        background-color: #f8f9fa;
    }

    .export-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin: 20px 0;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
</style>

<div class="form-container">
    <h2 style="text-align: center;">Reporte Mensual</h2>
    <form method="GET" action="" class="form-grid">
        <label for="mes_anio">Mes y Año:</label>
        <input type="month" name="mes_anio" id="mes_anio" value="<?= "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) ?>" required>

        <label for="estado">Estado:</label>
        <select name="estado" id="estado">
            <option value="todas" <?= ($estado == 'todas') ? 'selected' : '' ?>>Todas</option>
            <option value="pagadas" <?= ($estado == 'pagadas') ? 'selected' : '' ?>>Solo pagadas</option>
            <option value="impagas" <?= ($estado == 'impagas') ? 'selected' : '' ?>>Solo impagas</option>
        </select>

        <div class="form-actions">
            <a href="RPA.php" class="btn-custom">Volver</a>
            <button type="submit" class="btn-custom">Ver Reporte</button>
        </div>
    </form>
</div>

<?php if ($resultados): ?>
<div class="form-container">
    <h3 style="text-align: center;">Resultados de Facturación - <?= $mes ?>/<?= $anio ?></h3>

    <div class="export-buttons">
        <a href="?mes_anio=<?= "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) ?>&estado=<?= $estado ?>&exportar=pdf">
            <button class="btn-custom">Exportar a PDF</button>
        </a>
        <a href="?mes_anio=<?= "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) ?>&estado=<?= $estado ?>&exportar=csv">
            <button class="btn-custom">Exportar a CSV</button>
        </a>
    </div>

    <table>
        <tr>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Monto</th>
            <th>Estado</th>
            <th>Descripción</th>
        </tr>
        <?php foreach ($resultados as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['nombre']) ?></td>
                <td><?= $r['fecha_emision'] ?></td>
                <td>₡<?= number_format($r['monto'], 2) ?></td>
                <td><?= $r['pagada'] ? 'Pagada' : 'Impaga' ?></td>
                <td><?= htmlspecialchars($r['descripcion']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>
</body>
</html>