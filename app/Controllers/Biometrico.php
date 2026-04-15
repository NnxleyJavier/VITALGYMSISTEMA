<?php

namespace App\Controllers;
use App\Models\Cliente; // Asegúrate de importar el modelo de clientes

class Biometrico extends BaseController
{
    // 1. Mostrar la vista de enrolamiento para el CLIENTE
    public function enrolar($idCliente)
    {
        $clienteModel = new Cliente();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->to(base_url('recepcion'))->with('error', 'Cliente no encontrado.');
        }

        $data = [
            'cliente' => $cliente
        ];

        return view('html/main', $data)
             . view('html/RegistroHuellaCliente', $data)
             . view('html/footer');
    }

    // 2. Guardar la huella en la BD (Tabla clientes)
    public function guardarHuellaCliente()
    {
        if ($this->request->isAJAX()) {
            
            $idCliente = $this->request->getPost("id_cliente");

            // Recibimos las 6 capturas obligatorias
            $h1 = $this->request->getPost("huella_1");
            $h2 = $this->request->getPost("huella_2");
            $h3 = $this->request->getPost("huella_3");
            $h4 = $this->request->getPost("huella_4");
            $h5 = $this->request->getPost("huella_5");
            $h6 = $this->request->getPost("huella_6");

            // Validamos que ninguna venga vacía
            if (empty($idCliente) || empty($h1) || empty($h2) || empty($h3) || empty($h4) || empty($h5) || empty($h6)) {
                return $this->response->setJSON([
                    'status'  => 'error', 
                    'mensaje' => 'Error: No se completaron las 6 capturas necesarias.',
                    'token'   => csrf_hash()
                ]);
            }

            // Llamada al microservicio Python
            $respuestaBiometrica = $this->generarTemplateBiometrico($h1, $h2, $h3, $h4, $h5, $h6);

            if ($respuestaBiometrica['success'] === false) {
                return $this->response->setJSON([
                    'status'  => 'error', 
                    'mensaje' => 'Fallo biométrico: ' . $respuestaBiometrica['mensaje'],
                    'token'   => csrf_hash()
                ]);
            }

            // Guardar en la Base de Datos de CLIENTES
            $clienteModel = new Cliente();
            
            $guardado = $clienteModel->update($idCliente, [
                'Huella' => $respuestaBiometrica['template']
            ]);

            if ($guardado) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'mensaje' => '¡Huella procesada y vinculada al cliente exitosamente!',
                    'token'   => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'mensaje' => 'Error al guardar la huella en la base de datos.',
                    'token'   => csrf_hash()
                ]);
            }
        }
    }

    // Tu función original intacta
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
        
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
}