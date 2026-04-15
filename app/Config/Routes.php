<?php
namespace Config;

use CodeIgniter\Router\RouteCollection;

$routes = Services::routes();


if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}



/**
 * @var RouteCollection $routes
 */




$routes->get('/', 'Home::index');
$routes->post('/MandaraBDUsuario','Home::MandaraBDUsuario');

$routes->get('/accesoclientes', 'Kiosko::index');
$routes->post('/verificarHuella','Kiosko::verificarHuella');

$routes->get('/asistencia', 'Kiosko::asistencia');
$routes->post('/RegistroddeAsistencia', 'Kiosko::RegistroddeAsistencia');
$routes->get('/verAsistencias', 'Home::verAsistencias');
$routes->post('/verAsistencias', 'Home::verAsistencias');

$routes->get('/vistaRegistroHuella', 'Home::vistaRegistroHuella');
$routes->post('/guardarHuellaUsuario', 'Home::guardarHuellaUsuario');

$routes->get('/recordatoriosMembresia', 'Home::recordatoriosMembresia');
$routes->post('/marcarAvisoEnviado', 'Home::marcarAvisoEnviado');

$routes->get('/renovaciones', 'Home::panel');

$routes->get('/renovacionesRegistro/(:num)', 'Renovaciones::index/$1');
$routes->post('/renovacionesguardar', 'Renovaciones::guardarRenovacionAjax');

$routes->get('/dashboard', 'Dashboard::paginaPrincipal');

$routes->get('/CambioFechas', 'Dashboard::CambioFechas');
$routes->post('/actualizarFechaMembresia', 'Dashboard::actualizarFechaMembresia');

$routes->get('/servicios', 'Home::verMembresias');

$routes->get('/tienda', 'Tienda::index');

$routes->post('/registrarVenta', 'Tienda::registrarVenta');

$routes->get('/inventario', 'Tienda::inventario');
$routes->post('/guardarProducto', 'Tienda::guardarProducto');

$routes->get('/cartaresponsiva', 'Home::FirmarResponsiva');

$routes->post('/GuardarResponsiva', 'Home::GuardarResponsiva');



$routes->get('/recepcion', 'Recepcion::index');
$routes->get('/obtenerPendientesAJAX', 'Recepcion::obtenerPendientesAJAX');
$routes->post('/guardarPagoEInscripcion', 'Recepcion::guardarPagoEInscripcion');
$routes->get('/enrolar/(:num)', 'Biometrico::enrolar/$1');
$routes->post('/guardarHuellaCliente', 'Biometrico::guardarHuellaCliente');






service('auth')->routes($routes);
