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
// Aquí llega automáticamente el " (P.Amigo)" desde el controlador si solicitaron descuento
$tipo_membresia    = $_GET['tipo_visita_membresia'] ?? 'MENSUALIDAD'; 
$costo_membresia   = $_GET['costo_membresia'] ?? '0.00';
$cliente_id        = $_GET['cliente_membresia'] ?? '';
// Corrección: Leemos 'fecha_fin' que es la variable exacta que manda Renovaciones.php
$fecha_vencimiento = $_GET['fecha_fin'] ?? $_GET['fecha_vencimiento'] ?? date('d/m/Y', strtotime('+1 month'));

// Nombre de tu impresora configurada en Windows
$nombre_impresora = "USB22"; 

try {
    $connector = new WindowsPrintConnector($nombre_impresora);
    $printer = new Printer($connector);

    // --- DISEÑO DEL TICKET DE RENOVACIÓN ---
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    
    // Logo (usando la ruta local de "sistema")
    try {
        $logo = EscposImage::load("logo.png", false);
        $printer->bitImage($logo);
    } catch (Exception $e) {
        /* Si no encuentra el logo, ignora el error y sigue imprimiendo el texto */
    }

    $printer->setTextSize(1, 1);
    $printer->text("VITAL GYM\n");
    $printer->text("SUC. " . ($sucursal == "SUCUR00001" ? "CENTRO" : "XOXOCOTLAN") . "\n");
    $printer->text("===============================\n");
    
    // Título distintivo para renovaciones
    $printer->setFont(Printer::FONT_B);
    $printer->setTextSize(2, 2);
    $printer->text("RENOVACION\n");
    $printer->setTextSize(1, 1);
    $printer->text("===============================\n");

    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Atendio: $vendedor\n");
    $printer->text("Fecha de pago: " . date("d-m-Y H:i:s") . "\n");
    $printer->text("Socio: $cliente\n");
    $printer->text("ID Socio: $cliente_id\n");
    
    // Aquí se imprime: "Servicio: MES" o "Servicio: MES (P.Amigo)"
    $printer->text("Servicio: $tipo_membresia\n");
    
    // Mostramos la fecha exacta de corte que mandó la BD
    $printer->text("Proximo corte: $fecha_vencimiento\n");
    $printer->text("-------------------------------\n");

    $printer->setJustification(Printer::JUSTIFY_RIGHT);
    $printer->setTextSize(2, 2);
    // El costo siempre será el de catálogo, para transparencia de la caja
    $printer->text("TOTAL: $" . number_format((float)$costo_membresia, 2) . "\n");
    $printer->setTextSize(1, 1);
    $printer->text("-------------------------------\n");

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("¡Gracias por tu preferencia!\n");
    $printer->text("Conserva este ticket para cualquier aclaracion.\n");
    $printer->feed(3);
    
    $printer->cut();
    $printer->close();
} catch (Exception $e) {
    echo "No se pudo imprimir en la impresora: " . $e->getMessage() . "\n";
}