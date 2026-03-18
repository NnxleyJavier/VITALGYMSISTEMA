<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .card-reporte { 
        font-family: 'Poppins', sans-serif;
        margin: 20px auto 40px; 
        background-color: #ffffff; 
        color: #333333; 
        padding: 30px 40px; 
        border-radius: 12px; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #17a2b8; /* Azul Info consistente con el Dashboard */
        width: 100%; 
        max-width: 1100px; 
    }
    
    .report-section-title { 
        font-size: 16px; 
        color: #17a2b8; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        font-weight: 700; 
        margin-top: 30px; 
        margin-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
    }

    .table-financiera { margin-top: 10px; }
    
    .table-financiera thead th {
        background-color: #f8f9fa;
        color: #555;
        border-bottom: 2px solid #17a2b8;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table-financiera tbody td {
        vertical-align: middle;
        font-size: 14px;
        border-top: 1px solid #eee;
        padding: 12px;
    }

    .monto-positivo { color: #28a745; font-weight: 600; font-size: 15px; }
    
    /* Estilos para las pestañas de Bootstrap */
    .nav-tabs > li > a { color: #555; font-weight: 600; border-radius: 5px 5px 0 0; }
    .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover {
        color: #17a2b8;
        font-weight: 700;
        border-top: 3px solid #17a2b8;
    }
</style>

<div class="container-fluid">
    <div class="card-reporte">
        <h3 class="text-center" style="font-weight: 800; margin-top: 0; color: #333; letter-spacing: 1px;">
            <span class="glyphicon glyphicon-stats" style="color: #17a2b8;"></span> REPORTE DE INGRESOS
        </h3>
        <p class="text-center text-muted" style="margin-bottom: 30px;">Resumen financiero de transacciones (Efectivo, Tarjeta, Transferencia)</p>
        
        <!-- Pestañas de Navegación -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#dia" aria-controls="dia" role="tab" data-toggle="tab">Por Día</a></li>
            <li role="presentation"><a href="#semana" aria-controls="semana" role="tab" data-toggle="tab">Por Semana</a></li>
            <li role="presentation"><a href="#mes" aria-controls="mes" role="tab" data-toggle="tab">Por Mes</a></li>
        </ul>

        <!-- Contenido de las Pestañas -->
        <div class="tab-content">
            
            <!-- TAB 1: POR DÍA -->
            <div role="tabpanel" class="tab-pane active" id="dia">
                <h4 class="report-section-title">Últimos 30 Días</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-financiera">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th class="text-center">Transacciones</th>
                                <th class="text-right">Total Ingresado (MXN)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($porDia)): ?>
                                <?php foreach($porDia as $d): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                                        <td class="text-center"><span class="badge"><?= $d['transacciones'] ?></span></td>
                                        <td class="text-right monto-positivo">$<?= number_format($d['total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted" style="padding: 20px;">No hay movimientos recientes.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: POR SEMANA -->
            <div role="tabpanel" class="tab-pane" id="semana">
                <h4 class="report-section-title">Resumen Semanal</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-financiera">
                        <thead>
                            <tr>
                                <th>Semana del Año</th>
                                <th class="text-center">Transacciones</th>
                                <th class="text-right">Total Ingresado (MXN)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($porSemana)): ?>
                                <?php foreach($porSemana as $s): ?>
                                    <tr>
                                        <td>
                                            Semana iniciando el <strong><?= date('d/m/Y', strtotime($s['inicio_semana'])) ?></strong>
                                        </td>
                                        <td class="text-center"><span class="badge"><?= $s['transacciones'] ?></span></td>
                                        <td class="text-right monto-positivo">$<?= number_format($s['total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted" style="padding: 20px;">No hay movimientos recientes.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: POR MES -->
            <div role="tabpanel" class="tab-pane" id="mes">
                <h4 class="report-section-title">Historial Mensual</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-financiera">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th class="text-center">Transacciones</th>
                                <th class="text-right">Total Ingresado (MXN)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($porMes)): ?>
                                <?php foreach($porMes as $m): ?>
                                    <tr>
                                        <td style="text-transform: capitalize; font-weight: 500;">
                                            <?= $m['nombre_mes'] ?>
                                        </td>
                                        <td class="text-center"><span class="badge"><?= $m['transacciones'] ?></span></td>
                                        <td class="text-right monto-positivo">$<?= number_format($m['total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted" style="padding: 20px;">No hay movimientos recientes.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- Fin .tab-content -->

        <div style="margin-top: 40px; text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
            <a href="<?= base_url('/dashboard') ?>" class="btn btn-default">
                <span class="glyphicon glyphicon-arrow-left"></span> Volver
            </a>
            <button onclick="window.print()" class="btn btn-info" style="background-color: #17a2b8; border-color: #17a2b8;">
                <span class="glyphicon glyphicon-print"></span> Imprimir
            </button>
        </div>

    </div>
</div>