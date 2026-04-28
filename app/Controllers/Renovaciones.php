<?php

namespace App\Controllers;

use App\Models\Servicios;
use App\Models\Cliente;
use App\Models\RegistroMembresiaModel;

class Renovaciones extends BaseController
{
// 1. Carga la pantalla de renovación para un cliente específico
    public function index($idCliente)
    {
        $clienteModel = new \App\Models\Cliente();
        
        // Instanciamos el modelo de servicios
        $servicioModel = model(\App\Models\Servicios::class);

        // Buscamos al cliente
        $cliente = $clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to(base_url('/renovaciones'))->with('error', 'Cliente no encontrado.');
        }

        // =========================================================
        // FILTRADO DE SERVICIOS POR SUCURSAL
        // =========================================================
        $membresias = $servicioModel->obtenerServiciosPorSucursal('MEMBRESIA');
        $extras     = $servicioModel->obtenerServiciosPorSucursal('EXTRAS');

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
        
        // El modelo hace todo el trabajo pesado y guarda el pago y la membresía
        $resultado = $membresiaModel->renovarMembresiaTransaccion($datosRenovacion);

        if ($resultado['success']) {
            
            // =================================================================
            // AQUÍ INICIA EL PASO 4: CREAR SOLICITUD DE PRECIO AMIGO EN RENOVACIÓN
            // =================================================================
            $precioAmigo = $this->request->getPost('precio_amigo');
            $motivoAmigo = $this->request->getPost('motivo_amigo');
            
            // Extraemos el idPago que tu modelo acaba de insertar
            $idPagoGenerado = isset($resultado['idPago']) ? $resultado['idPago'] : null;

            if (!empty($precioAmigo) && !empty($motivoAmigo) && $idPagoGenerado) {
                $solicitudesModel = new \App\Models\SolicitudesPrecioAmigoModel();
                $solicitudesModel->insert([
                    'Clientes_IDClientes'   => $this->request->getPost('cliente_id'),
                    'Servicios_IDServicios' => $this->request->getPost('servicio_id'), 
                    'Pago_idPago'           => $idPagoGenerado, // <-- Vinculamos la solicitud al pago original
                    'precio_solicitado'     => $precioAmigo,
                    'motivo'                => $motivoAmigo,
                    'estado'                => 'Pendiente',
                    'users_id'              => auth()->user()->id
                ]);
            }
            // =================================================================
            // AQUÍ TERMINA EL PASO 4
            // =================================================================

            $fechaFinFormat = date('d/m/Y', strtotime($resultado['fecha_fin']));
            return redirect()->to(base_url('/panel'))->with('success', '¡Renovación exitosa! Vence el ' . $fechaFinFormat);
        } else {
            return redirect()->back()->with('error', 'Error: ' . $resultado['error']);
        }
    }

