<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket VitalGym</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            color: #000; 
            font-size: 13px;
            margin: 0;
            padding: 0;
        }
        .ticket-container { 
            width: 320px; 
            margin: 0 auto; 
            padding: 10px; 
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .logo-gym { max-width: 100px; margin-bottom: 8px; }
        .header h2 { margin: 0; font-size: 20px; font-weight: bold; }
        .info-fiscal { font-size: 11px; margin-bottom: 10px; line-height: 1.2; }
        .aviso-fiscal { font-size: 10px; font-weight: bold; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin-bottom: 10px; }
        .datos-cliente { margin-bottom: 10px; font-size: 12px; }
        .datos-cliente div { margin-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th { border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; font-weight: bold; font-size: 12px;}
        td { padding: 5px 0; vertical-align: top; font-size: 12px;}
        .item-nombre { display: block; max-width: 180px; }
        .item-vence { font-size: 10px; color: #555; display: block; padding-left: 10px; }
        .total-line { border-top: 1px dashed #000; padding-top: 8px; font-size: 16px; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 11px; }
        .quejas { font-size: 12px; font-weight: bold; margin-top: 10px; }
        .quejas span { font-size: 16px; }
    </style>
</head>
<body>

    <?php
        // Configuramos la información según la sucursal que llegue del controlador
        $sucursal_activa = $sucursal ?? 'SUCUR00001';
        if ($sucursal_activa == "SUCUR00002") {
            $direccion1 = "Camino al tequio # 307 C.P.71230";
            $direccion2 = "Santa Cruz Xoxocotlan, Oax.";
            $nombre_suc = "SUC. XOXOCOTLAN";
            $tel        = "(951) 549 - 9368";
        } else {
            $direccion1 = "Av. La paz 509 C.P. 68150";
            $direccion2 = "Col. California, Oaxaca de Juarez";
            $nombre_suc = "SUC. CENTRO";
            $tel        = "(951) 152 - 8664";
        }
        $rfc = "RFC: REAM890102V65";
    ?>

    <div class="ticket-container">
        <div class="header text-center">
        
            <h2>VITAL GYM & FITNESS</h2> 
            
            <div class="info-fiscal">
                <?= $nombre_suc ?><br>
                <?= $direccion1 ?><br>
                <?= $direccion2 ?><br>
                <?= $rfc ?><br>
                TEL: <?= $tel ?>
            </div>

            <div class="aviso-fiscal">
                REGIMEN DE INCORPORACION FISCAL<br>
                ESTE COMPROBANTE NO ES VALIDO PARA EFECTOS FISCALES
            </div>
        </div>

        <div class="datos-cliente">
            <div>FECHA: <?= date('d/m/Y H:i') ?></div>
            <div>SOCIO: <?= substr($cliente, 0, 26) ?></div> 
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-left">DESCRIPCION</th>
                    <th class="text-right">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <span class="item-nombre"><?= $membresia ?></span>
                        <span class="item-vence">(Vence: <?= $fecha_fin ?>)</span>
                    </td>
                    <td class="text-right">$<?= number_format($costo_base, 2) ?></td>
                </tr>
                
                <?php if(!empty($extras)): ?>
                    <?php foreach($extras as $extra): ?>
                    <tr>
                        <td style="padding-left: 10px;">
                            <span class="item-nombre">+ <?= $extra['nombre'] ?></span>
                        </td>
                        <td class="text-right">$<?= number_format($extra['costo'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="total-line text-right">
            TOTAL: $<?= number_format($total, 2) ?>
        </div>

        <div class="footer text-center">
            ¡GRACIAS POR TU PAGO!<br>
            Conserva este comprobante digital<br>
            
            <div class="quejas">
                QUEJAS O SUGERENCIAS WHATSAPP<br>
                <span>951-169-44-23</span>
            </div>
        </div>
    </div>
</body>
</html>