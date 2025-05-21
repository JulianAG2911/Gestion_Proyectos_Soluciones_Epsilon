<?php
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

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
                overflow: hidden; /* Para contener los elementos flotantes */
                display: table; /* Usar table para mejor posicionamiento */
                clear: both; /* Asegurar que nada flote alrededor */
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
                display: table-cell; /* Usar display table-cell */
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
                display: table-cell; /* Usar display table-cell */
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
                display: table-cell; /* Usar display table-cell */
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
        $this->html .= '<p>Tel: +506 6264 6903</p>';
        $this->html .= '<p>Email: info@solucionesepsilon.com</p>';
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

    public function output($filename) {
        $this->html .= '</body></html>';
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        $this->dompdf->stream($filename, array("Attachment" => true));
    }

    public function preview() {
        $this->html .= '</body></html>';
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline');
        
        echo $this->dompdf->output();
    }
}
?>