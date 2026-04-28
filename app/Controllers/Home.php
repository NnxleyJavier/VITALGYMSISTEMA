<?php

namespace App\Controllers;


use App\Models\UsersModel;
use App\Models\RegistroMembresiaModel;



class Home extends BaseController
{
    public function index(): string
    {


         $idUser = obtener_username(); // obtengo el id User de la Sesion

        $servicioModel = model(\App\Models\Servicios::class);

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

        $usuarioModel = model(\App\Models\Cliente::class);

        // Inserción en la base de datos

        $registroExitoso = $usuarioModel->registrarClienteCompleto($resultado);
if ($registroExitoso) {
    
    // 1. Cargamos el helper que acabamos de crear
        helper('gym');

        // 2. Obtenemos la sucursal del usuario logueado
        $sucursalActiva = obtener_id_gimnasio();

        
        
        // Armamos el array con las variables EXACTAS que espera tu archivo ticket14.php
        $datosParaTicket = [
            // Si tienes multisycursal en el nuevo sistema, aquí pondrías la variable. Pongo la 1 por defecto.
            'sucursal' => $sucursalActiva ?? 'SUCUR0000X', 
            // Esto lo puedes sacar de la sesión del usuario logueado: session()->get('nombre')
            'nombre' => obtener_username() ?? 'USUARIO', 
            // Concatenamos el nombre completo del cliente
            'cliente' => trim($resultado['Nombre'] . ' ' . $resultado['ApellidoP'] . ' ' . $resultado['ApellidoM']),
            // Si tu modelo te devuelve el ID generado, lo pones aquí. Si no, puedes poner "NUEVO" por ahora.
            'cliente_membresia' => 'NUEVO', 
            'tipo_visita_membresia' => $resultado['Tipo_Membresia_Ticket'],
            'costo_servicio_extra_membresia' => 0, // Aquí sumarías $resultado['Servicios_Extra'] si es necesario
            'costo_membresia' => $resultado['MontoTotal']
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Cliente registrado y huella guardada correctamente.',
            'token' => csrf_hash(),
            'valoresdata' => $datosParaTicket // <-- Mandamos esto al frontend para que arme la URL
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
        $MontoTotal = $this->request->getPost("MontoTotal");
        $servicios_extra = (array) $this->request->getPost("servicios_extra");
        $Acepta_WhatsApp = $this->request->getPost("Acepta_WhatsApp") ? 1 : 0;

        // --- LÓGICA PARA PASE DIARIO ---
        $servicioModel = model(\App\Models\Servicios::class);
        $servicio = $servicioModel->find($Servicios_IDServicios);
        $esPaseDiario = false;
        $tipoMembresiaTicket = 'MENSUALIDAD'; // Valor por defecto para el ticket
        
        if ($servicio) {
             $nombreServicio = strtoupper($servicio['NombreMembresia']);
    
    // Homologamos el nombre para que coincida con lo que espera ticket14.php
            if (stripos($nombreServicio, 'DÍA') !== false || stripos($nombreServicio, 'DIA') !== false) {
                $esPaseDiario = true;
                $tipoMembresiaTicket = 'DIA';
            } elseif (stripos($nombreServicio, 'QUINCENA') !== false) {
                $tipoMembresiaTicket = 'QUINCENA';
            } elseif (stripos($nombreServicio, 'SEMANA') !== false) {
                $tipoMembresiaTicket = 'SEMANA';
            }
                }
        // --- VALIDACIÓN DIFERENCIADA ---
        if ($esPaseDiario) {
            // Para pase diario, solo el nombre es obligatorio, además de los datos del pago.
            if (empty($Nombre) || empty($Servicios_IDServicios) || empty($Tipo_Pago) || empty($MontoTotal)) {
                return ["error" => "Faltan datos obligatorios para el pase diario (Nombre, Membresía, Pago)."];
            }
        } else {
            // Validación original para membresías completas
            $huellasCompletas = !empty($h1) && !empty($h2) && !empty($h3) && !empty($h4) && !empty($h5) && !empty($h6);
            if (empty($Nombre) || empty($ApellidoP) || !$huellasCompletas) {
                return ["error" => "Faltan datos obligatorios (Nombre, Apellido) o no se completaron las 6 capturas de huella."];
            }
        }

        $templateBiometrico = null;
        // Llamada a Python solo si NO es pase diario
        if (!$esPaseDiario) {
            $respuestaBiometrica = $this->generarTemplateBiometrico($h1, $h2, $h3, $h4, $h5, $h6);

            if ($respuestaBiometrica['success'] === false) {
                return ["error" => $respuestaBiometrica['mensaje']];
            }
            $templateBiometrico = $respuestaBiometrica['template'];
        }

        // Armamos el arreglo para mandárselo al Modelo
        $validarformProducto = array(
            "Nombre"                => $Nombre,
            "ApellidoP"             => $ApellidoP, // Puede ir vacío
            "ApellidoM"             => $ApellidoM, // Puede ir vacío
            "Telefono"              => $Telefono, // Puede ir vacío
            "Correo"                => $Correo, // Puede ir vacío
            "Huella"                => $templateBiometrico, // Será NULL para pase diario
            "Fecha_Ingreso"         => $Fecha,
            "Acepta_WhatsApp"       => $Acepta_WhatsApp,
            "Servicios_IDServicios" => $Servicios_IDServicios,
            "Tipo_Pago"             => $Tipo_Pago,
            "MontoTotal"            => $MontoTotal,
            "Servicios_Extra"       => $servicios_extra,
            "Tipo_Membresia_Ticket" => $tipoMembresiaTicket
        );

        return $validarformProducto;
    }

    protected function generarTemplateBiometrico($h1, $h2, $h3, $h4, $h5, $h6) {
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
       $userModel = model(\App\Models\UsersModel::class);
        
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



    public function verMembresias()
    {
        $username = obtener_username();
        $membresiaModel = new RegistroMembresiaModel();

        // 1. Capturamos ambos filtros de la URL (GET) para que funcionen juntos
        // Ejemplo URL: .../servicios?estado=activas&busqueda=951263
        $estado = $this->request->getGet('estado') ?? 'todas';
        $busqueda = $this->request->getGet('busqueda');

        // 2. El modelo aplica el WHERE estado AND (nombre LIKE %...% OR telefono LIKE %...%)
        $membresias = $membresiaModel->obtenerTodasLasMembresias($estado, $busqueda, 15);

        $data = [
            'titulo'     => 'Historial General de Membresías | VitalGym',
            'username'   => $username,
            'membresias' => $membresias,
            'pager'      => $membresiaModel->pager,
            'estado'     => $estado,
            'busqueda'   => $busqueda
        ];

        return view('html/main', $data) . view('html/VerMembresias', $data) . view('html/footer');
    }

    
public function verIngresos()
    {
        $asistenciasModel = new \App\Models\HistorialAccesosModel();
        
        // Capturamos la fecha del calendario (si no hay, usamos HOY por defecto)
        $fechaSeleccionada = $this->request->getGet('fecha') ?? date('Y-m-d');

        // Consultamos la base de datos
        $asistencias = $asistenciasModel->obtenerAsistenciasPorFecha($fechaSeleccionada);

        $data = [
            'titulo'      => 'Control de Asistencias | VitalGym',
            'username'    => obtener_username(),
            'fecha'       => $fechaSeleccionada, // Mandamos la fecha para dejarla pintada en el input
            'asistencias' => $asistencias
        ];

        return view('html/main', $data)
             . view('html/VerIngresos', $data)
             . view('html/footer');
    }

    
public function verAsistencias()
    {
        $asistenciaModel = new \App\Models\AsistenciaChecador();
        $userModel = new \App\Models\UsersModel();

        // --- 1. SI LA PETICIÓN ES POR AJAX (Filtro dinámico) ---
        if ($this->request->isAJAX()) {
            $fechaInicio = $this->request->getPost('fecha_inicio');
            $fechaFin    = $this->request->getPost('fecha_fin');
            $usuarioSeleccionado = $this->request->getPost('usuario');

            $asistencias = $asistenciaModel->obtenerAsistenciasPorRango($fechaInicio, $fechaFin, $usuarioSeleccionado);
            
            // Pre-formateamos los datos en PHP para no complicar el JavaScript
         // Pre-formateamos los datos en PHP para no complicar el JavaScript
            $datosFormateados = [];
            
            // Arreglo para traducir los días al español
            $diasEspanol = [
                'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 
                'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
            ];

            foreach ($asistencias as $a) {
                $entrada = new \DateTime($a['FechaHora_Registro_Entrada']);
                $salida = !empty($a['FechaHora_Registro_Salida']) ? new \DateTime($a['FechaHora_Registro_Salida']) : null;
                
                $horaSalida = $salida ? $salida->format('h:i A') : '<span class="badge bg-warning text-dark p-2" style="border-radius: 8px;">En turno</span>';
                $horasTrabajadas = $salida ? $entrada->diff($salida)->format('%h h %i min') : '<span class="text-muted">-</span>';

                // Obtenemos el nombre del día en inglés y lo traducimos
                $nombreDiaIngles = $entrada->format('l');
                $diaTraducido = $diasEspanol[$nombreDiaIngles];

                $datosFormateados[] = [
                    'username'  => esc($a['username']),
                    'dia'       => $diaTraducido, // NUEVO DATO
                    'fecha'     => $entrada->format('d/m/Y'),
                    'entrada'   => $entrada->format('h:i A'),
                    'salida'    => $horaSalida,
                    'trabajado' => $horasTrabajadas
                ];
            }
            
            return $this->response->setJSON([
                'status' => 'success', 
                'datos'  => $datosFormateados
            ]);
        }

        // --- 2. SI ES LA CARGA INICIAL DE LA PÁGINA (GET) ---
        $fechaInicio = date('Y-m-d', strtotime('monday this week'));
        $fechaFin    = date('Y-m-d', strtotime('sunday this week'));
        $usuarioSeleccionado = 'todos';

        // Traemos todos los usuarios y los registros de la semana actual por defecto
        $usuarios = $userModel->findAll();
        $asistencias = $asistenciaModel->obtenerAsistenciasPorRango($fechaInicio, $fechaFin, $usuarioSeleccionado);

        $data = [
            'titulo'              => 'Control de Asistencia | VitalGym',
            'username'            => obtener_username(), 
            'asistencias'         => $asistencias,
            'fechaInicio'         => $fechaInicio,
            'fechaFin'            => $fechaFin,
            'usuarios'            => $usuarios,
            'usuarioSeleccionado' => $usuarioSeleccionado
        ];

        return view('html/main', $data)
             . view('html/AsistenciasView', $data)
             . view('html/footer');
    }

    public function FirmarResponsiva()
    {
        $data = [
            'titulo'   => 'Carta Responsiva | VitalGym',
            'username' => obtener_username()
        ];

        return view('html/main', $data)
             . view('html/CartaResponsiva', $data)
             . view('html/footer');
    }
    

    public function GuardarResponsiva()
    {
        if ($this->request->isAJAX()) {
            
            $clientesModel =  model(\App\Models\Cliente::class);

            // 1. Recibir los datos del formulario
            $nombre = $this->request->getPost('nombre');
            $apellido_p = $this->request->getPost('apellido_p');
            $apellido_m = $this->request->getPost('apellido_m');
            $telefono = $this->request->getPost('telefono');
            $correo = $this->request->getPost('correo');
            $TelefonoEmergencia = $this->request->getPost('telefono_emergencia');
            $ContactoEmergencia = $this->request->getPost('contacto_emergencia');
            $acepta_wa = $this->request->getPost('recordatorio_wa') === 'SI' ? 1 : 0;
            
            // 2. Procesar la firma en Base64
            $firma_base64 = $this->request->getPost('firma_base64');
            $ruta_firma = null;

            if ($firma_base64) {
                // Quitar la cabecera "data:image/png;base64," para obtener solo el código
                $image_parts = explode(";base64,", $firma_base64);
                $image_base64 = base64_decode($image_parts[1]);
                
                // Crear un nombre único para el archivo
                $nombre_archivo = 'firma_' . time() . '_' . rand(100, 999) . '.png';
                
                // Definir la ruta donde se guardará (ej. public/uploads/firmas/)
                // Asegúrate de crear esta carpeta y darle permisos de escritura
                $ruta_guardado = FCPATH . 'assets/firmas/' . $nombre_archivo;
                
                // Guardar el archivo físicamente en el servidor
                file_put_contents($ruta_guardado, $image_base64);
                
                // Guardar solo el nombre o ruta relativa para la BD
                $ruta_firma = 'assets/firmas/' . $nombre_archivo;
            }

            // 3. Preparar los datos para insertar en la tabla clientes
            $datosCliente = [
                'Nombre' => $nombre,
                'ApellidoP' => $apellido_p,
                'ApellidoM' => $apellido_m,
                'Telefono' => $telefono,
                'Correo' => $correo,
                'Acepta_WhatsApp' => $acepta_wa,
                'Firma' => $ruta_firma,
                'Fecha_Ingreso' => date('Y-m-d'),
                'Telefono_Emergencia' => $TelefonoEmergencia,
                'Contacto_Emergencia' => $ContactoEmergencia,    
                'Huella' => null // Queda nula hasta que pasen a recepción
            ];

            // 4. Insertar en la BD
            if ($clientesModel->insert($datosCliente)) {
                return $this->response->setJSON(['status' => 'success', 'mensaje' => 'Pre-registro completado con éxito.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'mensaje' => 'Error al guardar en la base de datos.']);
            }
        }
    }

    

}