<?php
// Usamos el autoload del proyecto "sistema"
require __DIR__ . '/autoload.php'; 

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// 1. Captura de datos desde VitalGymSistema (Vía GET)
$sucursal          = $_GET['sucursal'] ?? 'SUCUR00001';
$vendedor          = $_GET['nombre'] ?? 'Cajero';
$cliente           = $_GET['cliente'] ?? 'Socio';
$tipo_membresia    = $_GET['tipo_visita_membresia'] ?? 'MENSUALIDAD';
$costo_membresia   = $_GET['costo_membresia'] ?? '0.00';
$cliente_id        = $_GET['cliente_membresia'] ?? '';
$fecha_vencimiento = $_GET['fecha_vencimiento'] ?? date('d/m/Y', strtotime('+1 month'));

// Nombre de tu impresora configurada en Windows
$nombre_impresora = "USB22"; 

try {
    $connector = new WindowsPrintConnector($nombre_impresora);
    $printer = new Printer($connector);

    // --- DISEÑO DEL TICKET DE RENOVACIÓN ---
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    
    // Logo (usando la ruta local de "sistema")
    try {
        $logo = EscposImage::load("./circle22.png", false);
        $printer->bitImage($logo);
    } catch(Exception $e) {}

    $printer->setTextSize(1, 1);
    $printer->text("VITAL GYM & FITNESS\n");
    $printer->text("SUC. " . ($sucursal == "SUCUR00001" ? "CENTRO" : "XOXOCOTLÁN") . "\n");
    $printer->text("===============================\n");
    
    // Título distintivo para renovaciones
    $printer->setFont(Printer::FONT_B);
    $printer->setTextSize(2, 2);
    $printer->text("RENOVACION\n");
    $printer->setTextSize(1, 1);
    $printer->text("===============================\n");

    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Atendió: $vendedor\n");
    $printer->text("Fecha de pago: " . date("d-m-Y H:i:s") . "\n");
    $printer->text("Socio: $cliente\n");
    $printer->text("ID Socio: $cliente_id\n");
    $printer->text("Servicio: $tipo_membresia\n");
    
    // Mostramos la fecha exacta de corte que mandó la BD
    $printer->text("Próximo corte: $fecha_vencimiento\n");
    $printer->text("-------------------------------\n");

    $printer->setJustification(Printer::JUSTIFY_RIGHT);
    $printer->setTextSize(2, 2);
    $printer->text("TOTAL: $" . number_format((float)$costo_membresia, 2) . "\n");

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(1, 1);
    $printer->text("\n===============================\n");
    $printer->text("¡GRACIAS POR TU PAGO!\n");
    $printer->text("Tu membresia ha sido actualizada.\n");
    
    $printer->feed(3);
    $printer->cut();
    $printer->close();

} catch (Exception $e) {
    echo "Error de impresión: " . $e->getMessage();
}