<?php

namespace App\Controllers;
use App\Models\ClientesModel;

class Recepcion extends BaseController
{
    // Carga la vista principal
    public function index()
    {

    
        $serviciosModel =  model(\App\Models\Servicios::class);
        // Mandamos los servicios a la vista para llenar el select del modal
        $data['servicios'] = $serviciosModel->where('Catalogo', 'MEMBRESIA')->findAll();
        $data['extras'] = $serviciosModel->where('Catalogo', 'EXTRAS')->findAll();
        
        return view('html/main', $data)
             . view('html/Espera', $data)
             . view('html/footer');
  
    }

public function obtenerPendientesAJAX()
    {
        if ($this->request->isAJAX()) {
            
            // Usamos el Query Builder para hacer un LEFT JOIN con la tabla de membresías
            $db = \Config\Database::connect();
            $builder = $db->table('clientes c');
            
            // Seleccionamos los datos del cliente y el ID de su membresía (si es que ya tiene)
            $builder->select('c.IDClientes, c.Nombre, c.ApellidoP, c.ApellidoM, c.Telefono, c.Firma, rm.idRegistros_Membresia');
            $builder->join('registros_membresia rm', 'rm.Clientes_IDClientes = c.IDClientes', 'left');
            
            // La regla principal: Aún no tienen huella
            $builder->where('c.Huella IS NULL');
            
            // Agrupamos por si acaso hay algún registro duplicado
            $builder->groupBy('c.IDClientes');
            
            $pendientes = $builder->get()->getResultArray();
            
            $data = [];
            foreach ($pendientes as $cliente) {
                
                // --- LA LÓGICA DE LOS BOTONES ---
                if (!empty($cliente['idRegistros_Membresia'])) {
                    // SÍ TIENE MEMBRESÍA (Ya pagó) -> Mostrar botón verde para Enrolar
                    $btnAccion = '<a href="'.base_url('enrolar/'.$cliente['IDClientes']).'" class="btn btn-sm btn-success"><i class="fas fa-fingerprint"></i> Enrolar Huella</a>';
                } else {
                    // NO TIENE MEMBRESÍA (No ha pagado) -> Mostrar botón azul para Procesar Pago
                    $btnAccion = '<button class="btn btn-sm btn-primary" onclick="abrirModalProcesar(' . $cliente['IDClientes'] . ', \'' . addslashes($cliente['Nombre'] . ' ' . $cliente['ApellidoP']) . '\')"><i class="fas fa-check-circle"></i> Procesar</button>';
                }
                
                // Formateamos la firma para mostrar una previsualización pequeña si existe
                $firmaHtml = $cliente['Firma'] ? '<img src="'.base_url($cliente['Firma']).'" height="30" alt="Firma">' : '<span class="badge bg-warning text-dark">Sin firma</span>';

                $data[] = [
                    $cliente['IDClientes'],
                    $cliente['Nombre'] . ' ' . $cliente['ApellidoP'] . ' ' . $cliente['ApellidoM'],
                    $cliente['Telefono'],
                    $firmaHtml,
                    $btnAccion
                ];
            }

            return $this->response->setJSON(['data' => $data]);
        }
    }


