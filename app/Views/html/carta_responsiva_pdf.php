<?php
// ==============================================================================
// MAGIA BASE64 CON PROTECCIÓN ANTI-ERRORES (TRY-CATCH)
// ==============================================================================

// 1. Convertir el Logo del Gym
$logoBase64 = '';
try {
    $rutaLogo = FCPATH . 'assets/recibos/circle22.png';
    if (file_exists($rutaLogo) && is_file($rutaLogo)) {
        $tipoLogo = pathinfo($rutaLogo, PATHINFO_EXTENSION);
        $datosLogo = @file_get_contents($rutaLogo);
        if ($datosLogo !== false) {
            $logoBase64 = 'data:image/' . $tipoLogo . ';base64,' . base64_encode($datosLogo);
        }
    }
} catch (\Exception $e) {
    $logoBase64 = '';
}

// 2. Convertir la Firma del Cliente
$firmaBase64 = '';
try {
    if (!empty($cliente['Firma'])) {
        $rutaFirma = FCPATH . ltrim($cliente['Firma'], '/'); 
        if (file_exists($rutaFirma) && is_file($rutaFirma)) {
            $tipoFirma = pathinfo($rutaFirma, PATHINFO_EXTENSION);
            $datosFirma = @file_get_contents($rutaFirma);
            if ($datosFirma !== false) {
                $firmaBase64 = 'data:image/' . $tipoFirma . ';base64,' . base64_encode($datosFirma);
            }
        }
    }
} catch (\Exception $e) {
    $firmaBase64 = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Responsiva - VitalGym</title>
    <style>
        /* Configuración estricta de la hoja física para Dompdf */
        @page { margin: 40px 50px 60px 50px; }
        
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #333; 
            line-height: 1.5; 
            text-align: justify; 
        }
        
        /* Encabezado Corporativo */
        .header-table { width: 100%; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; text-transform: uppercase; color: #2c3e50; margin: 0; letter-spacing: 1px; text-align: center; }
        .subtitle { font-size: 10px; color: #7f8c8d; text-align: center; margin-top: 3px; letter-spacing: 2px; text-transform: uppercase; }
        .doc-control { font-size: 9px; color: #95a5a6; text-align: right; vertical-align: bottom; }

        /* Títulos de sección formales */
        .section-title { 
            font-size: 10px; 
            font-weight: bold; 
            background-color: #f4f6f7; 
            padding: 5px 8px; 
            margin: 15px 0 10px 0; 
            border-left: 3px solid #2c3e50; 
            text-transform: uppercase; 
            color: #2c3e50;
        }

        /* Tabla de Datos estilo Formulario Legal */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10px; }
        .data-table th, .data-table td { border: 1px solid #bdc3c7; padding: 6px 8px; vertical-align: middle; }
        .data-table th { background-color: #fcfcfc; width: 22%; text-align: left; color: #555; }
        .data-table td { color: #000; font-weight: bold; }

        /* Cuerpo del Contrato */
        .legal-text { margin-bottom: 15px; }
        .legal-text p { margin: 0 0 10px 0; }
        .legal-text ol { padding-left: 20px; margin-top: 5px; }
        .legal-text li { margin-bottom: 8px; padding-left: 5px; }
        .legal-text strong { color: #2c3e50; }

        /* Área de Firma controlada para no desbordar */
        .signature-section { margin-top: 40px; width: 100%; text-align: center; page-break-inside: avoid; }
        .signature-box { display: inline-block; width: 300px; margin: 0 auto; }
        .signature-line { border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; font-weight: bold; font-size: 11px; color: #000; text-transform: uppercase; }
        .signature-date { font-size: 9px; color: #555; margin-top: 3px; }

        /* Pie de página fijo */
        .footer { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 9px; color: #95a5a6; border-top: 1px solid #ecf0f1; padding-top: 5px; }
    </style>
</head>
<body>

    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td width="20%" style="text-align: left;">
                <?php if($logoBase64): ?>
                    <img src="<?= $logoBase64 ?>" style="width: 75px; height: auto;">
                <?php endif; ?>
            </td>
            <td width="60%">
                <h1 class="title">Contrato de Adhesión y<br>Carta Responsiva</h1>
                <p class="subtitle">VitalGym Fitness Center</p>
            </td>
            <td width="20%" class="doc-control">
                FOLIO: <strong>VG-<?= str_pad($cliente['IDClientes'], 5, '0', STR_PAD_LEFT) ?></strong><br>
                Emisión: <?= date('d/m/Y') ?>
            </td>
        </tr>
    </table>

    <div class="section-title">I. Declaraciones del Socio</div>
    
    <table class="data-table">
        <tr>
            <th>Nombre Completo:</th>
            <td colspan="3"><?= mb_strtoupper(esc($cliente['Nombre'] . ' ' . $cliente['ApellidoP'] . ' ' . $cliente['ApellidoM'])) ?></td>
        </tr>
        <tr>
            <th>Teléfono Móvil:</th>
            <td width="28%"><?= esc($cliente['Telefono']) ?: 'NO REGISTRADO' ?></td>
            <th width="22%">Correo Electrónico:</th>
            <td width="28%"><?= mb_strtoupper(esc($cliente['Correo'])) ?: 'NO REGISTRADO' ?></td>
        </tr>
    </table>

    <div class="section-title">II. Contacto de Emergencia</div>
    
    <table class="data-table">
        <tr>
            <th>Nombre del Contacto:</th>
            <td><?= mb_strtoupper(esc($cliente['Contacto_Emergencia'])) ?: 'NO REGISTRADO' ?></td>
            <th>Teléfono:</th>
            <td><?= esc($cliente['Telefono_Emergencia']) ?: 'NO REGISTRADO' ?></td>
        </tr>
    </table>

    <div class="section-title">III. Cláusulas y Liberación de Responsabilidad</div>

    <div class="legal-text">
        <p>Por medio del presente documento, acepto voluntariamente participar en las actividades físicas, uso de aparatos, pesas, instalaciones y disciplinas que ofrece <strong>VITALGYM</strong>, sujetándome a los siguientes términos:</p>
        
        <ol>
            <li><strong>ASUNCIÓN DE RIESGOS:</strong> Reconozco y entiendo plenamente que el entrenamiento físico y el uso de las instalaciones deportivas conllevan riesgos inherentes de lesiones físicas, desde leves hasta severas. Asumo de manera personal y total la responsabilidad por cualquier lesión, daño o pérdida que pueda sufrir durante mi permanencia en las instalaciones.</li>
            <li><strong>ESTADO DE SALUD:</strong> Declaro bajo protesta de decir verdad que me encuentro en óptimas condiciones físicas y de salud para realizar rutinas de ejercicio, y manifiesto expresamente que no padezco ninguna enfermedad, lesión o condición médica que me impida realizar actividad física de manera segura.</li>
            <li><strong>REGLAMENTO INTERNO:</strong> Me comprometo a respetar en todo momento el reglamento interno del establecimiento, a utilizar el equipo correctamente cuidando la integridad del mismo, y a seguir de manera estricta las indicaciones y medidas de seguridad instruidas por el personal capacitado.</li>
            <li><strong>LIBERACIÓN DE RESPONSABILIDAD:</strong> Libero de manera absoluta y definitiva a <strong>VitalGym</strong>, así como a sus propietarios, administradores, instructores y empleados, de cualquier reclamo, demanda legal, indemnización o acción civil derivada de accidentes, lesiones, afectaciones a la salud o pérdida de objetos personales suscitados dentro de las instalaciones.</li>
        </ol>
        
        <p style="margin-top: 15px; font-weight: bold; text-align: center;">
            Al plasmar mi firma en el presente documento, confirmo que he leído íntegramente, entendido y aceptado mi conformidad con las cláusulas aquí expuestas.
        </p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <?php if($firmaBase64): ?>
                <img src="<?= $firmaBase64 ?>" class="signature-img" style="height: 60px;">
            <?php else: ?>
                <div style="height: 60px;"></div> <?php endif; ?>
            
            <div class="signature-line">
                <?= mb_strtoupper(esc($cliente['Nombre'] . ' ' . $cliente['ApellidoP'] . ' ' . $cliente['ApellidoM'])) ?>
            </div>
            <div class="signature-date">
                <strong>FIRMA DE CONFORMIDAD DEL SOCIO</strong><br>
                Suscrito el día <?= date('d') ?> de <?= mes_en_espanol(date('m')) ?> del <?= date('Y') ?>
            </div>
        </div>
    </div>

    <div class="footer">
        Documento de carácter legal e interno. Expediente protegido bajo la Ley Federal de Protección de Datos Personales. - VitalGym Sistema
    </div>

</body>
</html>
<?php
// Pequeña función de apoyo para poner el mes en español en la firma
function mes_en_espanol($mes_numero) {
    $meses = ['01'=>'Enero', '02'=>'Febrero', '03'=>'Marzo', '04'=>'Abril', '05'=>'Mayo', '06'=>'Junio', '07'=>'Julio', '08'=>'Agosto', '09'=>'Septiembre', '10'=>'Octubre', '11'=>'Noviembre', '12'=>'Diciembre'];
    return $meses[$mes_numero];
}
?>