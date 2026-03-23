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



service('auth')->routes($routes);