public function guardarRenovacionAjax()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'Petición no válida.']);
        }

        $idCliente = $this->request->getPost('cliente_id');
        $idServicio = $this->request->getPost('servicio_id');
        $montoTotal = $this->request->getPost('monto_total');
        $extrasIds = $this->request->getPost('extras');

        // Recogemos todos los datos del formulario
        $datosRenovacion = [
            'Clientes_IDClientes'   => $idCliente,
            'Servicios_IDServicios' => $idServicio,
            'Extras'                => $extrasIds,
            'MontoTotal'            => $montoTotal,
            'Tipo_Pago'             => $this->request->getPost('tipo_pago')
        ];

        $membresiaModel = new \App\Models\RegistroMembresiaModel();
        $resultado = $membresiaModel->renovarMembresiaTransaccion($datosRenovacion);

        if (isset($resultado['success']) && $resultado['success']) {
            
            // =================================================================
            // CREAR SOLICITUD DE PRECIO AMIGO EN RENOVACIÓN Y PREPARAR NOTA
            // =================================================================
            $precioAmigo = $this->request->getPost('precio_amigo');
            $motivoAmigo = $this->request->getPost('motivo_amigo');
            
            $idPagoGenerado = $resultado['idPago'] ?? null;
            
            // Variables vacías por defecto (Si no hay descuento, no se imprime nada extra)
            $notaPDFWhatsApp = ""; 
            $notaTicketTermico = "";

            if (!empty($precioAmigo) && !empty($motivoAmigo) && $idPagoGenerado) {
                $solicitudesModel = new \App\Models\SolicitudesPrecioAmigoModel();
                $solicitudesModel->insert([
                    'Clientes_IDClientes'   => $idCliente,
                    'Servicios_IDServicios' => $idServicio, 
                    'Pago_idPago'           => $idPagoGenerado, 
                    'precio_solicitado'     => $precioAmigo,
                    'motivo'                => $motivoAmigo,
                    'estado'                => 'Pendiente',
                    'users_id'              => auth()->user()->id
                ]);
                
                // Si sí solicitó descuento, llenamos las notas
                $notaPDFWhatsApp = " (Precio Amigo Solicitado: $" . number_format($precioAmigo, 2) . ")";
                $notaTicketTermico = " (P.Amigo)"; // Más cortito para que no se deforme el ticket de la impresora
            }
            // =================================================================

            // --- PREPARACIÓN DE DATOS PARA EL TICKET FÍSICO ---
            $clienteModel = new \App\Models\Cliente();
            $servicioModel = model(\App\Models\Servicios::class);
            
            $clienteData = $clienteModel->find($idCliente);
            $nombreCliente = trim($clienteData['Nombre'] . ' ' . $clienteData['ApellidoP']);
            
            $servicioData = $servicioModel->find($idServicio);
            
            // Le pegamos la nota al nombre para el PDF y WhatsApp
            $nombreServicio = ($servicioData['NombreMembresia'] ?? 'Membresía') . $notaPDFWhatsApp;
            
            $tipoMembresiaTicket = 'MES';
            if (stripos($servicioData['NombreMembresia'] ?? '', 'VISITA') !== false) {
                $tipoMembresiaTicket = 'VISITA';
            } elseif (stripos($servicioData['NombreMembresia'] ?? '', 'QUINCENA') !== false) {
                $tipoMembresiaTicket = 'QUINCENA';
            } elseif (stripos($servicioData['NombreMembresia'] ?? '', 'SEMANA') !== false) {
                $tipoMembresiaTicket = 'SEMANA';
            }
            
            // Le pegamos la nota cortita al nombre del ticket térmico
            $tipoMembresiaTicket .= $notaTicketTermico;

            helper('gym'); 
            $sucursalID = obtener_id_gimnasio() ?? 'SUCUR0001';
            
            $datosParaTicket = [
                'sucursal'              => $sucursalID,
                'nombre'                => obtener_username() ?: 'Cajero',
                'cliente'               => $nombreCliente,
                'cliente_membresia'     => $idCliente,
                'tipo_visita_membresia' => $tipoMembresiaTicket, // Ahora dirá ej: "MES (P.Amigo)"
                'costo_membresia'       => $montoTotal
            ];

            $fechaFinFormat = date('d/m/Y', strtotime($resultado['fecha_fin']));

            // =========================================================
            // LÓGICA DE EXTRAS DESGLOSADOS PARA EL PDF
            // =========================================================
            $costoExtras = 0;
            $listaExtras = [];

            if (!empty($extrasIds) && is_array($extrasIds)) {
                foreach ($extrasIds as $idExt) {
                    $extraData = $servicioModel->find($idExt);
                    if ($extraData) {
                        $costo = floatval($extraData['Costo']);
                        $costoExtras += $costo;
                        $listaExtras[] = [
                            'nombre' => $extraData['NombreMembresia'] ?? $extraData['Nombre_Servicio'] ?? 'Servicio Extra',
                            'costo'  => $costo
                        ];
                    }
                }
            }

            $costoBase = $montoTotal - $costoExtras; 

            // =========================================================
            // PREPARACIÓN DEL PDF Y MENSAJE DE WHATSAPP DESGLOSADO
            // =========================================================
            $urlWhatsApp = null;
            
            if (!empty($clienteData['Telefono']) && isset($clienteData['Acepta_WhatsApp']) && $clienteData['Acepta_WhatsApp'] == 1) {
                
                // 1. GENERAR EL PDF CON DOMPDF
                $datosPDF = [
                    'sucursal'   => $sucursalID, 
                    'cliente'    => $nombreCliente,
                    'membresia'  => $nombreServicio, // Imprimirá ej: "Mensualidad (Precio Amigo Solicitado: $250.00)"
                    'costo_base' => $costoBase,
                    'fecha_fin'  => $fechaFinFormat,
                    'extras'     => $listaExtras,
                    'total'      => $montoTotal
                ];
                        
                $htmlPDF = view('html/ReciboPDF', $datosPDF);
                
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($htmlPDF);
                $dompdf->setPaper('A5', 'portrait');
                $dompdf->render();
                $outputPDF = $dompdf->output();
                
                // 2. GUARDAR EL PDF EN EL SERVIDOR
                $nombreArchivo = 'ReciboRenovacion_' . $idCliente . '_' . time() . '.pdf';
                $rutaGuardado = FCPATH . 'assets/recibos/' . $nombreArchivo;
                file_put_contents($rutaGuardado, $outputPDF);
                
                $linkRecibo = base_url('assets/recibos/' . $nombreArchivo);

                // 3. ARMAR EL MENSAJE DE WHATSAPP
                $telefonoLimpio = preg_replace('/[^0-9]/', '', $clienteData['Telefono']);
                if (strlen($telefonoLimpio) == 10) {
                    $telefonoLimpio = '52' . $telefonoLimpio; 
                }

                $mensajeWA = "¡Hola " . trim($clienteData['Nombre']) . "! 🏋️‍♂️\n\n";
                $mensajeWA .= "Tu renovación en *VitalGym* se ha registrado con éxito.\n\n";
                $mensajeWA .= "📋 *Detalles de tu pago:*\n";
                $mensajeWA .= "🔹 Membresía: " . $nombreServicio . " ($" . number_format($costoBase, 2) . ")\n";
                
                if (!empty($listaExtras)) {
                    foreach ($listaExtras as $ext) {
                        $mensajeWA .= "🔸 Extra: " . $ext['nombre'] . " ($" . number_format($ext['costo'], 2) . ")\n";
                    }
                }
                
                $mensajeWA .= "\n💵 *Total pagado: $" . number_format($montoTotal, 2) . "*\n";
                $mensajeWA .= "📅 Nueva fecha de corte: *" . $fechaFinFormat . "*\n\n";
                $mensajeWA .= "📄 *Descarga tu recibo detallado aquí:*\n";
                $mensajeWA .= $linkRecibo . "\n\n";
                $mensajeWA .= "¡A darle con todo! 💪";

                $urlWhatsApp = "https://api.whatsapp.com/send?phone=" . $telefonoLimpio . "&text=" . urlencode($mensajeWA);
            }
            
            // RETORNAMOS JSON
            return $this->response->setJSON([
                'status'       => 'success',
                'mensaje'      => '¡Renovación exitosa! La nueva fecha de corte es el ' . $fechaFinFormat,
                'token'        => csrf_hash(),
                'valoresdata'  => $datosParaTicket, 
                'url_whatsapp' => $urlWhatsApp 
            ]);

        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'mensaje' => $resultado['error'] ?? 'Error desconocido al renovar.',
                'token'   => csrf_hash()
            ]);
        }
    }
}