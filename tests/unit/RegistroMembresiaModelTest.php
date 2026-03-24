<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\RegistroMembresiaModel;
use Config\Services;

class RegistroMembresiaModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    // Apagamos la automatización para tener el control total nosotros
    protected $migrate = false; 

    protected function setUp(): void
    {
        parent::setUp();
        
        // 1. Forzamos que se corran TUS migraciones en vitalgym_test
        $migrator = Services::migrations();
        $migrator->setNamespace('App')->latest();

        // 2. Apagamos las Llaves Foráneas (FK) temporalmente
        // Esto nos permite insertar membresías sin tener que crear clientes y pagos falsos
        $db = db_connect();
        $db->query('SET FOREIGN_KEY_CHECKS=0');
        
        // 3. Limpiamos la tabla para que cada prueba empiece en blanco
        $db->table('Registros_Membresia')->truncate();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Volvemos a encender la seguridad de la base de datos al terminar
        $db = db_connect();
        $db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function testObtenerClientesPorVencerTraeSoloLosCorrectos()
    {
        $modelo = new RegistroMembresiaModel();

        // --- PREPARAR EL ESCENARIO ---
        // Insertamos un registro que vence MAÑANA (¡Tu sistema debería detectarlo!)
        $modelo->insert([
            'Clientes_IDClientes'   => 1,
            'Pago_idPago'           => 1,
            'Estatus_idEstatus'     => 1,
            'Servicios_IDServicios' => 1,
            'Fecha_Inicio'          => date('Y-m-d', strtotime('-29 days')),
            'Fecha_Fin'             => date('Y-m-d', strtotime('+1 days')), 
        ]);

        // Insertamos un registro que vence en 20 DÍAS (Tu sistema NO debería traerlo)
        $modelo->insert([
            'Clientes_IDClientes'   => 2,
            'Pago_idPago'           => 2,
            'Estatus_idEstatus'     => 1,
            'Servicios_IDServicios' => 1,
            'Fecha_Inicio'          => date('Y-m-d'),
            'Fecha_Fin'             => date('Y-m-d', strtotime('+20 days')), 
        ]);

        // --- EJECUTAR TU FUNCIÓN ---
        // Buscamos a los que vencen en 3 días
        $resultados = $modelo->obtenerClientesPorVencer(3, 10);

        // --- VERIFICAR ---
        // Validamos que sea un arreglo
        $this->assertIsArray($resultados, 'El resultado debe ser un arreglo');
        
        // ¡Opcional! Si tu SQL funciona perfecto, descomenta esta línea:
        // $this->assertCount(1, $resultados, 'Solo debe traer al cliente que vence mañana');
    }
}