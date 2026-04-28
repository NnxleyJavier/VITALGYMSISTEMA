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



service('auth')->routes($routes);



$routes->post('/user-login', 'Api\AuthController::userLogin');


$routes->get('/', 'Home::index',['filter'=>'permission:admin.access']); 
$routes->post('/MandaraBDUsuario','Home::MandaraBDUsuario',['filter'=>'permission:admin.access']); 

$routes->get('/accesoclientes', 'Kiosko::index'); 
$routes->post('/verificarHuella','Kiosko::verificarHuella'); 

$routes->get('/asistencia', 'Kiosko::asistencia',['filter'=>'permission:user.access']);
$routes->post('/RegistroddeAsistencia', 'Kiosko::RegistroddeAsistencia',['filter'=>'permission:user.access']);

$routes->get('/verAsistencias', 'Home::verAsistencias',['filter'=>'permission:superadmin.vista']);
$routes->post('/verAsistencias', 'Home::verAsistencias',['filter'=>'permission:superadmin.vista']);

$routes->get('/vistaRegistroHuella', 'Home::vistaRegistroHuella',['filter'=>'permission:admin.access']);
$routes->post('/guardarHuellaUsuario', 'Home::guardarHuellaUsuario',['filter'=>'permission:admin.access']);

$routes->get('/recordatoriosMembresia', 'Home::recordatoriosMembresia',['filter'=>'permission:admin.access']);
$routes->post('/marcarAvisoEnviado', 'Home::marcarAvisoEnviado',['filter'=>'permission:admin.access']);

$routes->get('/renovaciones', 'Renovaciones::panel',['filter'=>'permission:admin.access']);


$routes->get('/renovacionesRegistro/(:num)', 'Renovaciones::index/$1',['filter'=>'permission:admin.access']);
$routes->post('/renovacionesguardar', 'Renovaciones::guardarRenovacionAjax',['filter'=>'permission:admin.access']);





$routes->get('/dashboard', 'Dashboard::paginaPrincipal',['filter'=>'permission:superadmin.vista']);

$routes->get('/CambioFechas', 'Dashboard::CambioFechas',['filter'=>'permission:admin.access']);
$routes->post('/actualizarFechaMembresia', 'Dashboard::actualizarFechaMembresia',['filter'=>'permission:admin.access']);

$routes->get('/servicios', 'Home::verMembresias',['filter'=>'permission:admin.access']); //Membresias 

$routes->get('/tienda', 'Tienda::index',['filter'=>'permission:admin.access']);

$routes->post('/registrarVenta', 'Tienda::registrarVenta',['filter'=>'permission:admin.access']);

$routes->get('/inventario', 'Tienda::inventario',['filter'=>'permission:admin.access']);
$routes->post('/guardarProducto', 'Tienda::guardarProducto',['filter'=>'permission:admin.access']);

$routes->get('/cartaresponsiva', 'Home::FirmarResponsiva');

$routes->post('/GuardarResponsiva', 'Home::GuardarResponsiva');



$routes->get('/recepcion', 'Recepcion::index',['filter'=>'permission:admin.access']);
$routes->get('/obtenerPendientesAJAX', 'Recepcion::obtenerPendientesAJAX',['filter'=>'permission:admin.access']);
$routes->post('/guardarPagoEInscripcion', 'Recepcion::guardarPagoEInscripcion',['filter'=>'permission:admin.access']);
$routes->get('/enrolar/(:num)', 'Biometrico::enrolar/$1',['filter'=>'permission:admin.access']);
$routes->post('/guardarHuellaCliente', 'Biometrico::guardarHuellaCliente',['filter'=>'permission:admin.access']);

$routes->post('/procesarSolicitudFecha', 'Dashboard::procesarSolicitudFecha',['filter'=>'permission:superadmin.vista']);

$routes->get('/verIngresos', 'Home::verIngresos',['filter'=>'permission:admin.access']);


$routes->post('/consultaRapidaSocio', 'Recepcion::consultaRapidaSocio', ['filter'=>'permission:admin.access']);

$routes->post('/solicitarPrecioAmigo', 'Home::solicitarPrecioAmigo');
$routes->get('/autorizar', 'Dashboard::autorizarPrecios', ['filter' => 'permission:superadmin.vista']);
$routes->post('/procesarPrecioAmigoAjax', 'Dashboard::procesarPrecioAmigoAjax', ['filter' => 'permission:superadmin.vista']);

service('auth')->routes($routes);
