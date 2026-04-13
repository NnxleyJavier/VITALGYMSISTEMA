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
        // Cargamos el helper para la sucursal y el username
        helper('usuario');

        // 1. Recogemos los datos base
        $idCliente  = $this->request->getPost('cliente_id');
        $idServicio = $this->request->getPost('servicio_id');
        $montoTotal = $this->request->getPost('monto_total');

        $datosRenovacion = [
            'Clientes_IDClientes'   => $idCliente,
            'Servicios_IDServicios' => $idServicio,
            'Extras'                => $this->request->getPost('extras'),
            'MontoTotal'            => $montoTotal,
            'Tipo_Pago'             => $this->request->getPost('tipo_pago')
        ];

        // 2. Transacción en BD
        $membresiaModel = new RegistroMembresiaModel();
        $resultado = $membresiaModel->renovarMembresiaTransaccion($datosRenovacion);

        // 3. Respuesta JSON
        if ($resultado['success']) {
            
            // --- INICIO PREPARACIÓN DE TICKET ---
            $clienteModel  = new Cliente();
            $servicioModel = new Servicios();

            $infoCliente  = $clienteModel->find($idCliente);
            $infoServicio = $servicioModel->find($idServicio);

            $nombreCliente = $infoCliente ? trim($infoCliente['Nombre'] . ' ' . $infoCliente['ApellidoP']) : 'Socio';
            
            // Homologar tipo de servicio para ticket15.php
            $tipoMembresiaTicket = 'MENSUALIDAD';
            if ($infoServicio) {
                $nombreServicio = strtoupper($infoServicio['NombreMembresia']);
                if (stripos($nombreServicio, 'DÍA') !== false || stripos($nombreServicio, 'DIA') !== false) {
                    $tipoMembresiaTicket = 'DIA';
                } elseif (stripos($nombreServicio, 'QUINCENA') !== false) {
                    $tipoMembresiaTicket = 'QUINCENA';
                } elseif (stripos($nombreServicio, 'SEMANA') !== false) {
                    $tipoMembresiaTicket = 'SEMANA';
                }
            }

            $datosParaTicket = [
                'sucursal'              => obtener_sucursal_usuario(),
                'nombre'                => obtener_username() ?: 'Cajero',
                'cliente'               => $nombreCliente,
                'cliente_membresia'     => $idCliente, // Enviamos el ID real como número de socio
                'tipo_visita_membresia' => $tipoMembresiaTicket,
                'costo_membresia'       => $montoTotal
            ];
            // --- FIN PREPARACIÓN DE TICKET ---

            $fechaFinFormat = date('d/m/Y', strtotime($resultado['fecha_fin']));
            return $this->response->setJSON([
                'status'      => 'success',
                'mensaje'     => '¡Renovación exitosa! La nueva fecha de corte es el ' . $fechaFinFormat,
                'token'       => csrf_hash(),
                'valoresdata' => $datosParaTicket // Mandamos los datos para imprimir
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'mensaje' => $resultado['mensaje'] ?? 'Error desconocido al renovar.',
                'token'   => csrf_hash()
            ]);
        }
    }

}