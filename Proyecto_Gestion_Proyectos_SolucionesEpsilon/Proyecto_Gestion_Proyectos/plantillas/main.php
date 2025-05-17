<?php
session_start(); // Iniciar sesión
require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/../plantilla.php';
// Carpeta donde están las plantillas (dentro de Proyecto_Gestion_Proyectos)
$carpeta = __DIR__ . '/';
$carpeta_thumbnails = '../../IMG/'; // Ruta hacia la carpeta IMG que está a nivel general

// Escanear todos los archivos en la carpeta
$archivos = array_diff(scandir($carpeta), array('..', '.', 'main.php')); // Ignora ".", ".." y este archivo

// Filtrar solo archivos que sigan el patrón plantillaX.html
$plantillas = [];
foreach ($archivos as $archivo) {
    if (preg_match('/^plantilla\d+\.html$/', $archivo)) {
        $plantillas[] = $archivo;
    }
}

// Ordenar las plantillas por número
usort($plantillas, function($a, $b) {
    preg_match('/\d+/', $a, $numA);
    preg_match('/\d+/', $b, $numB);
    return $numA[0] - $numB[0];
});

// Verificar si se seleccionó una plantilla
if (isset($_GET['plantilla'])) {
    $archivo = basename($_GET['plantilla']); // Seguridad: evita rutas externas
    $ruta = $carpeta . $archivo;

    if (file_exists($ruta)) {
        // Mostrar la plantilla seleccionada
        echo file_get_contents($ruta);
        echo '<p style="text-align:center; margin: 20px;">
                <a href="main.php" style="padding:10px 20px; background:#007BFF; color:white; text-decoration:none; border-radius:5px;">
                    Volver al menú de plantillas
                </a>
              </p>';
        exit;
    } else {
        echo "<h2>La plantilla no existe</h2>";
        echo '<p><a href="main.php">Volver</a></p>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Selector de Plantillas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../CSS/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600&family=Roboto+Slab:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .contenedor {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .tarjeta {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .tarjeta:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .tarjeta img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .tarjeta:hover img {
            transform: scale(1.05);
        }

        .tarjeta h3 {
            color: #0b4c66;
            margin: 15px 0;
            font-size: 1.5rem;
            font-weight: 600;
            font-family: 'Roboto Slab', serif;
        }

        .botones-container {
            display: flex;
            gap: 10px;
            padding: 10px;
        }

        .btn-ver, .btn-descargar {
            flex: 1;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-ver i, .btn-descargar i {
            font-size: 1.2rem;
            margin-bottom: 4px;
        }

        .btn-ver {
            background: #0b4c66;
            color: white;
        }

        .btn-descargar {
            background: #2ecc71;
            color: white;
        }

        .btn-ver:hover, .btn-descargar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-ver:hover {
            background: #083d52;
        }

        .btn-descargar:hover {
            background: #27ae60;
        }

        .encabezado-plantillas {
            background: linear-gradient(135deg, #0b4c66, #083d52);
            color: white;
            padding: 40px 20px;
            margin-bottom: 30px;
            text-align: center;
            border-radius: 0 0 50px 50px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .encabezado-plantillas h2 {
            font-size: 2.5rem;
            margin: 0;
            font-family: 'Roboto Slab', serif;
        }

        .encabezado-plantillas p {
            font-size: 1.1rem;
            margin-top: 10px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <?php MostrarNavbar(); ?>
    
    <div class="container mt-4">
        <h2 class="text-white p-3 rounded" style="background-color: #0b4c66; text-align: center;">Plantillas Disponibles</h2>
    </div>

    <div class="contenedor">
        <?php foreach ($plantillas as $archivo): ?>
            <?php
                $numero = preg_replace('/[^0-9]/', '', $archivo);
                $thumbnail = $carpeta_thumbnails . 'plantilla' . $numero . '.jpg';

                if (!file_exists(__DIR__ . '/../../IMG/plantilla' . $numero . '.jpg')) {
                    $thumbnail = 'https://via.placeholder.com/300x200.png?text=Vista+Previa+No+Disponible';
                }
            ?>
            <div class="tarjeta">
                <img src="<?= $thumbnail ?>" alt="Vista previa de Plantilla <?= $numero ?>">
                <h3>Plantilla <?= $numero ?></h3>
                <div class="botones-container">
                    <a href="main.php?plantilla=<?= urlencode($archivo) ?>" class="btn-ver">
                        <i class="fas fa-eye"></i>
                        <span>Ver</span>
                    </a>
                    <a href="descargar.php?archivo=<?= urlencode($archivo) ?>" class="btn-descargar">
                        <i class="fas fa-download"></i>
                        <span>Descargar</span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
