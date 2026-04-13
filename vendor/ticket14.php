<?php
// Usamos el autoload que ya funciona en el proyecto "sistema"
require __DIR__ . '/autoload.php'; 

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// 1. Captura de datos desde VitalGymSistema (Vía GET)
$sucursal          = $_GET['sucursal'] ?? 'SUCUR00001';
$vendedor          = $_GET['nombre'] ?? 'Cajero';
$cliente           = $_GET['cliente'] ?? 'Cliente Nuevo';
$tipo_membresia    = $_GET['tipo_visita_membresia'] ?? 'MENSUALIDAD';
$costo_membresia   = $_GET['costo_membresia'] ?? '0.00';
$cliente_id        = $_GET['cliente_membresia'] ?? 'NUEVO';

// Nombre de tu impresora configurada en Windows
$nombre_impresora = "USB22"; 

try {
    $connector = new WindowsPrintConnector($nombre_impresora);
    $printer = new Printer($connector);

    // --- LÓGICA DE FECHAS ---
    $fechaDT = new DateTime();
    if (strpos($tipo_membresia, 'MENSUAL') !== false) { $fechaDT->modify('+1 month'); }
    elseif (strpos($tipo_membresia, 'QUINCE') !== false) { $fechaDT->modify('+15 days'); }
    elseif (strpos($tipo_membresia, 'SEMANA') !== false) { $fechaDT->modify('+7 days'); }
    else { $fechaDT->modify('+1 day'); }
    $vencimiento = $fechaDT->format('d-m-Y');

    // --- DISEÑO DEL TICKET ---
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

    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Vendedor: $vendedor\n");
    $printer->text("Fecha: " . date("d-m-Y H:i:s") . "\n");
    $printer->text("Cliente: $cliente\n");
    $printer->text("ID Socio: $cliente_id\n");
    $printer->text("Plan: $tipo_membresia\n");
    $printer->text("Vence: $vencimiento\n");
    $printer->text("-------------------------------\n");

    $printer->setJustification(Printer::JUSTIFY_RIGHT);
    $printer->setTextSize(2, 2);
    $printer->text("TOTAL: $" . $costo_membresia . "\n");

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(1, 1);
    $printer->text("\n===============================\n");
    $printer->text("¡GRACIAS POR TU PAGO!\n");
    
    $printer->feed(3);
    $printer->cut();
    $printer->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}