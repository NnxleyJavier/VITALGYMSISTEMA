<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title><?= esc($titulo ?? 'VitalGym') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/CSS/bootstrap-min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/CSS/app.css') ?>">
    <script src="<?= base_url('assets/lib/jquery.min.js')?>"></script> 
    <script src="<?= base_url('assets/lib/bootstrap.min.js')?>"></script> 
<style>
        /* --- 1. ESTRUCTURA MAESTRA (Flexbox) --- */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* --- 2. HEADER SUPERIOR NEGRO --- */
        .header-vital {
            background-color: #000000;
            color: #ffffff;
            padding: 10px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            z-index: 1050; /* Aumentado para sobreponerse al menú móvil */
            position: relative;
        }
        
        .header-left { display: flex; align-items: center; }
        
        /* Botón Hamburguesa (Oculto en PC) */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            margin-right: 20px;
        }

        .logo-box { display: flex; flex-direction: column; align-items: flex-start; }
        .logo-title { margin: 0; font-size: 26px; font-weight: 900; letter-spacing: 1px; line-height: 1; }
        .logo-subtitle { color: #c4b50d; font-size: 11px; letter-spacing: 3px; margin-top: 4px; font-weight: bold; }
        
        .user-box { display: flex; align-items: center; }
        .greeting { color: #a0a0b0; font-size: 16px; margin-right: 15px; }
        .greeting strong { color: #ffffff; font-weight: bold; }
        .avatar-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #333; cursor: pointer; }

        /* --- 3. CUERPO DE LA APP Y MENÚ LATERAL --- */
        .app-body {
            display: flex;
            flex: 1;
            overflow: hidden;
            position: relative; /* Para el overlay del móvil */
        }

        .sidebar-vital {
            width: 260px;
            background-color: #1a1a2e;
            color: #fff;
            display: flex;
            flex-direction: column;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
            transition: left 0.3s ease-in-out; /* Animación de despliegue */
            z-index: 1000;
        }
        
        .sidebar-label { color: #6a6a8c; font-size: 11px; font-weight: bold; letter-spacing: 2px; padding: 20px 25px 10px; text-transform: uppercase; }
        
        .sidebar-vital a {
            color: #a0a0b0;
            text-decoration: none;
            padding: 15px 25px;
            font-size: 15px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .sidebar-vital a .glyphicon { margin-right: 12px; font-size: 18px; }
        
        .sidebar-vital a:hover, 
        .sidebar-vital a.active {
            background-color: #24243e;
            color: #ffffff;
            border-left: 4px solid #c4b50d;
        }

        /* --- 4. CONTENEDOR CENTRAL --- */
        .content-vital {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .content-vital > .container-fluid {
            flex: 1 0 auto;
            padding: 30px 40px;
        }

        /* Overlay oscuro para cerrar el menú en móviles */
        .sidebar-overlay {
            display: none;
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        /* --- 5. RESPONSIVIDAD (MÓVILES) --- */
        @media (max-width: 768px) {
            .header-vital { padding: 10px 20px; }
            .menu-toggle { display: block; } /* Mostrar botón hamburguesa */
            .greeting { display: none; } /* Ocultar el saludo largo en móvil para ahorrar espacio */
            .content-vital > .container-fluid { padding: 20px 15px; } /* Menos margen interior */
            
            /* Ocultar menú por defecto a la izquierda */
            .sidebar-vital {
                position: absolute;
                height: 100%;
                left: -260px;
            }
            
            /* Clase que se agrega con JS para abrir el menú */
            .sidebar-vital.open {
                left: 0;
            }
        }
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
        <div class="sidebar-label">Principal</div>
        <a href="<?= base_url('/dashboard') ?>"><span class="glyphicon glyphicon-home"></span> Panel de Control</a>
        
        <a href="<?= base_url('/') ?>"><span class="glyphicon glyphicon-user"></span> Registro de Clientes</a>
        <a href="<?= base_url('/accesoclientes') ?>"><span class="glyphicon glyphicon-transfer"></span> Control de Acceso</a>
        <a href="<?= base_url('/asistencia') ?>"><span class="glyphicon glyphicon-check"></span> Asistencia</a>
        
        <div class="sidebar-label">Administración</div>
        <a href="<?= base_url('/pagos') ?>"><span class="glyphicon glyphicon-usd"></span> Pagos y Caja</a>
        <a href="<?= base_url('/servicios') ?>"><span class="glyphicon glyphicon-list-alt"></span> Membresías</a>
        
        <div class="sidebar-label">Sistema</div>
        <a href="<?= base_url('/reportes') ?>"><span class="glyphicon glyphicon-stats"></span> Reportes</a>
        <a href="<?= base_url('/logout') ?>" style="margin-top: auto; color: #ff6b6b;"><span class="glyphicon glyphicon-log-out"></span> Cerrar Sesión</a>
    </aside>

    <div class="content-vital">
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