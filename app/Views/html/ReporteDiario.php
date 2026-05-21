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
</style>

<div class="container-fluid reporte-container">
    
 <div class="filter-box">
        <form method="GET" action="<?= base_url('/reportediario') ?>" id="formFecha">
            <div class="row align-items-center">
                <div class="col-md-5 col-sm-12">
                    <h4 style="margin: 0; font-weight: 600; color: #333;">
                        <i class="fas <?= $esSuperAdmin ? 'fa-chart-pie' : 'fa-cash-register' ?> text-primary"></i> 
                        <?= $esSuperAdmin ? 'Control de Cortes de Caja (General)' : 'Mi Corte de Caja (Tu Turno)' ?>
                    </h4>
                    <p class="text-muted small" style="margin: 0;">
                        <?= $esSuperAdmin ? 'Consulta los montos recolectados por turno y encargado.' : 'Resumen exacto del dinero procesado en tu turno actual.' ?>
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
                                    <td><strong style="color: #333;"><i class="fas fa-user-tie text-muted"></i> <?= esc($item['encargado']) ?></strong></td>
                                    <td><span class="badge badge-turno btn-info" style="background-color: #eef2f5; color: #495057; border: 1px solid #ced4da;"><?= esc($item['sucursal']) ?></span></td>
                                    <td class="text-center"><span class="badge label-success" style="font-size: 12px; border-radius: 4px;"><?= $item['inscripciones'] ?></span></td>
                                    <td class="text-center"><span class="badge label-warning" style="font-size: 12px; border-radius: 4px; background-color: #ff9800;"><?= $item['renovaciones'] ?></span></td>
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