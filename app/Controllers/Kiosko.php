<?php namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Cliente;
use App\Models\RegistroMembresiaModel;
use App\Models\RegistrohistorialAccesos;
use App\Models\UsersModel;
use App\Models\AsistenciaChecador;


class Kiosko extends Controller {//el controlador tenia en principio extends Controller, pero lo cambié a BaseController para poder usar la función obtener_username() que definimos ahí y así obtener el username de la sesión sin tener que repetir código.

 



   public function verificarHuella() {
    $huellaRecibida = $this->request->getPost('huella_feature_set');
    
    if (!$huellaRecibida) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió huella']);
    }

    $clienteModel = new Cliente();
    $RegistroMembresiaModel = new RegistroMembresiaModel(); // Cargamos el modelo de membresías para consultar fechas
    
    // OPTIMIZACIÓN: Solo traer usuarios que tengan huella registrada y membresía activa 

        $candidatos = $clienteModel->ObtenerclientesActivos(); // Usamos el método que ya definimos en Cliente.php

    foreach ($candidatos as $candidato) {
        // Llamada 1 a 1 al Python "Salvavidas"
        $resultado = $this->consultarMicroservicio($huellaRecibida, $candidato['Huella']);
        
        if (isset($resultado['match']) && $resultado['match'] === true) {

            $IdCandidato= $candidato['IDClientes'] ; // Aquí empezamos a vincular las fechas o consultar por ID

            $ConsultarFecha = $RegistroMembresiaModel->obtenerFechaMembresia($IdCandidato); // Método que debes crear en tu modelo para obtener la fecha de fin de membresía;
            
            $diasRestantes = $this->calcularDiasRestantes($ConsultarFecha[0]['Fecha_Fin'],$IdCandidato); // Método que debes crear para calcular días restantes y preparar la respuesta JSON


            // ¡ENCONTRADO! Devolvemos los datos del usuario
            return $this->response->setJSON([
                'status' => 'success',
                'nombre' => $candidato['Nombre'],
                'apellido_paterno' => $candidato['ApellidoP'],
                'dias_restantes' => $diasRestantes['dias_restantes'], // Esto es lo que calculamos en el método calcularDiasRestantes
                'token' => csrf_hash() // Refrescar token
            ]);
        }
    }
    
    return $this->response->setJSON([
        'status' => 'error', 
        'message' => 'Huella no encontrada',
        'token' => csrf_hash()
    ]);
}





