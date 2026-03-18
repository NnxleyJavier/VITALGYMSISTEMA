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
            background-color: #f4f6f9; /* Fondo gris claro para resaltar el contenido */
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
            z-index: 10; /* Para que la sombra pase por encima del menú */
        }
        .logo-box { display: flex; flex-direction: column; align-items: flex-start; }
        .logo-title { margin: 0; font-size: 26px; font-weight: 900; letter-spacing: 1px; line-height: 1; }
        .logo-subtitle { color: #c4b50d; font-size: 11px; letter-spacing: 3px; margin-top: 4px; font-weight: bold; }
        .user-box { display: flex; align-items: center; }
        .greeting { color: #a0a0b0; font-size: 16px; margin-right: 15px; }
        .greeting strong { color: #ffffff; font-weight: bold; }
        .avatar-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #333; }

        /* --- 3. CUERPO DE LA APP Y MENÚ LATERAL --- */
        .app-body {
            display: flex;
            flex: 1; /* Toma todo el alto restante debajo del header */
            overflow: hidden;
        }

        .sidebar-vital {
            width: 260px;
            background-color: #1a1a2e; /* Azul/Gris ultra oscuro */
            color: #fff;
            display: flex;
            flex-direction: column;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        /* Títulos de sección en el menú */
        .sidebar-label {
            color: #6a6a8c;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
            padding: 20px 25px 10px;
            text-transform: uppercase;
        }

        /* Botones del menú */
        .sidebar-vital a {
            color: #a0a0b0;
            text-decoration: none;
            padding: 15px 25px;
            font-size: 15px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 4px solid transparent; /* Borde invisible por defecto */
        }
        .sidebar-vital a .glyphicon { margin-right: 12px; font-size: 18px; }
        
        /* Efecto al pasar el mouse y elemento activo */
        .sidebar-vital a:hover, 
        .sidebar-vital a.active {
            background-color: #24243e; /* Brillo ligero */
            color: #ffffff;
            border-left: 4px solid #c4b50d; /* Línea dorada Fitness */
        }

        /* --- 4. CONTENEDOR CENTRAL --- */
        .content-vital {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto; /* Permite scroll solo en el contenido */
        }
        .content-vital > .container-fluid {
            flex: 1 0 auto;
            padding: 30px 40px; /* Margen respirable alrededor de tu formulario */
        }
    </style>
</head>
<body>

<header class="header-vital">
    <div class="logo-box">
        <h1 class="logo-title">VITAL GYM</h1>
        <span class="logo-subtitle">FITNESS</span>
    </div>
    
    <div class="user-box dropdown">
        <span class="greeting">Hola, <strong><?= esc($username ?? 'username') ?></strong></span>
       
        
        <img src="<?= base_url('assets/img/avatar.png') ?>" 
             alt="Perfil" 
             class="avatar-img" 
             id="perfilDropdown" 
             data-toggle="dropdown" 
             data-bs-toggle="dropdown" 
             aria-expanded="false" 
             style="cursor: pointer;">
        
        <div class="dropdown-menu dropdown-menu-right dropdown-menu-end shadow" aria-labelledby="perfilDropdown" style="margin-top: 15px; padding: 10px; border-radius: 8px;">
            
            <form class="m-0" id="cerrarsesion_form" action="<?= base_url('index.php/logout') ?>" method="get">
                <button type="submit" class="btn btn-outline-danger btn-sm shadow w-100" id="cerrarsesion">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>

        </div>
    </div>
</header>

    <div class="app-body">
        
        <aside class="sidebar-vital">
            <div class="sidebar-label">Principal</div>
            <a href="<?= base_url('/dashboard') ?>"><span class="glyphicon glyphicon-home"></span> Panel de Control</a>
            
            <a href="<?= base_url('/') ?>" class="active"><span class="glyphicon glyphicon-user"></span> Registro de Clientes</a>
            <a href="<?= base_url('/acceso-clientes') ?>"><span class="glyphicon glyphicon-transfer"></span> Control de Acceso</a>
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