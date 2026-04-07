


<?php


require __DIR__ . '/autoload.php'; //Nota: si renombraste la carpeta a algo diferente de "ticket" cambia el nombre en esta línea
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;




$nombre_impresora = "USB22"; 
$connector = new WindowsPrintConnector($nombre_impresora);
$printer = new Printer($connector);
#Mando un numero de respuesta para saber que se conecto correctamente.

# Vamos a alinear al centro lo próximo que imprimamos
$printer->setJustification(Printer::JUSTIFY_CENTER);



// Fecha de inicio en formato 'YYYY-MM-DD HH:MM:SS'
$fechaInicio = $_GET['fecha_validez_membresia']; // Usamos el formato estándar de fecha

// Convertir a objeto DateTime
$fechaDateTime = new DateTime($fechaInicio);

// Verificar el tipo de visita y calcular la nueva fecha
if ($_GET['tipo_visita_membresia'] == "MENSUALIDAD") {
    // Sumar un mes
    $fechaDateTime->modify('+1 month');
    // Ajustar al último día del mes
    $nuevaFecha = $fechaDateTime->format('d-m-Y');
} elseif ($_GET['tipo_visita_membresia'] == "QUINCENA") {
    // Sumar 15 días
    $fechaDateTime->modify('+15 days');
    $nuevaFecha = $fechaDateTime->format('d-m-Y');
} elseif ($_GET['tipo_visita_membresia'] == "SEMANA") {
    // Sumar 7 días
    $fechaDateTime->modify('+7 days');
    $nuevaFecha = $fechaDateTime->format('d-m-Y');
} elseif ($_GET['tipo_visita_membresia'] == "DIA") {
    // Sumar 1 día
    $fechaDateTime->modify('+1 day');
    $nuevaFecha = $fechaDateTime->format('d-m-Y');
} else {
    // Si el tipo no es válido
    $nuevaFecha = "???---.";
}

$formato = $fechaDateTime->format('d m Y');

/*
	Intentaremos cargar e imprimir
	el logo
*/
try{
	$logo = EscposImage::load("./circle22.png", false);
	$printer->bitImage($logo);
}catch(Exception $e){/*No hacemos nada si hay error*/
return false ;
}

$iva = ($_GET['costo_membresia']*0.16);
$subtotal = $_GET['costo_membresia']-$iva;

$printer->setFont(Printer::FONT_B);
$printer->setTextSize(1, 1);

$printer->text("VITAL GYM & FITNESS". "\n");

if($_GET['sucursal']=="SUCUR00002"){
	
	$printer->text("Camino al tequio # 307 C.P71230". "\n");
	$printer->text("Santa Cruz Xoxocotlan ,Oax". "\n");
	$printer->text("RFC. REAM890102V65"."\n");
	$printer->text("SUC. XOXOCOTLAN" . "\n");
	$printer->text("TEL: (951) 549 – 9368" . "\n");
	
}elseif($_GET['sucursal']=="SUCUR00001"){

	$printer->text("Av. La paz 509 C.P 68150". "\n");
	$printer->text("Col. California, Oaxaca de Juarez,Oax". "\n");
	$printer->text("RFC. REAM890102V65"."\n");
	$printer->text("SUC. CENTRO" . "\n");
	$printer->text("TEL: (951) 152 - 8664" . "\n");
	
}

$printer->text("\n"."REGIMEN DE INCORPORACION FISCAL" . "\n");
$printer->text("ESTE COMPROBANTE NO ES VALIDO PARA EFECTOS FISCALES" . "\n");
$printer->text("===============================" . "\n");

/*
	Ahora vamos a imprimir los
	productos
*/
$printer->setFont(Printer::FONT_B);
$printer->setTextSize(1, 1);

$printer->text("Vendedor: " .$_GET['nombre']. "\n");

$printer->text("Fecha: " .date("d-m-Y H:i:s"). "\n");

$printer->text("Cliente: " .$_GET['cliente']. "\n");

$printer->text("Clave del cliente: " .$_GET['cliente_membresia']. "\n");

$printer->text("Concepto:".$_GET['tipo_visita_membresia']. "\n");

$printer->text("Fecha de inicio: ".$formato. "\n");

$printer->text("Fecha de finalización: ".$nuevaFecha. "\n");

$printer->text("OTRO SERVICIO, NO CAUSA IVA ".$_GET['costo_servicio_extra_membresia']. "\n");

$printer->text($_GET['costo_servicio_extra_membresia']. "\n");

$printer->text("Subtotal:".$subtotal. "\n");

$printer->text("IVA:".$iva. "\n");

$printer->text("Descuentos :". "\n");
$printer->text("". "\n");
$printer->setFont(Printer::FONT_B);
$printer->setTextSize(2, 2);

$printer->text("Total :".$_GET['costo_membresia']. "\n");

$printer->setTextSize(1, 1);
$printer->text("===============================" . "\n");
           
            $printer->setFont(Printer::FONT_B);
            $printer->setTextSize(2, 2);
            $printer->text("\n"."QUEJAS O SUGERENCIAS WHATSAPP "."\n");
			$printer->text("\n"."951-169-44-23"."\n");


			try{
				$logo = EscposImage::load("./contacto.png", false);
				$printer->bitImage($logo);
			}catch(Exception $e){/*No hacemos nada si hay error*/
			return false ;
			}
			

/*Alimentamos el papel 3 veces*/
$printer->feed(2);

/*
	Cortamos el papel. Si nuestra impresora
	no tiene soporte para ello, no generará
	ningún error
*/
$printer->cut();

/*
	Por medio de la impresora mandamos un pulso.
	Esto es útil cuando la tenemos conectada
	por ejemplo a un cajón
*/
$printer->pulse();

/*
	Para imprimir realmente, tenemos que "cerrar"
	la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
*/
$printer->close();

?>