private function consultarMicroservicio($huellaNueva, $huellaBD) {
    // Usamos el endpoint /verificar que YA TIENES en tu app.py Salvavidas
    $url = "http://127.0.0.1:5000/verificar";
    
    $data = [
        'huella_nueva' => $huellaNueva, // Esto es el JSON String que mandó el JS
        'huella_bd'    => $huellaBD
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    // Timeout corto (1 segundo) para que si Python falla, no congele todo el bucle
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return ['match' => false];
}


private function calcularDiasRestantes($fechaFin,$IdCliente) {

      $HistorialAccesos = new RegistrohistorialAccesos(); // Usamos el modelo correcto para historial de accesos
  // 1. EXTRAER LA FECHA DE FIN
     

        // 2. CREAR OBJETOS DE TIEMPO (Ajustado a hora de México)
        // Nota: Si no pusiste el 'use CodeIgniter\I18n\Time;' arriba, usamos la ruta completa
        $hoy      = \CodeIgniter\I18n\Time::now('America/Mexico_City');
        $fechaFin = \CodeIgniter\I18n\Time::parse($fechaFin, 'America/Mexico_City');

        // TRUCO VITAL: Igualamos las horas a la medianoche. 
        // Así evitamos que si viene a las 11 PM diga que le queda "cero" días por unas horas.
        $hoy      = $hoy->setHour(0)->setMinute(0)->setSecond(0);
        $fechaFin = $fechaFin->setHour(0)->setMinute(0)->setSecond(0);

        // 3. CALCULAR DIFERENCIA EXACTA EN DÍAS
        $diferencia    = $hoy->difference($fechaFin);
        $diasRestantes = (int) $diferencia->getDays();

        // 4. PREPARAR VARIABLES PARA EL HISTORIAL Y RESPUESTA
        $estatusAcceso = '';
        $motivoAcceso  = '';
        $respuestaJSON = [];
        $FechaHora_Acceso = date('Y-m-d H:i:s'); // Hora actual para guardar en historial

        if ($diasRestantes >= 0) {
            // --- ESCENARIO A: TIENE DÍAS A FAVOR (ACTIVO) ---
            $estatusAcceso = "1";
            $motivoAcceso  = "Acceso correcto. Días restantes: " . $diasRestantes;
            
            $respuestaJSON = [
                'Clientes_IDClientes ' => $IdCliente,
                'FechaHora_Acceso'      => $FechaHora_Acceso,
                'dias_restantes' => $diasRestantes,
                'Estatus_idEstatus'          => 1, // 1 para permitido
                'Motivo'           => $motivoAcceso,
            ];
       
            // 5. GUARDAR EN EL HISTORIAL (Fuera del if para que guarde ambos casos)
    // Este arreglo SÍ coincide exactamente con los campos de tu tabla
    $datosGuardar = [
        'Clientes_IDClientes' => $IdCliente,
        'FechaHora_Acceso'    => $FechaHora_Acceso,
        'Estatus_idEstatus'      => $estatusAcceso,
        'Motivo'              => $motivoAcceso
    ];


      $HistorialAccesos->insert($datosGuardar);


        } else {
            // --- ESCENARIO B: DÍAS NEGATIVOS (VENCIDO) ---
            $diasVencidos  = abs($diasRestantes); // Convertimos el negativo a positivo (ej. -3 a 3)
            $estatusAcceso = 2;
            $motivoAcceso  = "Membresía vencida hace {$diasVencidos} días.";
            
            $respuestaJSON = [
                'Clientes_IDClientes ' => $IdCliente,
                'FechaHora_Acceso'      => $FechaHora_Acceso,
                'dias_restantes' => $diasRestantes,
                'Estatus_idEstatus'          => 2, // 2 para denegado
                'Motivo'           => $motivoAcceso,
            ];
        }


        // 5. GUARDAR EN HISTORIAL DE ACCESOS (SI QUIERES)



        
      return $respuestaJSON;
}







   public function asistencia() {
        $idUser= obtener_username();; // obtengo el id User de la Sesion
        $data = [
            'username' => $idUser
            ];

         return view('html/main', $data)
             . view('html/Asistencia')
             . view('html/footer');
    
    }



 public function index() {
        $idUser= obtener_username(); // obtengo el id User de la Sesion
        $data = [
            'username' => $idUser
            ];

         return view('html/main', $data)
             . view('html/AccesoClientes')
             . view('html/footer');
    
    }




  public function RegistroddeAsistencia()
{
    $huellaRecibida = $this->request->getPost('huella_feature_set');

    if (!$huellaRecibida) {
        return $this->response->setJSON([
            'status' => 'error', 
            'mensaje' => 'No se recibió huella',
            'token' => csrf_hash()
        ]);
    }

    $userModel = new UsersModel(); 
    $usuarios = $userModel->findAll();

    foreach ($usuarios as $usuario) {
        $huellaBD = $usuario['Huella'];

        if (!empty($huellaBD)) {
            $resultado = $this->consultarMicroservicio($huellaRecibida, $huellaBD);

            if (isset($resultado['match']) && $resultado['match'] === true) {
                
                $idUsuario = $usuario['id'];
                $nombreUsuario = $usuario['username'];
                $ahora = date('Y-m-d H:i:s');
                $tiempoActual = time(); // Para calcular los segundos matemáticamente
                
                $asistenciaModel = new AsistenciaChecador();
                
                // 1. Buscar el último registro de este usuario
                // Asumo que tu tabla tiene una llave primaria llamada 'id'. Si se llama distinto, cámbiala en el update más abajo.
                $ultimoRegistro = $asistenciaModel->where('users_id', $idUsuario)
                                                  ->orderBy('FechaHora_Registro_Entrada', 'DESC')
                                                  ->first();

                $tipoRegistro = ''; // Para avisarle al JS si fue entrada o salida

                if ($ultimoRegistro) {
                    // Ver si el último movimiento fue una entrada o una salida para calcular el tiempo
                    $fechaUltimoMov = !empty($ultimoRegistro['FechaHora_Registro_Salida']) 
                                        ? $ultimoRegistro['FechaHora_Registro_Salida'] 
                                        : $ultimoRegistro['FechaHora_Registro_Entrada'];
                    
                    $tiempoUltimo = strtotime($fechaUltimoMov);
                    $diferenciaSegundos = $tiempoActual - $tiempoUltimo;

                    // --- VALIDACIÓN ANTI-REBOTES (1 minuto = 60 segundos) ---
                    if ($diferenciaSegundos < 60) {
                        return $this->response->setJSON([
                            'status'  => 'info', 
                            'mensaje' => 'Registro pausado: Ya registraste un movimiento hace unos segundos.',
                            'nombre'  => $nombreUsuario,
                            'token'   => csrf_hash()
                        ]);
                    }

                    // --- LÓGICA DE ENTRADA / SALIDA ---
                    // Si el último registro tiene Entrada pero NO tiene Salida -> Toca registrar Salida
                    if (empty($ultimoRegistro['FechaHora_Registro_Salida'])) {
                        // OJO: Aquí uso $ultimoRegistro['idAsistenciaChecador']. Cambia 'idAsistenciaChecador' por el nombre de tu Primary Key si es diferente (ej. 'id')
                        $asistenciaModel->update($ultimoRegistro['idAsistenciaChecador'], [
                            'FechaHora_Registro_Salida' => $ahora
                        ]);
                        $tipoRegistro = 'Salida';
                    } else {
                        // Si ya tiene salida, significa que está entrando de nuevo (nuevo turno o regresó de comer)
                        $asistenciaModel->insert([
                            'users_id' => $idUsuario,
                            'FechaHora_Registro_Entrada' => $ahora,
                            'FechaHora_Registro_Salida'  => null
                        ]);
                        $tipoRegistro = 'Entrada';
                    }
                } else {
                    // No hay historial en lo absoluto, es su primera Entrada
                    $asistenciaModel->insert([
                        'users_id' => $idUsuario,
                        'FechaHora_Registro_Entrada' => $ahora,
                        'FechaHora_Registro_Salida'  => null
                    ]);
                    $tipoRegistro = 'Entrada';
                }

                // Devolver éxito al frontend
                return $this->response->setJSON([
                    'status'     => 'success',
                    'nombre'     => $nombreUsuario,
                    'rolUsuario' => 'Usuario', 
                    'tipo'       => $tipoRegistro, // Mandamos "Entrada" o "Salida"
                    'hora'       => date('h:i A', strtotime($ahora)), // Hora formateada para mostrar en pantalla
                    'token'      => csrf_hash()
                ]);
            }
        }
    }

    return $this->response->setJSON([
        'status'  => 'error',
        'mensaje' => 'Huella no encontrada o usuario no registrado.',
        'token'   => csrf_hash()
    ]);
}











}