 public function guardarPagoEInscripcion()
    {
        if ($this->request->isAJAX()) {
            
            // 1. Recibir los datos principales
            // Usamos 'id_cliente' que es el name="" del input hidden en el HTML
            $idCliente = $this->request->getPost('id_cliente'); 
            $idServicio = $this->request->getPost('Servicios_IDServicios');
            
            // --- VALIDACIÓN DE SEGURIDAD ---
            if (empty($idCliente)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error: El ID del Cliente no llegó al servidor. Revisa el formulario.',
                    'token' => csrf_hash()
                ]);
            }
            if (empty($idServicio)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error: Debes seleccionar un servicio.',
                    'token' => csrf_hash()
                ]);
            }
            // -------------------------------------

            // Recibir el resto de los datos
            $tipoPago = $this->request->getPost('Tipo_Pago');
            $montoTotal = $this->request->getPost('MontoTotal');
            $extrasID = $this->request->getPost('ExtraID'); 
            $extrasCosto = $this->request->getPost('ExtraCosto');

            // 2. Preparar el array para el modelo
            $datos = [
                'IDClientes' => $idCliente,
                'Servicios_IDServicios' => $idServicio,
                'Tipo_Pago' => $tipoPago,
                'MontoTotal' => $montoTotal,
                'ExtraID' => $extrasID,
                'ExtraCosto' => $extrasCosto
            ];

            // 3. Ejecutar la Inserción en el modelo
            $usuarioModel = model(\App\Models\Cliente::class);
            $registroExitoso = $usuarioModel->completarInscripcionExistente($datos);

            // 4. Verificamos si devolvió el idPago (Éxito) o el error_real (Fallo)
            if (isset($registroExitoso['idPago'])) {
                
                // --- PREPARACIÓN DEL TICKET ---
                helper('usuario'); 
                $sucursalActiva = obtener_sucursal_usuario();
                
               // Extraer información base
                $clienteData = $usuarioModel->find($idCliente);
                $serviciosModel = model(\App\Models\Servicios::class);
                $servicioData = $serviciosModel->find($idServicio);
                
                $nombreCompleto = trim($clienteData['Nombre'] . ' ' . $clienteData['ApellidoP'] . ' ' . $clienteData['ApellidoM']);
                $nombreMembresia = $servicioData['NombreMembresia'] ?? $servicioData['Nombre_Servicio'];
                
                // =========================================================
                // LÓGICA DE EXTRAS DESGLOSADOS
                // =========================================================
                $costoExtras = 0;
                $listaExtras = []; // Arreglo para guardar nombres y costos

                if (!empty($extrasID) && is_array($extrasID)) {
                    for ($i = 0; $i < count($extrasID); $i++) {
                        if (!empty($extrasID[$i])) {
                            $costo = floatval($extrasCosto[$i]);
                            $costoExtras += $costo;
                            
                            // Consultamos el nombre del servicio extra
                            $extraData = $serviciosModel->find($extrasID[$i]);
                            $nombreExtra = $extraData['NombreMembresia'] ?? $extraData['Nombre_Servicio'] ?? 'Servicio Extra';
                            
                            $listaExtras[] = [
                                'nombre' => $nombreExtra,
                                'costo'  => $costo
                            ];
                        }
                    }
                }

                // El costo base es el total menos los extras
                $costoBase = $montoTotal - $costoExtras; 
                // =========================================================

           

                // =========================================================
                // PREPARACIÓN DEL PDF Y MENSAJE DE WHATSAPP DESGLOSADO
                // =========================================================
                $urlWhatsApp = null;
                
                if (!empty($clienteData['Telefono']) && isset($clienteData['Acepta_WhatsApp']) && $clienteData['Acepta_WhatsApp'] == 1) {
                    
                    $membresiaModel = model(\App\Models\RegistroMembresiaModel::class);
                    $registroMembresia = $membresiaModel->find($registroExitoso['idRegistroMembresia']);
                    
                    if ($registroMembresia) {
                        $fechaFinFormat = date("d/m/Y", strtotime($registroMembresia['Fecha_Fin']));
                        
                    // 1. GENERAR EL PDF CON DOMPDF
                        $datosPDF = [
                            'sucursal'   => $sucursalActiva ?? 'SUCUR00001', // <--- Agrega esta línea
                            'cliente'    => $nombreCompleto,
                            'membresia'  => $nombreMembresia,
                            'costo_base' => $costoBase,
                            'fecha_fin'  => $fechaFinFormat,
                            'extras'     => $listaExtras,
                            'total'      => $montoTotal
                        ];

                        // Armar el arreglo final para ticket14.php
                        $datosParaTicket = [
                            'sucursal' => $sucursalActiva ?? 'SUCUR0000X', 
                            'nombre' => obtener_username() ?? 'USUARIO', 
                            'cliente' => $nombreCompleto,
                            'cliente_membresia' => $idCliente, 
                            'tipo_visita_membresia' => $nombreMembresia,
                            'costo_base' => $costoBase,                        // <-- NUEVO
                            'fecha_fin' => $fechaFinFormat,                    // <-- NUEVO
                            'extras' => json_encode($listaExtras),             // <-- NUEVO (Lo enviamos empaquetado)
                            'costo_membresia' => $montoTotal
                        ];
                                
                        $htmlPDF = view('html/ReciboPDF', $datosPDF);
                        
                        $dompdf = new \Dompdf\Dompdf();
                        $dompdf->loadHtml($htmlPDF);
                        $dompdf->setPaper('A5', 'portrait');
                        $dompdf->render();
                        $outputPDF = $dompdf->output();
                        
                        // 2. GUARDAR EL PDF EN EL SERVIDOR
                        $nombreArchivo = 'Recibo_' . $idCliente . '_' . time() . '.pdf';
                        $rutaGuardado = FCPATH . 'assets/recibos/' . $nombreArchivo;
                        file_put_contents($rutaGuardado, $outputPDF);
                        
                        $linkRecibo = base_url('assets/recibos/' . $nombreArchivo);

                        // 3. ARMAR EL MENSAJE DE WHATSAPP DESGLOSADO
                        $telefonoLimpio = preg_replace('/[^0-9]/', '', $clienteData['Telefono']);
                        if (strlen($telefonoLimpio) == 10) {
                            $telefonoLimpio = '52' . $telefonoLimpio; 
                        }

                        $mensajeWA = "¡Hola " . trim($clienteData['Nombre']) . "! 🏋️‍♂️\n\n";
                        $mensajeWA .= "Tu pago en *VitalGym* se ha registrado con éxito.\n\n";
                        $mensajeWA .= "📋 *Detalles de tu pago:*\n";
                        $mensajeWA .= "🔹 Membresía: " . $nombreMembresia . " ($" . number_format($costoBase, 2) . ")\n";
                        
                        // Recorremos los extras para ponerlos en el mensaje
                        if (!empty($listaExtras)) {
                            foreach ($listaExtras as $ext) {
                                $mensajeWA .= "🔸 Extra: " . $ext['nombre'] . " ($" . number_format($ext['costo'], 2) . ")\n";
                            }
                        }
                        
                        $mensajeWA .= "\n💵 *Total pagado: $" . number_format($montoTotal, 2) . "*\n";
                        $mensajeWA .= "📅 Vencimiento: *" . $fechaFinFormat . "*\n\n";
                        $mensajeWA .= "📄 *Descarga tu recibo detallado aquí:*\n";
                        $mensajeWA .= $linkRecibo . "\n\n";
                        $mensajeWA .= "¡A darle con todo! 💪";

                        $urlWhatsApp = "https://api.whatsapp.com/send?phone=" . $telefonoLimpio . "&text=" . urlencode($mensajeWA);
                    }
                }
                // =========================================================
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Pago e inscripción registrados correctamente.',
                    'token' => csrf_hash(),
                    'id_pago' => $registroExitoso['idPago'],
                    'valoresdata' => $datosParaTicket,
                    'url_whatsapp' => $urlWhatsApp // <-- Enviamos la URL al JavaScript
                ]);

            } else {
                // Aquí imprimimos el error exacto que nos mandó el modelo si la base de datos falla
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error: ' . ($registroExitoso['error_real'] ?? 'Desconocido'),
                    'token' => csrf_hash()
                ]);
            }
        }   
    }
}