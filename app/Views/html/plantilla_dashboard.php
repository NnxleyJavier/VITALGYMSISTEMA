<?php
// Solo dejamos las fechas dinámicas, las demás variables ya vienen del Controlador
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'esp');
$fechaHoy = strftime("%d %b %Y"); 
$mesActual = ucfirst(strftime("%b %Y")); 
?>

<style>
    /* Estilos Generales Principales */
    .content-vital { display: block; padding: 25px; background-color: #f2f3f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .vital-portlet-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e2e5ec; }
    .vital-portlet-title { font-size: 1.5rem; font-weight: 600; color: #48465b; margin: 0; }
    
    /* Tarjetas de Imagen (Ingresos Principales) */
    .custom-bg-card { border-radius: 12px; min-height: 180px; background-size: cover; background-position: center; position: relative; display: flex; align-items: center; justify-content: center; box-shadow: 0px 4px 15px rgba(0,0,0,0.1); color: #ffffff; margin-bottom: 25px; transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .custom-bg-card:hover { transform: translateY(-5px); box-shadow: 0px 8px 25px rgba(0,0,0,0.2); }
    .custom-bg-title { position: absolute; top: 20px; left: 20px; font-size: 1.2rem; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; text-shadow: 2px 2px 8px rgba(0,0,0,0.8); }
    .custom-bg-amount { font-size: 3rem; font-weight: 800; margin: 0; text-shadow: 3px 3px 10px rgba(0,0,0,0.8); }
    
    /* Panel Blanco (Desglose Ingresos) - NUEVOS ESTILOS MEJORADOS */
    .info-row-white { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); height: 100%; }
   /* Separador vertical entre columnas (Más visible) */
    .info-item { 
        border-right: 2px dashed #d1d5e4; /* Línea más gruesa y oscura */
        padding: 20px 30px; 
    }

    .info-item:last-child { border-right: none; }
    
    .widget-title { font-size: 1.4rem; font-weight: 700; color: #48465b; margin-bottom: 5px; }
    /* Descripciones estilo reporte (Mayúsculas pequeñas y espaciadas) */
    .widget-desc { 
        font-size: 0.85rem; 
        color: #888ea8; 
        display: block; 
        margin-bottom: 20px; 
        font-weight: 700; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
    }
    .widget-number { font-size: 2.5rem; font-weight: 800; display: block; margin-bottom: 25px; }
    
   /* Separadores horizontales (Sub-widgets) más visibles */
    .sub-widget { 
        border-top: 2px dashed #e0e6ed; /* Línea más marcada */
        padding-top: 18px; 
        margin-top: 18px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
    }
    .sub-widget-info h6 { margin: 0; font-size: 1.15rem; color: #3b3f5c; font-weight: 800; }
    .sub-widget-info small { color: #888ea8; font-size: 0.9rem; font-weight: 600; }
    .sub-widget-val { font-weight: 800; font-size: 1.35rem; color: #2c304d; }

    /* Barra Inferior (Métodos de Pago) - NUEVOS ESTILOS */
    .payment-bar { background: #ffffff; border-radius: 12px; padding: 25px; margin-top: 15px; margin-bottom: 35px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
    .payment-label { display: block; color: #8e96b8; font-size: 1rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px; }
    .payment-value { font-weight: 800; font-size: 1.8rem; }
    .border-right-dashed { border-right: 1px dashed #e2e5ec; }

    /* Widgets de Membresías (Métricas Verticales) */
    .side-widget { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden; height: 100%; display: flex; flex-direction: column; margin-bottom: 25px; }
    .side-widget-header { position: relative; padding: 25px 20px; text-align: center; background-size: cover; background-position: center; color: white; }
    .side-widget-header h3 { margin: 0; font-size: 1.2rem; font-weight: 600; letter-spacing: 1px; text-shadow: 2px 2px 6px rgba(0,0,0,0.8); }
    .side-widget-total { font-size: 3rem; font-weight: 800; margin-top: 5px; display: block; text-shadow: 2px 2px 8px rgba(0,0,0,0.8); }
    
    .header-bg-success { background-image: url('./assets/media/bg/bg-5.jpg'); } 
    .header-bg-danger { background-image: url('./assets/media/bg/bg-7.jpg'); } 
    .header-bg-info { background-image: url('./assets/media/bg/bg-1.jpg'); } 
    
    .side-widget-body { padding: 15px 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; }
    .branch-stat-item { display: flex; align-items: center; padding: 12px 0; border-bottom: 1px dashed #e2e5ec; }
    .branch-stat-item:last-child { border-bottom: none; }
    .branch-stat-icon { background-color: #f2f3f8; color: #595d6e; font-weight: 800; font-size: 1.1rem; height: 40px; width: 55px; display: flex; align-items: center; justify-content: center; border-radius: 8px; margin-right: 15px; }
    .branch-stat-text { font-weight: 600; color: #48465b; font-size: 1rem; }

    /* Footer */
    .custom-footer { padding: 20px; background: transparent; border-top: 1px solid #e2e5ec; display: flex; justify-content: space-between; align-items: center; font-size: 1rem; color: #74788d; margin-top: 30px; }

    /* Responsividad Móvil */
    @media (max-width: 768px) { 
        .info-item { border-right: none; border-bottom: 1px solid #f0f3ff; margin-bottom: 15px; } 
        .info-item:last-child { border-bottom: none; } 
        .custom-bg-amount { font-size: 2.5rem; } 
        .vital-portlet-head { flex-direction: column; align-items: flex-start; } 
        .border-right-dashed { border-right: none; border-bottom: 1px dashed #e2e5ec; padding-bottom: 15px; margin-bottom: 15px; }
    }
</style>

<div class="content-vital">
    
    <div class="vital-portlet-head">
        <h3 class="vital-portlet-title">Ingresos en <?= $fechaHoy ?> (Día Actual)</h3>
    </div>

    <div class="row">
        <div class="col-12 col-md-4">
            <div class="custom-bg-card" style="background-image: url('./assets/media/bg/450.jpg');">
                <h4 class="custom-bg-title">Membresías</h4>
                <h3 class="custom-bg-amount">$<?= number_format($ingresosHoy['membresias'], 2) ?></h3>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="custom-bg-card" style="background-image: url('./assets/media/bg/bg-1.jpg');">
                <h4 class="custom-bg-title">Tienda</h4>
                <h3 class="custom-bg-amount">$<?= number_format($ingresosHoy['tienda'], 2) ?></h3>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="custom-bg-card" style="background-image: url('./assets/media/bg/bg-5.jpg');">
                <h4 class="custom-bg-title">Total General</h4>
                <h3 class="custom-bg-amount">$<?= number_format($ingresosHoy['total'], 2) ?></h3>
            </div>
        </div>
    </div>

    <div class="info-row-white" style="margin-bottom: 0;"> 
        <div class="row">
            <div class="col-12 col-md-6 info-item">
                <h3 class="widget-title">Ingreso Membresías</h3>
                <span class="widget-desc">Recaudación de todas las Sucursales</span>
                <span class="widget-number text-info">$<?= number_format($ingresosHoy['membresias'], 2) ?></span>
                
                <div class="sub-widget" style="border-top: 3px solid #0000002c; padding-top: 20px; margin-top: auto;">
                    <div class="sub-widget-info">
                        <h6>MATRIZ VITAL GYM</h6>
                        <small>Acceso principal</small>
                    </div>
                    <span class="sub-widget-val">$<?= number_format($ingresosHoy['membresias_matriz'], 2) ?></span> 
                </div>
                
                <div class="sub-widget ">
                    <div class="sub-widget-info">
                        <h6>VITAL GYM SUC XOXO</h6>
                        <small>Acceso sucursal</small>
                    </div>
                    <span class="sub-widget-val">$<?= number_format($ingresosHoy['membresias_xoxo'], 2) ?></span>
                </div>
            </div>

            <div class="col-12 col-md-6 info-item" style="border-right: none; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="margin-bottom: 20px;">
                    <h3 class="widget-title">Ingreso Tienda</h3>
                    <span class="widget-desc">Venta global de productos</span>
                    <span class="widget-number text-warning">$<?= number_format($ingresosHoy['tienda'], 2) ?></span>
                </div>

                <div style="border-top: 3px solid #0000002c; padding-top: 20px; margin-top: auto;">
                    <h3 class="widget-title">Ingreso Total General</h3>
                    <span class="widget-desc">Suma de Membresías + Tienda</span>
                    <span class="widget-number text-success" style="margin-bottom: 0;">$<?= number_format($ingresosHoy['total'], 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="payment-bar">
        <div class="row text-center">
            <div class="col-12 col-md-4 border-right-dashed">
                <span class="payment-label">EFECTIVO (HOY)</span>
                <span class="payment-value" style="color:#1dc9b7;">$<?= number_format($metodosHoy['efectivo'], 2) ?></span>
            </div>
            <div class="col-12 col-md-4 border-right-dashed">
                <span class="payment-label">TARJETA (HOY)</span>
                <span class="payment-value" style="color:#5d78ff;">$<?= number_format($metodosHoy['tarjeta'], 2) ?></span>
            </div>
            <div class="col-12 col-md-4">
                <span class="payment-label">TRANSFERENCIA (HOY)</span>
                <span class="payment-value" style="color:#ffb822;">$<?= number_format($metodosHoy['transferencia'], 2) ?></span>
            </div>
        </div>
    </div>


    <div class="vital-portlet-head mt-4">
        <h3 class="vital-portlet-title">Resumen Económico de <?= $mesActual ?></h3>
    </div>

    <div class="row">
        <div class="col-12 col-md-4">
            <div class="custom-bg-card" style="background-image: url('./assets/media/bg/bg-7.jpg');">
                <h4 class="custom-bg-title">Membresías Mes</h4>
                <h3 class="custom-bg-amount">$<?= number_format($ingresosMes['membresias'], 2) ?></h3>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="custom-bg-card" style="background-image: url('./assets/media/bg/450.jpg');">
                <h4 class="custom-bg-title">Tienda Mes</h4>
                <h3 class="custom-bg-amount">$<?= number_format($ingresosMes['tienda'], 2) ?></h3>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="custom-bg-card" style="background-image: url('./assets/media/bg/bg-1.jpg');">
                <h4 class="custom-bg-title">Total General Mes</h4>
                <h3 class="custom-bg-amount">$<?= number_format($ingresosMes['total'], 2) ?></h3>
            </div>
        </div>
    </div>

    



    <div class="info-row-white" style="margin-bottom: 0;"> 
        <div class="row">
            
            <div class="col-12 col-md-6 info-item">
                <h3 class="widget-title">Ingreso Membresías</h3>
                <span class="widget-desc">Recaudación de todas las Sucursales</span>
                <span class="widget-number text-info">$<?= number_format($ingresosMes['membresias'], 2) ?></span>
                
                <div class="sub-widget" style="border-top: 3px solid #0000002c; padding-top: 20px; margin-top: auto;">
                    <div class="sub-widget-info">
                        <h6>MATRIZ VITAL GYM</h6>
                        <small>Acceso principal</small>
                    </div>
                    <span class="sub-widget-val">$<?= number_format($ingresosMes['membresias_matriz'], 2) ?></span> 
                </div>
                
                <div class="sub-widget">
                    <div class="sub-widget-info">
                        <h6>VITAL GYM SUC XOXO</h6>
                        <small>Acceso sucursal</small>
                    </div>
                    <span class="sub-widget-val">$<?= number_format($ingresosMes['membresias_xoxo'], 2) ?></span>
                </div>
            </div>

            <div class="col-12 col-md-6 info-item" style="border-right: none; display: flex; flex-direction: column; justify-content: space-between;">
                
                <div style="margin-bottom: 25px;">
                    <h3 class="widget-title">Ingreso Tienda</h3>
                    <span class="widget-desc">Venta global de productos</span>
                    <span class="widget-number text-warning">$<?= number_format($ingresosMes['tienda'], 2) ?></span>
                </div>

                <div style="border-top: 3px solid #0000002c; padding-top: 25px; margin-top: auto;">
                    <h3 class="widget-title" style="font-size: 1.6rem;">Ingreso Total General</h3>
                    <span class="widget-desc">Suma de Membresías + Tienda</span>
                    <span class="widget-number text-success" style="margin-bottom: 0; font-size: 2.8rem;">$<?= number_format($ingresosMes['total'], 2) ?></span>
                </div>
                
            </div>
            
        </div>
    </div>



    <div class="payment-bar">
        <div class="row text-center">
            <div class="col-12 col-md-4 border-right-dashed">
                <span class="payment-label">EFECTIVO (MES)</span>
                <span class="payment-value" style="color:#1dc9b7;">$<?= number_format($metodosMes['efectivo'], 2) ?></span>
            </div>
            <div class="col-12 col-md-4 border-right-dashed">
                <span class="payment-label">TARJETA (MES)</span>
                <span class="payment-value" style="color:#5d78ff;">$<?= number_format($metodosMes['tarjeta'], 2) ?></span>
            </div>
            <div class="col-12 col-md-4">
                <span class="payment-label">TRANSFERENCIA (MES)</span>
                <span class="payment-value" style="color:#ffb822;">$<?= number_format($metodosMes['transferencia'], 2) ?></span>
            </div>
        </div>
    </div>


    <div class="vital-portlet-head mt-4">
        <h3 class="vital-portlet-title">Métricas de Membresías (<?= $mesActual ?>)</h3>
    </div>

    <div class="row">
        
        <div class="col-12 col-md-6 col-xl-4">
            <div class="side-widget">
                <div class="side-widget-header header-bg-info">
                    <h3>ACTIVAS</h3>
                    <span class="side-widget-total"><?= $counters['activos']['total'] ?></span>
                </div>
                <div class="side-widget-body">
                    <div class="branch-stat-item">
                        <div class="branch-stat-icon"><?= $counters['activos']['matriz'] ?></div>
                        <div class="branch-stat-text">MATRIZ VITAL GYM</div>
                    </div>
                    <div class="branch-stat-item">
                        <div class="branch-stat-icon"><?= $counters['activos']['xoxo'] ?></div>
                        <div class="branch-stat-text">VITAL GYM SUC XOXO</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="side-widget">
                <div class="side-widget-header header-bg-success">
                    <h3>NUEVAS MEMBRESÍAS</h3>
                    <span class="side-widget-total"><?= $counters['nuevas']['total'] ?></span>
                </div>
                <div class="side-widget-body">
                    <div class="branch-stat-item">
                        <div class="branch-stat-icon"><?= $counters['nuevas']['matriz'] ?></div>
                        <div class="branch-stat-text">MATRIZ VITAL GYM</div>
                    </div>
                    <div class="branch-stat-item">
                        <div class="branch-stat-icon"><?= $counters['nuevas']['xoxo'] ?></div>
                        <div class="branch-stat-text">VITAL GYM SUC XOXO</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="side-widget">
                <div class="side-widget-header header-bg-success">
                    <h3>RENOVACIONES</h3>
                    <span class="side-widget-total"><?= $counters['renovaciones']['total'] ?></span>
                </div>
                <div class="side-widget-body">
                    <div class="branch-stat-item">
                        <div class="branch-stat-icon"><?= $counters['renovaciones']['matriz'] ?></div>
                        <div class="branch-stat-text">MATRIZ VITAL GYM</div>
                    </div>
                    <div class="branch-stat-item">
                        <div class="branch-stat-icon"><?= $counters['renovaciones']['xoxo'] ?></div>
                        <div class="branch-stat-text">VITAL GYM SUC XOXO</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-6">
            <div class="side-widget">
                <div class="side-widget-header header-bg-danger">
                    <h3>POR VENCER</h3>
                    <span class="side-widget-total"><?= $counters['por_vencer']['total'] ?></span>
                </div>
                <div class="side-widget-body">
                    <div class="branch-stat-item">
                        <div class="branch-stat-icon"><?= $counters['por_vencer']['matriz'] ?></div>
                        <div class="branch-stat-text">MATRIZ VITAL GYM</div>
                    </div>
                    <div class="branch-stat-item">
                        <div class="branch-stat-icon"><?= $counters['por_vencer']['xoxo'] ?></div>
                        <div class="branch-stat-text">VITAL GYM SUC XOXO</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-6">
            <div class="side-widget">
                <div class="side-widget-header header-bg-danger">
                    <h3>VENCIDAS</h3>
                    <span class="side-widget-total"><?= $counters['vencidas']['total'] ?></span>
                </div>
                <div class="side-widget-body">
                    <div class="branch-stat-item">
                        <div class="branch-stat-icon"><?= $counters['vencidas']['matriz'] ?></div>
                        <div class="branch-stat-text">MATRIZ VITAL GYM</div>
                    </div>
                    <div class="branch-stat-item">
                        <div class="branch-stat-icon"><?= $counters['vencidas']['xoxo'] ?></div>
                        <div class="branch-stat-text">VITAL GYM SUC XOXO</div>
                    </div>
                </div>
            </div>
        </div>

    </div> 
    

</div>