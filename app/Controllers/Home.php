<?php

namespace App\Controllers;

use App\Models\Servicios;
use App\Models\Cliente;
use App\Models\UsersModel;
use App\Models\RegistroMembresiaModel;
use CodeIgniter\Shield\Models\UserModel;


class Home extends BaseController
{
    public function index(): string
    {


         $idUser = obtener_username(); // obtengo el id User de la Sesion

        $servicioModel = new Servicios();

        // 1. Filtrar usando las funciones de CodeIgniter:
        // Traemos solo los que dicen "MEMBRESIA"
        $membresias = $servicioModel->where('Catalogo', 'MEMBRESIA')->findAll();
        
        // Traemos solo los que dicen "EXTRAS"
        $extras = $servicioModel->where('Catalogo', 'EXTRAS')->findAll();



        $data = [
           'titulo'     => 'Registro de Usuario | VitalGym',
            'membresias' => $membresias, // <- Enviamos las membresías
            'extras'     => $extras,     // <- Enviamos los extras
            'username'   => $idUser
        ];


        return view('html/main', $data)
             . view('html/RegistroClientes', $data)
             . view('html/footer');
    }





    public function MandaraBDUsuario() {
        $resultado = $this->validarProducto();
      //  var_dump($resultado);
        // Si hay error en la validación o en la biometría
        if (isset($resultado['error'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $resultado['error'],
                'token' => csrf_hash()
            ]);
        }

        $usuarioModel = new Cliente(); 
        
        // Inserción en la base de datos
        if ($usuarioModel->registrarClienteCompleto($resultado)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Cliente registrado y huella guardada correctamente.',
                'token' => csrf_hash() 
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al guardar en la base de datos (revisa tu modelo).',
                'token' => csrf_hash()
            ]);
        }
    }

   private function validarProducto()
    {
        $Nombre = $this->request->getPost("Nombre");
        $ApellidoP = $this->request->getPost("ApellidoP");
        $ApellidoM = $this->request->getPost("ApellidoM");
        $Telefono = $this->request->getPost("Telefono");
        $Correo = $this->request->getPost("Correo");
        $Fecha = date('Y-m-d H:i:s');
        
        $h1 = $this->request->getPost("huella_1");
        $h2 = $this->request->getPost("huella_2");
        $h3 = $this->request->getPost("huella_3");
        $h4 = $this->request->getPost("huella_4");
        $h5 = $this->request->getPost("huella_5");
        $h6 = $this->request->getPost("huella_6");

        $Servicios_IDServicios = $this->request->getPost("Servicios_IDServicios");
        $Tipo_Pago = $this->request->getPost("Tipo_Pago");

        // =========================================================
        // 🔥 NUEVOS DATOS CAPTURADOS DEL HTML
        // =========================================================
        $MontoTotal = $this->request->getPost("MontoTotal");
       // Agregamos (array) para forzar a PHP a convertirlo en lista siempre
        $servicios_extra = (array) $this->request->getPost("servicios_extra");// Esto llega como Array o Null

        // 🔥 NUEVA LÍNEA: Capturamos el Checkbox
        $Acepta_WhatsApp = $this->request->getPost("Acepta_WhatsApp") ? 1 : 0;

        // Validación básica (Agregamos validación para MontoTotal)
        if (empty($Nombre) || empty($ApellidoP) || empty($Telefono) || empty($Fecha) || 
            empty($h1) || empty($h2) || empty($h3) || empty($h4) || empty($h5) || empty($h6) ||
            empty($Servicios_IDServicios) || empty($Tipo_Pago) || empty($MontoTotal)) {
            return ["error" => "Faltan datos obligatorios, no se completaron las 6 capturas de huella, o el monto es inválido."];
        }

        // Llamada a Python
        $respuestaBiometrica = $this->generarTemplateBiometrico($h1, $h2, $h3, $h4, $h5, $h6);

        if ($respuestaBiometrica['success'] === false) {
            return ["error" => $respuestaBiometrica['mensaje']];
        }

        // Armamos el arreglo para mandárselo al Modelo
        $validarformProducto = array(
            "Nombre"                => $Nombre,
            "ApellidoP"             => $ApellidoP,
            "ApellidoM"             => $ApellidoM,
            "Telefono"              => $Telefono,
            "Correo"                => $Correo,
            "Huella"                => $respuestaBiometrica['template'], 
            "Fecha_Ingreso"         => $Fecha,
            "Acepta_WhatsApp"       => $Acepta_WhatsApp,
            "Servicios_IDServicios" => $Servicios_IDServicios,
            "Tipo_Pago"             => $Tipo_Pago,
            
            // 🔥 NUEVOS DATOS PARA EL MODELO
            "MontoTotal"            => $MontoTotal,
            "Servicios_Extra"       => $servicios_extra
        );

        return $validarformProducto;
    }

    private function generarTemplateBiometrico($h1, $h2, $h3, $h4, $h5, $h6) {
        $url = "http://127.0.0.1:5000/crear_template";
        
        $data = [
            'huella_1' => $h1, 'huella_2' => $h2, 'huella_3' => $h3, 
            'huella_4' => $h4, 'huella_5' => $h5, 'huella_6' => $h6
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout de 5 seg por si Python se traba
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $json = json_decode($response, true);
            
            if (isset($json['status']) && $json['status'] == 'success') {
                return ['success' => true, 'template' => $json['template']];
            } else {
                $msg = isset($json['mensaje']) ? $json['mensaje'] : "Error desconocido en motor biométrico";
                return ['success' => false, 'mensaje' => $msg];
            }
        }
        
        return ['success' => false, 'mensaje' => "No se pudo conectar con el servicio biométrico de Python."];
    }

   
   








