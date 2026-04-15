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
$cliente_id        = $_GET['cliente_membresia'] ?? 'NUEVO';
$tipo_membresia    = $_GET['tipo_visita_membresia'] ?? 'MENSUALIDAD';
$total             = $_GET['costo_membresia'] ?? '0.00';

$vencimiento       = $_GET['fecha_fin'] ?? date('d/m/Y');
$costo_base        = $_GET['costo_base'] ?? $total; 
$extras_json       = $_GET['extras'] ?? '[]';
$extras            = json_decode($extras_json, true);

// 2. CONFIGURACIÓN DE SUCURSALES
if ($sucursal == "SUCUR00002") {
    $direccion1 = "Camino al tequio # 307 C.P.71230";
    $direccion2 = "Santa Cruz Xoxocotlan, Oax.";
    $nombre_suc = "SUC. XOXOCOTLAN";
    $telefono   = "TEL: (951) 549 - 9368";
} else {
    // Por defecto CENTRO
    $direccion1 = "Av. La paz 509 C.P. 68150";
    $direccion2 = "Col. California, Oaxaca de Juarez";
    $nombre_suc = "SUC. CENTRO";
    $telefono   = "TEL: (951) 152 - 8664";
}
$rfc = "RFC: REAM890102V65";

$nombre_impresora = "USB22"; 

try {
    $connector = new WindowsPrintConnector($nombre_impresora);
    $printer = new Printer($connector);

    // --- ENCABEZADO ---
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    
    try {
        $logo = EscposImage::load("./circle22.png", false);
        $printer->bitImage($logo);
    } catch(Exception $e) {}

    $printer->setTextSize(1, 1);
    $printer->text("VITAL GYM & FITNESS\n");
    $printer->text($nombre_suc . "\n");
    $printer->text($direccion1 . "\n");
    $printer->text($direccion2 . "\n");
    $printer->text($rfc . "\n");
    $printer->text($telefono . "\n");
    $printer->text("\nREGIMEN DE INCORPORACION FISCAL\n");
    $printer->text("ESTE COMPROBANTE NO ES VALIDO\n");
    $printer->text("PARA EFECTOS FISCALES\n");
    $printer->text("================================\n"); 

    // --- DATOS GENERALES ---
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Fecha: " . date("d-m-Y H:i:s") . "\n");
    $printer->text("Cajero: $vendedor\n");
    $printer->text("Socio: #$cliente_id - $cliente\n");
    $printer->text("--------------------------------\n");
    
    // --- PRODUCTOS ---
    $printer->setEmphasis(true);
    $printer->text(sprintf("%-19s %12s\n", "DESCRIPCION", "IMPORTE"));
    $printer->setEmphasis(false);
    $printer->text("--------------------------------\n");

    $nombre_mem = substr($tipo_membresia, 0, 19);
    $printer->text(sprintf("%-19s $%11.2f\n", $nombre_mem, (float)$costo_base));
    $printer->text("  (Vence: $vencimiento)\n"); 

    if (!empty($extras) && is_array($extras)) {
        foreach ($extras as $ext) {
            $nombre_ext = substr("+ " . $ext['nombre'], 0, 19);
            $printer->text(sprintf("%-19s $%11.2f\n", $nombre_ext, (float)$ext['costo']));
        }
    }
    
    $printer->text("--------------------------------\n");

    // --- TOTAL ---
    $printer->setJustification(Printer::JUSTIFY_RIGHT);
    $printer->setEmphasis(true);
    $printer->setTextSize(2, 2);
    $printer->text("TOTAL: $" . number_format((float)$total, 2) . "\n");
    $printer->setEmphasis(false);

    // --- PIE DE TICKET (QUEJAS) ---
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(1, 1);
    $printer->text("\n================================\n");
    $printer->text("¡GRACIAS POR TU PAGO!\n\n");
    
    $printer->setEmphasis(true);
    $printer->text("QUEJAS O SUGERENCIAS WHATSAPP\n");
    $printer->setTextSize(2, 2);
    $printer->text("951-169-44-23\n");
    $printer->setTextSize(1, 1);
    $printer->setEmphasis(false);

    try {
        $logoContacto = EscposImage::load("./contacto.png", false);
        $printer->bitImage($logoContacto);
    } catch(Exception $e) {}
    
    $printer->feed(3);
    $printer->cut();
    $printer->pulse();
    $printer->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}