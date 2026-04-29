<div class="container" style="margin-top: 30px; margin-bottom: 50px;">
    
    <!-- Encabezado de Bienvenida -->
    <div class="row">
        <div class="col-md-12">
            <div class="jumbotron" style="background-color: #f8f9fa; border-left: 5px solid #337ab7;">
             <!--    <h1>¡Hola, <?= esc($username) ?>! 👋</h1> -->
                <p class="lead">Bienvenido al panel de administración de <strong>VitalGym</strong>. Selecciona una opción para comenzar.</p>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Acceso Rápido -->
    <div class="row">
        
        <!-- Módulo 1: Registro de Clientes -->
        <div class="col-md-4 col-sm-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="glyphicon glyphicon-user"></i> Nuevos Clientes</h3>
                </div>
                <div class="panel-body text-center">
                    <span class="glyphicon glyphicon-plus-sign" style="font-size: 4em; color: #337ab7; margin-bottom: 15px;"></span>
                    <p>Registrar un nuevo miembro, capturar huella y asignar membresía.</p>
                    <a href="<?= base_url('/') ?>" class="btn btn-primary btn-block">Ir a Registro</a>
                </div>
            </div>
        </div>

        <!-- Módulo 2: Renovaciones -->
        <div class="col-md-4 col-sm-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="glyphicon glyphicon-refresh"></i> Renovaciones</h3>
                </div>
                <div class="panel-body text-center">
                    <span class="glyphicon glyphicon-credit-card" style="font-size: 4em; color: #8a6d3b; margin-bottom: 15px;"></span>
                    <p>Buscar clientes existentes y procesar pagos de renovación.</p>
                    <a href="<?= base_url('/renovaciones') ?>" class="btn btn-warning btn-block">Panel de Renovación</a>
                </div>
            </div>
        </div>

        <!-- Módulo 3: Kiosco / Asistencia -->
        <div class="col-md-4 col-sm-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="glyphicon glyphicon-time"></i> Kiosco de Acceso</h3>
                </div>
                <div class="panel-body text-center">
                    <span class="glyphicon glyphicon-hand-up" style="font-size: 4em; color: #3c763d; margin-bottom: 15px;"></span>
                    <p>Pantalla para lectura de huella digital y control de acceso.</p>
                    <a href="<?= base_url('/accesoclientes') ?>" class="btn btn-success btn-block">Abrir Kiosco</a>
                </div>
            </div>
        </div>

        <!-- Módulo 4: Reportes Financieros (Nuevo) -->
        <div class="col-md-4 col-sm-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="glyphicon glyphicon-stats"></i> Reporte de Ingresos</h3>
                </div>
                <div class="panel-body text-center">
                    <span class="glyphicon glyphicon-usd" style="font-size: 4em; color: #31708f; margin-bottom: 15px;"></span>
                    <p>Consulta el desglose de ingresos por día, semana y mes.</p>
                    <a href="<?= base_url('/dashboard/reporteIngresos') ?>" class="btn btn-info btn-block">Ver Finanzas</a>
                </div>
            </div>
        </div>

        <!-- Módulo 5: Recordatorios -->
        <div class="col-md-4 col-sm-6">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="glyphicon glyphicon-bell"></i> Vencimientos</h3>
                </div>
                <div class="panel-body text-center">
                    <span class="glyphicon glyphicon-calendar" style="font-size: 4em; color: #a94442; margin-bottom: 15px;"></span>
                    <p>Ver membresías por vencer y enviar alertas de WhatsApp.</p>
                    <a href="<?= base_url('/recordatoriosMembresia') ?>" class="btn btn-danger btn-block">Ver Alertas</a>
                </div>
            </div>
        </div>

    </div>
</div>