// 1. Cargar la vista de registro
    public function vistaRegistroHuella() 
    {
        // Usamos Shield para obtener los datos del usuario logueado
        $idUser = auth()->id(); 
        $username = obtener_username();

        $data = [
            'username' => $username,
            'idUser'   => $idUser
        ];

        return view('html/main', $data)
             . view('html/RegistroUsuario') // La vista que crearemos en el paso 2
             . view('html/footer');
    }



   // 2. Guardar la huella en la BD (Usando Template de Python)
    public function guardarHuellaUsuario() 
    {
        // ¡SEGURIDAD! Tomamos el ID directamente de la sesión de Shield
        $idUsuarioLogueado = auth()->id(); 

        if (!$idUsuarioLogueado) {
            return $this->response->setJSON([
                'status'  => 'error', 
                'mensaje' => 'Error: Sesión expirada. Por favor, inicia sesión de nuevo.',
                'token'   => csrf_hash()
            ]);
        }

        // 1. Recibimos las 6 capturas desde el JavaScript
        $h1 = $this->request->getPost("huella_1");
        $h2 = $this->request->getPost("huella_2");
        $h3 = $this->request->getPost("huella_3");
        $h4 = $this->request->getPost("huella_4");
        $h5 = $this->request->getPost("huella_5");
        $h6 = $this->request->getPost("huella_6");

        // Validamos que ninguna venga vacía
        if (empty($h1) || empty($h2) || empty($h3) || empty($h4) || empty($h5) || empty($h6)) {
            return $this->response->setJSON([
                'status'  => 'error', 
                'mensaje' => 'Error: No se completaron las 6 capturas necesarias.',
                'token'   => csrf_hash()
            ]);
        }

        // 2. Llamada a tu microservicio Python para generar el template oficial
        $respuestaBiometrica = $this->generarTemplateBiometrico($h1, $h2, $h3, $h4, $h5, $h6);

        // Si Python devuelve un error (ej. huellas muy diferentes entre sí)
        if ($respuestaBiometrica['success'] === false) {
            return $this->response->setJSON([
                'status'  => 'error', 
                'mensaje' => 'Fallo biométrico: ' . $respuestaBiometrica['mensaje'],
                'token'   => csrf_hash()
            ]);
        }

        // 3. Guardar en la Base de Datos
        $userModel = new UsersModel(); 
        
        // Hacemos el UPDATE usando el template devuelto por Python, NO la huella en bruto
        $guardado = $userModel->update($idUsuarioLogueado, [
            'Huella' => $respuestaBiometrica['template']
        ]);

        if ($guardado) {
            return $this->response->setJSON([
                'status'  => 'success',
                'mensaje' => '¡Tu huella ha sido procesada y vinculada a tu cuenta exitosamente!',
                'token'   => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'mensaje' => 'Error al guardar en la base de datos.',
                'token'   => csrf_hash()
            ]);
        }
    }


    public function recordatoriosMembresia()
{
    $idUser = obtener_username();
    
    // Aquí instancias tu modelo y creas la lógica para traer a los clientes
    // que estén a 3 días o menos de su fecha de corte.
    $registroModel = new RegistroMembresiaModel();
    $clientesProximos = $registroModel->obtenerClientesPorVencer(3,10); 

    $data = [
        'titulo'           => 'Vencimientos | VitalGym',
        'username'         => $idUser,
        'clientesProximos' => $clientesProximos, // <-- Enviamos el arreglo a la vista
        'pager'            => $registroModel->pager // <-- Esto es para la paginación
    ];

    return view('html/main', $data)
         . view('html/RecordatoriosMembresia', $data)
         . view('html/footer');
}


// 2. AGREGA ESTA NUEVA FUNCIÓN en el mismo controlador:
    public function marcarAvisoEnviado()
    {
        $idRegistro = $this->request->getPost('idRegistro');
        
        if ($idRegistro) {
            $registroModel = new RegistroMembresiaModel();
            // Actualizamos la base de datos poniendo el campo en 1
            $registroModel->update($idRegistro, ['Aviso_Enviado' => 1]);
            
            return $this->response->setJSON(['status' => 'success']);
        }
        
        return $this->response->setJSON(['status' => 'error']);
    }



public function panel()
    {
        $membresiaModel = new RegistroMembresiaModel();
        
        // Capturamos lo que el usuario haya escrito en la barra de búsqueda (GET)
        $telefonoBuscar = $this->request->getGet('telefono');
        
        // Llamamos a la función: le pasamos la búsqueda, avisamos desde 5 días antes, y paginamos de 10 en 10
        $clientes = $membresiaModel->obtenerClientesParaRenovacion($telefonoBuscar, 5, 10);
        
        $data = [
            'titulo'   => 'Panel de Renovaciones | VitalGym',
            'username' => obtener_username(),
            'clientes' => $clientes,
            'pager'    => $membresiaModel->pager,
            'busqueda' => $telefonoBuscar // Lo mandamos a la vista para dejarlo escrito en el input
        ];

        return view('html/main', $data)
             . view('html/ListaRenovaciones', $data)
             . view('html/footer');
    }
    

}