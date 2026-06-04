<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    .reporte-container { font-family: 'Poppins', sans-serif; padding-top: 10px; }
    .card-balance { border-radius: 12px; border: none; color: white; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .bg-gradient-caja { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .bg-gradient-tienda { background: linear-gradient(135deg, #ff9966, #ff5e62); }
    .bg-gradient-total { background: linear-gradient(135deg, #4e54c8, #8f94fb); }
    
    .filter-box { background: #ffffff; padding: 15px 25px; border-radius: 10px; border-left: 4px solid #4e54c8; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
    .table-turnos thead { background-color: #f8f9fa; color: #4e54c8; font-weight: 600; }
    .badge-turno { font-size: 12px; padding: 6px 12px; border-radius: 20px; font-weight: 500; }

    /* Efecto hover para los botones de desglose */
    .btn-detalles { transition: all 0.2s ease-in-out; display: inline-block; }
    .btn-detalles:hover { transform: scale(1.1); filter: brightness(1.1); }
</style>

<div class="container-fluid reporte-container">
    
    <div class="filter-box">
        <form method="GET" action="<?= base_url('/reportediario') ?>" id="formFecha">
            <div class="row align-items-center">
                <div class="col-md-5 col-sm-12">
                    <h4 style="margin: 0; font-weight: 600; color: #333;">
                        <i class="fas <?= isset($esSuperAdmin) && $esSuperAdmin ? 'fa-chart-pie' : 'fa-cash-register' ?> text-primary"></i> 
                        <?= isset($esSuperAdmin) && $esSuperAdmin ? 'Control de Cortes de Caja (General)' : 'Mi Corte de Caja (Tu Turno)' ?>
                    </h4>
                    <p class="text-muted small" style="margin: 0;">
                        <?= isset($esSuperAdmin) && $esSuperAdmin ? 'Consulta los montos recolectados por turno y encargado.' : 'Resumen exacto del dinero procesado en tu turno actual.' ?>
                    </p>
                </div>
                <div class="col-md-4 col-sm-8 mt-2">
                    <div class="input-group">
                        <span class="input-group-addon" style="background: #f8f9fa;"><i class="fas fa-calendar-day"></i></span>
                        <input type="date" name="fecha" class="form-control" value="<?= esc($fecha) ?>" onchange="document.getElementById('formFecha').submit();" style="border-radius: 0 6px 6px 0; font-weight: 600;">
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 mt-2">
                    <button type="submit" class="btn btn-primary btn-block" style="border-radius: 6px; font-weight: 500;">
                        <i class="fas fa-search"></i> Filtrar Día
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php 
    // Totales globales para las tarjetas del día seleccionado
    $grandMembresias = 0; $grandTienda = 0; $grandGeneral = 0;
    foreach($reporte as $r) {
        $grandMembresias += $r['caja']['TotalMembresias'];
        $grandTienda += $r['tienda'];
        $grandGeneral += $r['corte_total'];
    }
    ?>

    <div class="row">
        <div class="col-md-4">
            <div class="panel card-balance bg-gradient-caja">
                <div class="panel-body text-center" style="padding: 20px;">
                    <h5 class="text-uppercase" style="opacity: 0.9; letter-spacing: 0.5px; font-size: 12px; font-weight: 600;">Ingresos Gym</h5>
                    <h2 style="margin: 5px 0; font-weight: 700;">$<?= number_format($grandMembresias, 2) ?></h2>
                    <p style="margin:0; font-size: 11px; opacity: 0.8;"><i class="fas fa-dumbbell"></i> Inscripciones y Renovaciones</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel card-balance bg-gradient-tienda">
                <div class="panel-body text-center" style="padding: 20px;">
                    <h5 class="text-uppercase" style="opacity: 0.9; letter-spacing: 0.5px; font-size: 12px; font-weight: 600;">Ventas Tienda</h5>
                    <h2 style="margin: 5px 0; font-weight: 700;">$<?= number_format($grandTienda, 2) ?></h2>
                    <p style="margin:0; font-size: 11px; opacity: 0.8;"><i class="fas fa-shopping-cart"></i> Suplementos y Productos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel card-balance bg-gradient-total">
                <div class="panel-body text-center" style="padding: 20px;">
                    <h5 class="text-uppercase" style="opacity: 0.9; letter-spacing: 0.5px; font-size: 12px; font-weight: 600;">Caja General Entregada</h5>
                    <h2 style="margin: 5px 0; font-weight: 700;">$<?= number_format($grandGeneral, 2) ?></h2>
                    <p style="margin:0; font-size: 11px; opacity: 0.8;"><i class="fas fa-vault"></i> Efectivo + Tarjeta + Transf.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default" style="border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden;">
        <div class="panel-heading bg-white" style="padding: 20px; border-bottom: 1px solid #f2f3f8;">
            <h4 class="panel-title text-primary" style="font-weight: 600; font-size: 16px;">
                <i class="fas fa-user-clock"></i> Desglose de Actividad por Cajero (Turnos)
            </h4>
        </div>
        <div class="panel-body" style="padding: 0;">
            <?php if(!empty($reporte)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-turnos mb-0" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Cajero Responsable</th>
                                <th>Sucursal</th>
                                <th class="text-center">Nuevos</th>
                                <th class="text-center">Renovaciones</th>
                                <th class="text-right">Efectivo Gym</th>
                                <th class="text-right">Tarjeta Gym</th>
                                <th class="text-right">Transf. Gym</th>
                                <th class="text-right">Venta Tienda</th>
                                <th class="text-right" style="background-color: #f0f4ff; color: #4e54c8; font-weight: 700;">Arqueo Exacto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reporte as $item): ?>
                                <tr>
                                    <td>
                                        <strong class="btn-detalles" data-userid="<?= $item['users_id'] ?? 0 ?>" data-fecha="<?= esc($fecha) ?>" data-tipo="todos" data-encargado="<?= esc($item['encargado']) ?>" style="color: #4e54c8; cursor: pointer; text-decoration: underline;">
                                            <i class="fas fa-user-tie text-muted"></i> <?= esc($item['encargado']) ?>
                                        </strong>
                                    </td>
                                    
                                    <td><span class="badge badge-turno btn-info" style="background-color: #eef2f5; color: #495057; border: 1px solid #ced4da;"><?= esc($item['sucursal']) ?></span></td>
                                    
                                    <td class="text-center">
                                        <?php if($item['inscripciones'] > 0): ?>
                                            <span class="badge label-success btn-detalles" data-userid="<?= $item['users_id'] ?? 0 ?>" data-fecha="<?= esc($fecha) ?>" data-tipo="nuevos" data-encargado="<?= esc($item['encargado']) ?>" style="font-size: 12px; border-radius: 4px; padding: 6px 10px; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.1); background-color: #28a745;">
                                                <i class="fas fa-user-plus"></i> <?= $item['inscripciones'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="font-size: 12px; color: #ccc;">0</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php if($item['renovaciones'] > 0): ?>
                                            <span class="badge label-warning btn-detalles" data-userid="<?= $item['users_id'] ?? 0 ?>" data-fecha="<?= esc($fecha) ?>" data-tipo="renovaciones" data-encargado="<?= esc($item['encargado']) ?>" style="font-size: 12px; border-radius: 4px; padding: 6px 10px; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.1); background-color: #ff9800;">
                                                <i class="fas fa-sync-alt"></i> <?= $item['renovaciones'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="font-size: 12px; color: #ccc;">0</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-right">$<?= number_format($item['caja']['Efectivo'], 2) ?></td>
                                    <td class="text-right">$<?= number_format($item['caja']['Tarjeta'], 2) ?></td>
                                    <td class="text-right">$<?= number_format($item['caja']['Transferencia'], 2) ?></td>
                                    <td class="text-right" style="color: #e67e22; font-weight: 500;">$<?= number_format($item['tienda'], 2) ?></td>
                                    <td class="text-right" style="background-color: #fafbff; font-weight: 700; color: #4e54c8; font-size: 14px;">
                                        $<?= number_format($item['corte_total'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center" style="padding: 40px; color: #999;">
                    <i class="fas fa-box-open" style="font-size: 40px; margin-bottom: 15px; color: #ccc;"></i>
                    <p style="margin: 0;">No hay movimientos de caja ni ventas registradas para este día.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleTurno" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius: 10px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
      <div class="modal-header" style="background: linear-gradient(135deg, #4e54c8, #8f94fb); color: white; border-radius: 10px 10px 0 0; padding: 15px 25px;">
        <h5 class="modal-title" id="modalLabel" style="font-size: 16px; margin: 0;">
            <i class="fas fa-receipt"></i> <span id="modalEncargadoNombre" style="font-weight: 700;"></span>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color: white; opacity: 1; text-shadow: none; margin-top: -5px;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="tablaDetallesTurno">
               <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th style="padding-left: 25px;">Socio Atendido</th>
                        <th>Concepto Registrado</th>
                        <th>Método</th>
                        <th>Hora</th>
                        <th class="text-right">Monto</th>
                        <th class="text-center" style="padding-right: 25px;">Acción</th> 
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer" style="background-color: #f8f9fa; border-top: 1px solid #eee;">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 6px;">Cerrar Detalles</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    
    $('.btn-detalles').on('click', function() {
        let userId = $(this).data('userid');
        let fecha = $(this).data('fecha');
        let tipoClic = $(this).data('tipo'); // 'todos', 'nuevos', o 'renovaciones'
        let encargado = $(this).data('encargado');
        
        // Evitar buscar si el empleado no tiene un ID válido (ej. solo vendió tienda y no tiene perfil en gymnasios)
        if(userId == 0) return;

        // Ajustamos el título de la ventana según qué botón oprimió el usuario
        let tituloModal = '';
        if (tipoClic === 'nuevos') tituloModal = 'Nuevas Inscripciones de: ' + encargado;
        else if (tipoClic === 'renovaciones') tituloModal = 'Renovaciones cobradas por: ' + encargado;
        else tituloModal = 'Historial completo de: ' + encargado;

        $('#modalEncargadoNombre').text(tituloModal);
        
        // Pantalla de carga mientras va al servidor
        $('#tablaDetallesTurno tbody').html('<tr><td colspan="6" class="text-center" style="padding: 40px;"><i class="fas fa-circle-notch fa-spin fa-2x text-primary" style="margin-bottom:10px;"></i><br><strong class="text-muted">Extrayendo datos de caja...</strong></td></tr>');
        
        $('#modalDetalleTurno').modal('show');

        // Solicitud AJAX al servidor (Usa la ruta que creamos anteriormente)
        $.ajax({
            url: '<?= base_url("/detallesTurnoAjax") ?>',
            type: 'GET',
            data: { users_id: userId, fecha: fecha },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    
                    let datosBrutos = response.data;
                    let datosFiltrados = [];

                    // FILTRADO INTELIGENTE EN JAVASCRIPT
                    if (tipoClic === 'nuevos') {
                        // Busca los tickets que lleven la palabra "inscrip" en el concepto
                        datosFiltrados = datosBrutos.filter(item => item.Concepto.toLowerCase().includes('inscrip'));
                    } else if (tipoClic === 'renovaciones') {
                        // Busca los tickets que NO lleven la palabra "inscrip"
                        datosFiltrados = datosBrutos.filter(item => !item.Concepto.toLowerCase().includes('inscrip'));
                    } else {
                        // Mostrar absolutamente todo (Clic en el nombre del cajero)
                        datosFiltrados = datosBrutos;
                    }

                    let html = '';
                    
                    if (datosFiltrados.length > 0) {
                        $.each(datosFiltrados, function(i, item) {
                            
                           let nombreCompleto = item.Nombre + ' ' + item.ApellidoP + ' ' + (item.ApellidoM ? item.ApellidoM : '');
                            let horaReal = new Date(item.Fecha_Pago).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', hour12: true });
                            let montoFinal = parseFloat(item.Monto).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });

                            let badgeMetodo = 'badge-secondary';
                            if(item.Tipo_Pago === 'Efectivo') badgeMetodo = 'badge-success';
                            if(item.Tipo_Pago === 'Tarjeta' || item.Tipo_Pago === 'TarjetaCredito') badgeMetodo = 'badge-info';
                            if(item.Tipo_Pago === 'Transferencia') badgeMetodo = 'badge-warning';

                            // --- INICIO DE LO NUEVO: LÓGICA DEL BOTÓN WHATSAPP ---
                            let btnWhatsApp = '<span class="text-muted" style="font-size:11px;"><i class="fas fa-phone-slash"></i> Sin Tel.</span>';
                            
                            let telefonoLimpio = item.Telefono ? item.Telefono.replace(/[^0-9]/g, '') : '';
                            if (telefonoLimpio.length === 10) {
                                telefonoLimpio = '52' + telefonoLimpio; // Código de México
                            }

                            if (telefonoLimpio !== '') {
                                // Extrae solo la fecha ("YYYY-MM-DD")
                                let fechaRecibo = item.Fecha_Pago.split(' ')[0];
                                let enlaceRecibo = '<?= base_url("assets/recibos/Recibo_") ?>' + item.Clientes_IDClientes + '_' + fechaRecibo + '.pdf';
                                
                                let mensaje = "¡Hola " + item.Nombre + "! 🏋️‍♂️\n\nAquí tienes el enlace para descargar la copia de tu recibo de pago:\n" + enlaceRecibo;
                                let urlWA = "https://api.whatsapp.com/send?phone=" + telefonoLimpio + "&text=" + encodeURIComponent(mensaje);
                                
                                btnWhatsApp = `<a href="${urlWA}" target="_blank" class="btn btn-success btn-sm" style="border-radius:20px; padding: 2px 10px; font-size: 11px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="fab fa-whatsapp"></i> Reenviar</a>`;
                            }
                            // --- FIN DE LO NUEVO ---

                            // --- ARMADO DE LA FILA (SE INCLUYE EL btnWhatsApp AL FINAL) ---
                            html += `<tr>
                                <td style="padding-left: 25px;"><strong style="color:#4e54c8;">${nombreCompleto}</strong></td>
                                <td style="font-size: 13px; color: #555;">${item.Concepto}</td>
                                <td><span class="badge ${badgeMetodo}" style="font-size:11px; padding: 5px 8px;">${item.Tipo_Pago}</span></td>
                                <td class="text-muted" style="font-size:13px;"><i class="far fa-clock"></i> ${horaReal}</td>
                                <td class="text-right" style="font-weight:700; color:#333;">${montoFinal}</td>
                                <td class="text-center" style="padding-right: 25px;">${btnWhatsApp}</td>
                            </tr>`;

                        });
                    } else {
                        html = '<tr><td colspan="6" class="text-center text-muted" style="padding: 30px;"><i class="fas fa-folder-open fa-2x mb-2" style="color:#ddd;"></i><br>No hay registros de <b>' + (tipoClic === 'nuevos' ? 'nuevas inscripciones' : 'renovaciones') + '</b> para mostrar.</td></tr>';
                    }
                    
                    $('#tablaDetallesTurno tbody').html(html);
                } else {
                    $('#tablaDetallesTurno tbody').html('<tr><td colspan="6" class="text-center text-danger" style="padding: 30px;"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Error al extraer los datos financieros.</td></tr>');
                }
            },
            error: function() {
                $('#tablaDetallesTurno tbody').html('<tr><td colspan="6" class="text-center text-danger" style="padding: 30px;"><i class="fas fa-wifi fa-2x mb-2"></i><br>Error de red al comunicarse con el servidor.</td></tr>');
            }
        });
    });
});
</script>