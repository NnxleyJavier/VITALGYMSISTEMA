<?php

namespace App\Controllers;

use App\Models\Servicios;
use App\Models\Cliente;
use App\Models\RegistroMembresiaModel;

class Renovaciones extends BaseController
{
    // Carga la pantalla de renovación para un cliente específico
    public function index($idCliente)
    {
        $clienteModel = new Cliente();
        $servicioModel = new Servicios();

        // Buscamos al cliente
        $cliente = $clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to(base_url('/renovaciones'))->with('error', 'Cliente no encontrado.');
        }

        // Traemos catálogos
        $membresias = $servicioModel->where('Catalogo', 'MEMBRESIA')->findAll();
        $extras = $servicioModel->where('Catalogo', 'EXTRAS')->findAll();

        $data = [
            'titulo'     => 'Renovar Membresía | VitalGym',
            'username'   => obtener_username(),
            'cliente'    => $cliente,
            'membresias' => $membresias,
            'extras'     => $extras
        ];

        return view('html/main', $data)
             . view('html/RenovarMembresia', $data)
             . view('html/footer');
    }

    // Procesa el formulario
    public function guardarRenovacion()
    {
        // Recogemos todos los datos del formulario
        $datosRenovacion = [
            'Clientes_IDClientes'   => $this->request->getPost('cliente_id'),
            'Servicios_IDServicios' => $this->request->getPost('servicio_id'),
            'Extras'                => $this->request->getPost('extras'), // Array de checkboxes
            'MontoTotal'            => $this->request->getPost('monto_total'),
            'Tipo_Pago'             => $this->request->getPost('tipo_pago')
        ];

        $membresiaModel = new RegistroMembresiaModel();
        $resultado = $membresiaModel->renovarMembresiaTransaccion($datosRenovacion);

        if ($resultado['success']) {
            $fechaFinFormat = date('d/m/Y', strtotime($resultado['fecha_fin']));
            return redirect()->to(base_url('/renovaciones/panel'))->with('success', '¡Renovación exitosa! Vence el ' . $fechaFinFormat);
        } else {
            return redirect()->back()->with('error', 'Error: ' . $resultado['error']);
        }
    }

public function guardarRenovacionAjax()
    {
        // 1. Recogemos todos los datos del formulario (enviados por AJAX)
        $datosRenovacion = [
            'Clientes_IDClientes'   => $this->request->getPost('cliente_id'),
            'Servicios_IDServicios' => $this->request->getPost('servicio_id'),
            'Extras'                => $this->request->getPost('extras'), // Array de checkboxes (puede venir vacío)
            'MontoTotal'            => $this->request->getPost('monto_total'),
            'Tipo_Pago'             => $this->request->getPost('tipo_pago')
        ];

        // 2. Llamamos al modelo que hace la transacción segura
        $membresiaModel = new RegistroMembresiaModel();
        $resultado = $membresiaModel->renovarMembresiaTransaccion($datosRenovacion);

        // 3. Respondemos en formato JSON
        if ($resultado['success']) {
            $fechaFinFormat = date('d/m/Y', strtotime($resultado['fecha_fin']));
            return $this->response->setJSON([
                'status'  => 'success',
                'mensaje' => '¡Renovación exitosa! La nueva fecha de corte es el ' . $fechaFinFormat,
                'token'   => csrf_hash() // Refrescamos el token por seguridad
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'mensaje' => 'Error al renovar: ' . $resultado['error'],
                'token'   => csrf_hash()
            ]);
        }
    }

}