<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    /* Contenedor Principal */
    .reporte-container { 
        font-family: 'Poppins', sans-serif; 
        padding: 20px; 
        background-color: #f4f6f9; /* Un gris un poco más sólido de fondo */
        min-height: 100vh;
    }
    
    /* Filtros Elegantes */
    .filter-box { 
        background: #ffffff; 
        padding: 20px 25px; 
        border-radius: 12px; 
        border: none;
        margin-bottom: 25px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
    }
    .filter-title { margin: 0; font-weight: 700; color: #1a202c; font-size: 18px; }
    .filter-subtitle { margin: 0; font-size: 13px; color: #4a5568; font-weight: 500; }
    
    /* Tarjetas de Balance - COLORES VIBRANTES E INTENSOS */
    .card-balance { 
        border-radius: 14px; 
        border: none; 
        color: white; 
        margin-bottom: 25px; 
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        transition: transform 0.2s;
    }
    .card-balance:hover { transform: translateY(-3px); }
    
    /* Verde Esmeralda Fuerte para Caja */
    .bg-gradient-caja { background: linear-gradient(135deg, #0ba360, #138a53); }
    /* Rojo/Carmesí Intenso para Tienda */
    .bg-gradient-tienda { background: linear-gradient(135deg, #e52d27, #b31217); }
    /* Azul Marino/Nocturno Profundo para el Total */
    .bg-gradient-total { background: linear-gradient(135deg, #141e30, #243b55); }
    
    .card-label { text-transform: uppercase; font-size: 12px; letter-spacing: 1px; font-weight: 600; opacity: 0.9; margin-bottom: 5px; }
    .card-value { font-weight: 700; font-size: 28px; margin: 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); }
    
    /* Tablas Personalizadas */
    .panel-custom {
        background: #ffffff;
        border-radius: 14px;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 15px;
    }
    .table-custom { margin-bottom: 0; }
    .table-custom thead tr th { 
        background-color: #243b55; /* Encabezado de tabla oscuro y elegante */
        color: #ffffff; 
        font-weight: 600; 
        text-transform: uppercase; 
        font-size: 12px; 
        letter-spacing: 0.5px; 
        border: none;
        padding: 14px 10px;
    }
    /* Redondeo de las esquinas del encabezado de la tabla */
    .table-custom thead tr th:first-child { border-top-left-radius: 8px; }
    .table-custom thead tr th:last-child { border-top-right-radius: 8px; }

    .table-custom tbody tr td { 
        vertical-align: middle; 
        font-size: 13px; 
        color: #1a202c;
        padding: 14px 10px;
        border-top: 1px solid #e2e8f0;
        font-weight: 500;
    }
    .table-custom tbody tr:hover td { background-color: #f8fafc; }
    
    /* Badges de Métodos de Pago - COLORES SÓLIDOS Y LLAMATIVOS */
    .badge-pay {
        font-size: 11px;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        display: inline-block;
        text-align: center;
        color: white;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    /* Verde Brillante */
    .badge-efectivo { background-color: #00b09b; } 
    /* Azul Eléctrico */
    .badge-tarjeta { background-color: #2f80ed; } 
    /* Naranja/Fuego */
    .badge-transfer { background-color: #f2994a; } 

    .badge-credito { background-color: #6f42c1; }
    /* Enlace de usuario consultable */
    .user-link {
        color: #2f80ed;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: color 0.2s;
    }
    .user-link:hover { color: #141e30; text-decoration: underline; }
</style>

<div class="container-fluid reporte-container">
    
    <div class="filter-box">
        <form method="GET" action="<?= base_url('/reporterangos') ?>" id="formFecha">
            <div class="row align-items-center">
                <div class="col-md-4 col-sm-12">
                    <h4 class="filter-title"><i class="fas fa-chart-bar text-primary"></i> Reporte y Auditoría por Rangos</h4>
                    <p class="filter-subtitle">Consulta de acumulados financieros globales y desgloses por cajero.</p>
                </div>
                <div class="col-md-3 col-sm-6 mt-2">
                    <div class="input-group">
                        <span class="input-group-addon" style="background: #f8f9fa; font-weight: 500; font-size: 12px; color: #4a5568;">Desde</span>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="<?= esc($fecha_inicio) ?>" style="font-weight: 500; color: #2d3748;">
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mt-2">
                    <div class="input-group">
                        <span class="input-group-addon" style="background: #f8f9fa; font-weight: 500; font-size: 12px; color: #4a5568;">Hasta</span>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="<?= esc($fecha_fin) ?>" style="font-weight: 500; color: #2d3748;">
                    </div>
                </div>
                <div class="col-md-2 col-sm-12 mt-2">
                    <button type="submit" class="btn btn-primary btn-block" style="border-radius: 8px; font-weight: 500; background: #4b6cb7; border: none; padding: 8px 12px; box-shadow: 0 4px 6px rgba(75, 108, 183, 0.2);">
                        <i class="fas fa-search"></i> Analizar Rango
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php
    $sumMembresias = 0; $sumTienda = 0; $sumTotal = 0;
    foreach($reporte as $r) {
        $sumMembresias += $r['caja']['TotalMembresias'];
        $sumTienda += $r['tienda'];
        $sumTotal += $r['corte_total'];
    }
    ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-balance bg-gradient-caja">
                <div class="card-body" style="padding: 20px;">
                    <div class="card-label">Membresías e Inscripciones</div>
                    <div class="card-value">$<?= number_format($sumMembresias, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-balance bg-gradient-tienda">
                <div class="card-body" style="padding: 20px;">
                    <div class="card-label">Ventas de Tienda</div>
                    <div class="card-value">$<?= number_format($sumTienda, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-balance bg-gradient-total">
                <div class="card-body" style="padding: 20px;">
                    <div class="card-label">Total Neto Recaudado</div>
                    <div class="card-value">$<?= number_format($sumTotal, 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-custom">
        <div class="table-responsive">
            <table class="table table-custom">
            <thead>
        <tr>
            <th>Encargado / Cajero</th>
            <th>Sucursal</th>
            <th class="text-right">Efectivo</th>
            <th class="text-right">Tarjeta Débito</th>
            <th class="text-right" style="color: #bc92ff;">Tarjeta Crédito</th> <th class="text-right">Transferencia</th>
            <th class="text-right">Venta Tienda</th>
            <th class="text-right" style="background-color: #f7fafc; color: #4b6cb7; font-weight: 700;">Total Recaudado</th>
        </tr>
    </thead>
                <tbody>
                    <?php foreach($reporte as $item): ?>
                        <tr>
                            <td>
                                <span class="user-link btn-detalles" data-userid="<?= $item['users_id'] ?? 0 ?>" data-encargado="<?= esc($item['encargado']) ?>">
                                    <i class="fas fa-user-circle text-muted" style="margin-right: 4px;"></i> <?= esc($item['encargado']) ?>
                                </span>
                            </td>
                            <td><span class="badge" style="background-color: #edf2f7; color: #4a5568; font-weight: 500; font-size: 11px; padding: 4px 8px;"><?= esc($item['sucursal']) ?></span></td>
                            <td class="text-right text-success" style="font-weight: 500;">$<?= number_format($item['caja']['Efectivo'], 2) ?></td>
                            <td class="text-right text-primary" style="font-weight: 500;">$<?= number_format($item['caja']['Tarjeta'], 2) ?></td>
                            <td class="text-right" style="font-weight: 500; color: #bc92ff;">$<?= number_format($item['caja']['TarjetaCredito'], 2) ?></td> 
                            <td class="text-right text-warning" style="font-weight: 500;">$<?= number_format($item['caja']['Transferencia'], 2) ?></td>
                            <td class="text-right">$<?= number_format($item['tienda'], 2) ?></td>
                            <td class="text-right font-weight-bold" style="color: #4b6cb7; font-weight: 700; font-size: 14px;">$<?= number_format($item['corte_total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleTurno" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:14px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
          <div class="modal-header" style="background: linear-gradient(135deg, #141e30, #243b55); color: white; border-radius: 14px 14px 0 0; padding: 18px 25px;">
                <button type="button" class="close" data-dismiss="modal" style="color:white; opacity:1; font-size: 24px;">&times;</button>
                <h4 class="modal-title" id="modalEncargadoNombre" style="font-weight: 600; margin: 0; font-size: 16px;"></h4>
            </div>
            <div class="modal-body" style="padding: 0;">
                
                <div style="background-color: #f7fafc; padding: 12px 25px; border-bottom: 1px solid #edf2f7; display: flex; justify-content: flex-end;">
                    <button type="button" id="btnImprimirCorteCompleto" class="btn btn-info" style="border-radius: 6px; font-weight: 600; font-size: 13px; background-color: #00bcd4; border: none; padding: 6px 16px; box-shadow: 0 2px 4px rgba(0,188,212,0.3);">
                        <i class="fas fa-print"></i> Imprimir Corte Completo (Tiquetera)
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom" id="tablaDetallesTurno" style="margin-bottom:0;">
                        <thead>
                            <tr style="background:#f7fafc;">
                                <th style="padding-left:25px;">Socio</th>
                                <th>Concepto Cobrado</th>
                                <th>Método</th>
                                <th style="color: #4b6cb7; font-weight: 600;"><i class="fas fa-calendar-alt"></i> Fecha de Pago</th>
                                <th class="text-right" style="padding-right:25px;">Monto</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Almacén temporal de los datos del AJAX para el motor de impresión tiquete
    let datosOperacionesActuales = [];
    let encargadoActual = '';

    $('.btn-detalles').on('click', function() {
        let userId = $(this).data('userid');
        encargadoActual = $(this).data('encargado');
        let fechaInicio = $('#fecha_inicio').val();
        let fechaFin = $('#fecha_fin').val();
        
        if(userId == 0) return;

        $('#modalEncargadoNombre').html('<i class="fas fa-search-dollar"></i> Desglose Cronológico de Rango: ' + encargadoActual);
        $('#tablaDetallesTurno tbody').html('<tr><td colspan="5" class="text-center" style="padding: 50px;"><i class="fas fa-spinner fa-spin fa-2x text-primary" style="margin-bottom: 10px;"></i><br><span class="text-muted">Procesando rango en la base de datos...</span></td></tr>');
        $('#modalDetalleTurno').modal('show');

        $.ajax({
            url: '<?= base_url("/detallesRangoAjax") ?>',
            type: 'GET',
            data: { users_id: userId, fecha_inicio: fechaInicio, fecha_fin: fechaFin },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    datosOperacionesActuales = response.data; // Guardamos en memoria global
                    let html = '';
                    
                    if(response.data.length === 0) {
                        html = '<tr><td colspan="5" class="text-center text-muted" style="padding:40px;">No se registraron cobros de membresías en este periodo.</td></tr>';
                    } else {
                        response.data.forEach(function(item) {
                            let nombreSocio = item.Nombre + ' ' + item.ApellidoP;
                            let monto = '$' + parseFloat(item.Monto).toFixed(2);
                            
                            let claseBadge = 'badge-efectivo';
                            if(item.Tipo_Pago === 'Tarjeta') claseBadge = 'badge-tarjeta';
                            else if(item.Tipo_Pago === 'TarjetaCredito') claseBadge = 'badge-credito'; // <-- NUEVO CANDADO
                            else if(item.Tipo_Pago === 'Transferencia') claseBadge = 'badge-transfer';

                            // Formateador Estricto de Fecha Mexicana (DD/MM/YYYY)
                            let partesFH = item.Fecha_Pago.split(' ');
                            let fFormateada = partesFH[0];
                            let hFormateada = '';
                            
                            if (partesFH[0].includes('-')) {
                                let c = partesFH[0].split('-');
                                fFormateada = c[2] + '/' + c[1] + '/' + c[0];
                            }
                            if (partesFH.length === 2) {
                                let ch = partesFH[1].split(':');
                                hFormateada = ch[0] + ':' + ch[1];
                            }

                            html += `<tr>
                                <td style="padding-left: 25px; font-weight: 600; color: #2d3748;">${nombreSocio}</td>
                                <td style="color: #4a5568;">${item.Concepto}</td>
                                <td><span class="badge-pay ${claseBadge}">${item.Tipo_Pago}</span></td>
                                <td><span style="font-weight: 500; color: #2d3748;">${fFormateada}</span> <span class="text-muted small" style="font-size:11px; margin-left:4px;"><i class="far fa-clock"></i> ${hFormateada}</span></td>
                                <td class="text-right font-weight-bold" style="padding-right:25px; color:#2d3748; font-size:14px;">${monto}</td>
                            </tr>`;
                        });
                    }
                    $('#tablaDetallesTurno tbody').html(html);
                }
            }
        });
    });

    // ====================================================================
    // MOTOR DE IMPRESIÓN CONTINUA PARA TIQUETERAS TÉRMICAS (80MM)
    // ====================================================================
    $('#btnImprimirCorteCompleto').on('click', function() {
        if(datosOperacionesActuales.length === 0) {
            Swal.fire('Atención', 'No hay datos disponibles para imprimir.', 'warning');
            return;
        }

        let fInicio = $('#fecha_inicio').val();
        let fFin = $('#fecha_fin').val();
        
        // 1. Iniciar la construcción del ticket en texto puro y CSS de 80mm
        let ventanaImpresion = window.open('', '_blank', 'width=400,height=600');
        
        let htmlTicket = `
        <html>
        <head>
            <title>Corte por Rango - VitalGym</title>
            <style>
                @page { margin: 0; }
                body { 
                    font-family: 'Courier New', Courier, monospace; 
                    width: 280px; 
                    margin: 0; 
                    padding: 10px; 
                    color: #000; 
                    font-size: 12px;
                }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .linea-separadora { border-top: 1px dashed #000; margin: 8px 0; }
                .titulo { font-size: 15px; font-weight: bold; margin: 0; }
                table { width: 100%; border-collapse: collapse; font-size: 11px; }
                td { padding: 4px 0; vertical-align: top; }
                .item-socio { font-weight: bold; display: block; }
                .item-info { font-size: 10px; color: #333; }
                .gran-total { font-size: 14px; font-weight: bold; margin-top: 5px; }
            </style>
        </head>
        <body>
            <div class="text-center">
                <span class="titulo">VITAL GYM & FITNESS</span><br>
                <span>AUDITORÍA DE CAJA POR RANGO</span><br>
                <div class="linea-separadora"></div>
            </div>
            
            <div>
                <b>CAJERO:</b> ${encargadoActual.toUpperCase()}<br>
                <b>DESDE:</b> ${fInicio}<br>
                <b>HASTA:</b> ${fFin}<br>
                <b>EMISIÓN:</b> ${new Date().toLocaleString()}<br>
            </div>
            
            <div class="linea-separadora"></div>
            <div class="text-center"><b>HISTORIAL DE OPERACIONES</b></div>
            <div class="linea-separadora"></div>
            
            <table>
        `;

        let acumuladoDinero = 0;

        // 2. Recorrer dinámicamente las operaciones de la auditoría
        datosOperacionesActuales.forEach(function(item) {
            let socio = (item.Nombre + ' ' + item.ApellidoP).substring(0, 22);
            let concepto = item.Concepto.substring(0, 24);
            let montoNum = parseFloat(item.Monto);
            acumuladoDinero += montoNum;

            let partesF = item.Fecha_Pago.split(' ')[0].split('-');
            let fMx = partesF[2] + '/' + partesF[1]; // Día/Mes

            htmlTicket += `
                <tr>
                    <td>
                        <span class="item-socio">${socio}</span>
                        <span class="item-info">${concepto} (${item.Tipo_Pago})</span>
                    </td>
                    <td class="text-right" style="vertical-align:bottom; font-weight:bold;">
                        ${fMx}<br>$${montoNum.toFixed(2)}
                    </td>
                </tr>
                <tr><td colspan="2" style="border-top:1px dotted #ccc; padding:0; height:1px;"></td></tr>
            `;
        });

        // 3. Cerrar bloques y estampar el Arqueo Exacto totalizador
        htmlTicket += `
            </table>
            <div class="linea-separadora"></div>
            <div class="text-right gran-total">
                TOTAL DE CORTE: $${acumuladoDinero.toFixed(2)}
            </div>
            <div class="linea-separadora"></div>
            <div class="text-center" style="margin-top:25px;">
                * FIN DE REPORTE AUDITADO *<br>
                VitalGym Software v2.5
            </div>
            <br><br><br><br>
        </body>
        </html>
        `;

        // 4. Inyectar contenido al navegador y forzar disparo físico
        ventanaImpresion.document.write(htmlTicket);
        ventanaImpresion.document.close();
        
        // Pequeño delay de 500ms para asegurar que el navegador cargue los estilos CSS del ticket antes de imprimir
        setTimeout(function() {
            ventanaImpresion.print();
            ventanaImpresion.close();
        }, 500);
    });
});
</script>