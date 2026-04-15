<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title><?= esc($titulo ?? 'VitalGym') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/CSS/bootstrap-min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/CSS/appmenu.css') ?>">
    <script src="<?= base_url('assets/lib/jquery.min.js')?>"></script> 
    <script src="<?= base_url('assets/lib/bootstrap.min.js')?>"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>


        /* --- 5. RESPONSIVIDAD (MÓVILES) --- */
      
    </style>
</head>
<body>

<header class="header-vital">
    <div class="header-left">
        <button id="menuToggle" class="menu-toggle">
            <span class="glyphicon glyphicon-menu-hamburger"></span>
        </button>
        
        <div class="logo-box">
            <h1 class="logo-title">VITAL GYM</h1>
            <span class="logo-subtitle">FITNESS</span>
        </div>
    </div>
    
    <div class="user-box dropdown">
        <span class="greeting">Hola, <strong><?= esc($username ?? 'username') ?></strong></span>
        
        <img src="<?= base_url('assets/img/avatar.png') ?>" 
             alt="Perfil" 
             class="avatar-img" 
             id="perfilDropdown" 
             data-toggle="dropdown" 
             aria-expanded="false">
        
        <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="perfilDropdown" style="margin-top: 15px; padding: 10px; border-radius: 8px;">
            <form class="m-0" id="cerrarsesion_form" action="<?= base_url('index.php/logout') ?>" method="get">
                <button type="submit" class="btn btn-outline-danger btn-sm shadow w-100" id="cerrarsesion">
                    <i class="glyphicon glyphicon-log-out"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</header>

<div class="app-body">
    
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar-vital" id="sidebarVital">
        <div class="sidebar-label opcion1">Principal</div>
        <a href="<?= base_url('/dashboard') ?>"><span class="glyphicon glyphicon-home"></span> Panel de Control</a>
       <a href="<?= base_url('/tienda') ?>"><span class="glyphicon glyphicon-shopping-cart"></span> Tienda</a>
        
        <a href="<?= base_url('/inventario') ?>"><span class="glyphicon glyphicon-list-alt"></span> Inventario</a>
        
        <a href="<?= base_url('/') ?>"><span class="glyphicon glyphicon-user"></span> Registro de Clientes</a>
        <a href="<?= base_url('/accesoclientes') ?>"><span class="glyphicon glyphicon-transfer"></span> Control de Acceso</a>
        <a href="<?= base_url('/asistencia') ?>"><span class="glyphicon glyphicon-check"></span> Asistencia</a>
        
        <div class="sidebar-label">Administración</div>
        <a href="<?= base_url('/recepcion') ?>"><span class="glyphicon glyphicon-user"></span> Recepción de Clientes </a>
         <a href="<?= base_url('/cartaresponsiva') ?>"><span class="glyphicon glyphicon-file"></span> Carta Responsiva </a>
        <a href="<?= base_url('/servicios') ?>"><span class="glyphicon glyphicon-list-alt"></span> Membresías</a>
        <a href="<?= base_url('/vistaRegistroHuella') ?>"><span class="glyphicon glyphicon-inbox"></span> Registro de Huellas Staff</a>
        <a href="<?= base_url('/recordatoriosMembresia') ?>"><span class="glyphicon glyphicon-comment"></span> Recordatorios de Membresía</a>
        <a href="<?= base_url('/renovaciones') ?>"><span class="glyphicon glyphicon-refresh"></span> Renovaciones de Membresía</a>
        <a href="<?= base_url('/verAsistencias') ?>"><span class="glyphicon glyphicon-refresh"></span> Ver Asitencia </a>

        <div class="sidebar-label">Sistema</div>
        <a href="<?= base_url('/dashboard') ?>"><span class="glyphicon glyphicon-stats"></span> Reportes </a>
        <a href="<?= base_url('/CambioFechas') ?>"><span class="glyphicon glyphicon-calendar"></span> Ajuste de Fechas</a>
        <a href="<?= base_url('/logout') ?>" style="margin-top: auto; color: #ff6b6b;"><span class="glyphicon glyphicon-log-out"></span> Cerrar Sesión</a>
    </aside>

    <div class="content-vital">
       <div class="vista-global-container" style="min-height: calc(100vh - 130px); padding-top: 20px;">
            <div class="container-fluid">
            <script>
$(document).ready(function() {
    
    // 1. MARCADOR DINÁMICO DEL MENÚ (Active)
    // Obtenemos la URL exacta en la que está el usuario ahora mismo
    var currentUrl = window.location.href.split(/[?#]/)[0]; 

    // Revisamos cada enlace del menú...
    $('.sidebar-vital a').each(function() {
        // Si el enlace coincide con la URL actual...
        if (this.href === currentUrl || currentUrl.startsWith(this.href + '/')) {
            $(this).addClass('active'); // Lo marcamos
        } else {
            $(this).removeClass('active'); // Limpiamos los demás por si acaso
        }
    });

    // 2. FUNCIONALIDAD DEL MENÚ MÓVIL (Abrir y Cerrar)
    $('#menuToggle').click(function() {
        $('#sidebarVital').toggleClass('open');
        $('#sidebarOverlay').fadeToggle(200); // Muestra/Oculta la sombra suavemente
    });

    // Cerrar al tocar lo oscurecido (fuera del menú)
    $('#sidebarOverlay').click(function() {
        $('#sidebarVital').removeClass('open');
        $(this).fadeOut(200);
    });

});
</script>