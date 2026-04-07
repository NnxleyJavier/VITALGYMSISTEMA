<?php
// Nota: Si usas Composer, asegúrate de que la ruta apunte correctamente a vendor/autoload.php
// Si ya tenías tu carpeta estructurada para requerir 'autoload.php', déjalo así.
require __DIR__ . '/autoload.php'; 

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// 1. Recibir y validar las variables enviadas por GET
$sucursal          = isset($_GET['sucursal']) ? $_GET['sucursal'] : 'SUCUR00001';
$nombre_vendedor   = isset($_GET['nombre']) ? $_GET['nombre'] : 'Cajero';
$cliente           = isset($_GET['cliente']) ? $_GET['cliente'] : 'Público en General';
$cliente_membresia = isset($_GET['cliente_membresia']) ? $_GET['cliente_membresia'] : 'N/A';
$tipo_visita       = isset($_GET['tipo_visita_membresia']) ? strtoupper($_GET['tipo_visita_membresia']) : 'DIA';
$costo_extra       = isset($_GET['costo_servicio_extra_membresia']) ? $_GET['costo_servicio_extra_membresia'] : '0.00';
$costo_membresia   = isset($_GET['costo_membresia']) ? $_GET['costo_membresia'] : '0.00';

// Nombre de la impresora compartida en Windows
$nombre_impresora = "USB22"; 

try {
    $connector = new WindowsPrintConnector($nombre_impresora);
    $printer = new Printer($connector);

    // 2. Lógica de Fechas
    $fechaInicio = date("Y-m-d");
    $fechaDateTime = new DateTime($fechaInicio);

    if ($tipo_visita == "MENSUALIDAD") {
        $fechaDateTime->modify('+1 month');
    } elseif ($tipo_visita == "QUINCENA") {
        $fechaDateTime->modify('+15 days');
    } elseif ($tipo_visita == "SEMANA") {
        $fechaDateTime->modify('+7 days');
    } elseif ($tipo_visita == "DIA") {
        $fechaDateTime->modify('+1 day');
    }
    $nuevaFecha = $fechaDateTime->format('Y-m-d');

    // 3. Encabezado y Logo
    $printer->setJustification(Printer::JUSTIFY_CENTER);

    try {
        // Asegúrate de que circle22.png esté en la misma carpeta que este archivo
        $logo = EscposImage::load("./circle22.png", false);
        $printer->bitImage($logo);
    } catch(Exception $e) { 
        /* No hacemos nada si hay error con la imagen */ 
    }

    $printer->setFont(Printer::FONT_B);
    $printer->setTextSize(1, 1);
    $printer->text("VITAL GYM & FITNESS\n");

    // 4. Datos de Sucursal
    if ($sucursal == "SUCUR00002") {
        $printer->text("Camino al tequio # 307 C.P71230\n");
        $printer->text("Santa Cruz Xoxocotlan, Oax\n");
        $printer->text("RFC. REAM890102V65\n");
        $printer->text("SUC. XOXOCOTLAN\n");
        $printer->text("TEL: (951) 549 – 9368\n");
    } elseif ($sucursal == "SUCUR00001") {
        $printer->text("Av. La paz 509 C.P 68150\n");
        $printer->text("Col. California, Oaxaca de Juarez, Oax\n");
        $printer->text("RFC. REAM890102V65\n");
        $printer->text("SUC. CENTRO\n");
        $printer->text("TEL: (951) 152 - 8664\n");
    }

    $printer->text("\nREGIMEN DE INCORPORACION FISCAL\n");
    $printer->text("ESTE COMPROBANTE NO ES VALIDO PARA EFECTOS FISCALES\n");
    $printer->text("===============================\n");

    // 5. Detalles de la compra
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->setFont(Printer::FONT_B);
    $printer->setTextSize(1, 1);

    $printer->text("Vendedor: " . $nombre_vendedor . "\n");
    $printer->text("Fecha: " . date("d-m-Y H:i:s") . "\n");
    $printer->text("Cliente: " . $cliente . "\n");
    $printer->text("Clave del cliente: " . $cliente_membresia . "\n");
    $printer->text("Concepto: " . $tipo_visita . "\n");
    $printer->text("Fecha de inicio: " . date("d-m-Y") . "\n");
    $printer->text("Fecha de finalización: " . $nuevaFecha . "\n");
    $printer->text("OTRO SERVICIO, NO CAUSA IVA: $" . number_format($costo_extra, 2) . "\n");
    $printer->text("Subtotal: $" . number_format($costo_membresia, 2) . "\n");
    $printer->text("IVA: $0.00\n"); // En tu código original decía IVA: $_GET['costo_membresia'], lo dejé a 0.00 por lógica contable, pero puedes cambiarlo si lo requieres.
    $printer->text("Descuentos:\n\n");

    // 6. Total
    $printer->setFont(Printer::FONT_B);
    $printer->setTextSize(2, 2);
    $printer->text("Total: $" . number_format($costo_membresia, 2) . "\n");

    $printer->setTextSize(1, 1);
    $printer->text("===============================\n");
            
    // 7. Pie de página y Redes/Contacto
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setFont(Printer::FONT_B);
    $printer->setTextSize(2, 2);
    $printer->text("\nQUEJAS O SUGERENCIAS WHATSAPP\n");
    $printer->text("\n951-169-44-23\n");

    try {
        // Asegúrate de que contacto.png esté en la misma carpeta
        $logoContacto = EscposImage::load("./contacto.png", false);
        $printer->bitImage($logoContacto);
    } catch(Exception $e) { 
        /* No hacemos nada si hay error */ 
    }

    // 8. Finalizar impresión
    $printer->feed(3);
    $printer->cut();
    $printer->pulse(); // Útil para abrir el cajón de dinero si tienes uno
    $printer->close();

} catch (Exception $e) {
    // Si la impresora no está conectada o hay error, puedes capturarlo aquí
    echo "No se pudo imprimir en la impresora: " . $e->getMessage();
}
?>