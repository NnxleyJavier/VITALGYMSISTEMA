<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Config\Factories; 
use App\Controllers\Home;
use App\Models\Servicios;
use App\Models\Cliente;
use App\Models\UsersModel;
use CodeIgniter\Shield\Entities\User;
use Config\Services;

class TestableHome extends Home
{
    public static $biometricResult = [];

    protected function generarTemplateBiometrico($h1 = null, $h2 = null, $h3 = null, $h4 = null, $h5 = null, $h6 = null)
    {
        return self::$biometricResult;
    }
}

class HomeTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;
    use AuthenticationTesting;

    protected $namespace = 'all'; 
    
    protected function setUp(): void
    {
        parent::setUp();
        Services::reset();
        Factories::resetOf('models'); 

        $migrator = Services::migrations();
        $migrator->setNamespace('CodeIgniter\Settings')->latest();
        $migrator->setNamespace('CodeIgniter\Shield')->latest();
    }

    public function testIndex()
    {
        $mockServiciosModel = $this->getMockBuilder(Servicios::class)
            ->onlyMethods(['findAll']) 
            ->addMethods(['where'])    
            ->disableOriginalConstructor()
            ->getMock();

        // <-- Agregamos el 'Costo' que pedía tu HTML
        $mockMembresias = [['IDServicios' => 1, 'Catalogo' => 'MEMBRESIA', 'NombreMembresia' => 'Mensual', 'Costo' => 500]];
        $mockExtras     = [['IDServicios' => 2, 'Catalogo' => 'EXTRAS', 'NombreMembresia' => 'Locker', 'Costo' => 50]];

        $callCount = 0;
        $mockServiciosModel->method('where')->willReturnSelf();
        $mockServiciosModel->method('findAll')->will($this->returnCallback(function () use (&$callCount, $mockMembresias, $mockExtras) {
            $callCount++;
            return ($callCount === 1) ? $mockMembresias : $mockExtras;
        }));

        Factories::injectMock('models', Servicios::class, $mockServiciosModel);

        $user = new User(['id' => 1, 'username' => 'testuser']);
        $this->actingAs($user); 

        $result = $this->controller(Home::class)->execute('index');

        $this->assertTrue($result->isOK());
        $result->assertSee('Registro de Usuario | VitalGym');
        $result->assertSee('Mensual'); 
        $result->assertSee('Locker');  
    }

    public function testMandaraBDUsuarioSuccess()
    {
        $mockServiciosModel = $this->createMock(Servicios::class);
        $mockServiciosModel->method('find')->willReturn(['NombreMembresia' => 'Mensual']);
        Factories::injectMock('models', Servicios::class, $mockServiciosModel);

        $mockClienteModel = $this->createMock(Cliente::class);
        $mockClienteModel->method('registrarClienteCompleto')->willReturn(true);
        Factories::injectMock('models', Cliente::class, $mockClienteModel);

        TestableHome::$biometricResult = [
            'success' => true,
            'template' => 'fake_biometric_template'
        ];

        $request = \Config\Services::request();
        $request->setMethod('post');
        $request->setGlobal('post', [
            'Nombre' => 'Test', 
            'ApellidoP' => 'User',
            'Servicios_IDServicios' => 1, 
            'Tipo_Pago' => 'Efectivo', 
            'MontoTotal' => 100,
            'huella_1' => 'h1', 'huella_2' => 'h2', 'huella_3' => 'h3',
            'huella_4' => 'h4', 'huella_5' => 'h5', 'huella_6' => 'h6',
        ]);

        $result = $this->withRequest($request)
                       ->controller(TestableHome::class) 
                       ->execute('MandaraBDUsuario');

        $this->assertTrue($result->isOK());
        
        // <-- Ignoramos el Token aleatorio, comprobamos los datos reales
        $result->assertJSONFragment([
            'status' => 'success',
            'message' => 'Cliente registrado y huella guardada correctamente.'
        ]);
    }

    public function testMandaraBDUsuarioBiometricFailure()
    {
        $mockServiciosModel = $this->createMock(Servicios::class);
        $mockServiciosModel->method('find')->willReturn(['NombreMembresia' => 'Mensual']);
        Factories::injectMock('models', Servicios::class, $mockServiciosModel);

        TestableHome::$biometricResult = [
            'success' => false,
            'mensaje' => 'Biometric scan failed'
        ];

        $request = \Config\Services::request();
        $request->setMethod('post');
        $request->setGlobal('post', [
            'Nombre' => 'Test', 
            'ApellidoP' => 'User',
            'Servicios_IDServicios' => 1, 
            'Tipo_Pago' => 'Efectivo', 
            'MontoTotal' => 100,
            'huella_1' => 'h1', 'huella_2' => 'h2', 'huella_3' => 'h3',
            'huella_4' => 'h4', 'huella_5' => 'h5', 'huella_6' => 'h6',
        ]);

        $result = $this->withRequest($request)
                       ->controller(TestableHome::class)
                       ->execute('MandaraBDUsuario');

        $this->assertTrue($result->isOK());
        $result->assertJSONFragment([
            'status' => 'error',
            'message' => 'Biometric scan failed'
        ]);
    }

    public function testGuardarHuellaUsuarioSuccess()
    {
        $mockUserModel = $this->createMock(UsersModel::class);
        $mockUserModel->method('update')->willReturn(true);
        Factories::injectMock('models', UsersModel::class, $mockUserModel);

        TestableHome::$biometricResult = [
            'success' => true,
            'template' => 'fake_biometric_template'
        ];

        $user = new User(['id' => 123]);
        $this->actingAs($user); 

        $request = \Config\Services::request();
        $request->setMethod('post');
        $request->setGlobal('post', [
            'huella_1' => 'h1', 'huella_2' => 'h2', 'huella_3' => 'h3',
            'huella_4' => 'h4', 'huella_5' => 'h5', 'huella_6' => 'h6',
        ]);

        $result = $this->withRequest($request)
                       ->controller(TestableHome::class)
                       ->execute('guardarHuellaUsuario');

        $this->assertTrue($result->isOK());
        $result->assertJSONFragment([
            'status' => 'success',
            'mensaje' => '¡Tu huella ha sido procesada y vinculada a tu cuenta exitosamente!'
        ]);
    }

    public function testMandaraBDUsuarioPaseDiarioSuccess()
    {
        $mockClienteModel = $this->createMock(Cliente::class);
        $mockClienteModel->method('registrarClienteCompleto')->willReturn(true);
        Factories::injectMock('models', Cliente::class, $mockClienteModel);

        $mockServiciosModel = $this->createMock(Servicios::class);
        $mockServiciosModel->method('find')->willReturn(['NombreMembresia' => 'GYM 1 DÍA']);
        Factories::injectMock('models', Servicios::class, $mockServiciosModel);

        $request = \Config\Services::request();
        $request->setMethod('post');
        $request->setGlobal('post', [
            'Nombre' => 'Test Pase Diario',
            'Servicios_IDServicios' => 99, 
            'Tipo_Pago' => 'Efectivo',
            'MontoTotal' => 50,
        ]);

        $result = $this->withRequest($request)
                       ->controller(Home::class) 
                       ->execute('MandaraBDUsuario');

        $this->assertTrue($result->isOK());
        $result->assertJSONFragment([
            'status' => 'success',
            'message' => 'Cliente registrado y huella guardada correctamente.'
        ]);
    }
}