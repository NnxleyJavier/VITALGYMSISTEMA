<?php

namespace App\Controllers;

use App\Models\PagoModel;
use App\Models\VentasproductosModel;
use App\Models\RegistroMembresiaModel;

class ReporteRango extends BaseController
{
    public function index()
    {
        helper(['gym', 'usuario']);
        $idGymActual = obtener_id_gimnasio();
        
        $usuarioLogueado = auth()->user();
        $esSuperAdmin = $usuarioLogueado->inGroup('superadmin'); 
        $idUsuarioFiltro = $esSuperAdmin ? null : $usuarioLogueado->id;

        // Captura de fechas seguras
        $fechaInicio = $this->request->getGet('fecha_inicio') ?: date('Y-m-d');
        $fechaFin    = $this->request->getGet('fecha_fin') ?: $fechaInicio;

    // 2. Instanciar los modelos
        $pagoModel = new \App\Models\PagoModel();
        $ventasModel = new \App\Models\VentasproductosModel();

        // Llamadas a los nuevos métodos de rango
        $reporteCaja = $pagoModel->getResumenTurnosPorRango($fechaInicio, $fechaFin, $idGymActual, $idUsuarioFiltro);
        $reporteTienda = $ventasModel->getResumenTiendaPorRango($fechaInicio, $fechaFin, $idUsuarioFiltro);

        $reporteFinal = [];
        $usuariosProcesados = []; 

        foreach ($reporteCaja as $caja) {
            $userId = $caja['users_id'];
            $usuariosProcesados[] = $userId;

            $tienda = 0;
            foreach ($reporteTienda as $t) {
                if ($t['users_id'] == $userId) {
                    $tienda = floatval($t['total_tienda']);
                    break;
                }
            }

            $reporteFinal[] = [
                'users_id'      => $userId,
                'encargado'     => strtoupper($caja['encargado']),
                'sucursal'      => $caja['sucursal'] ?? 'Matriz',
                'caja'          => [
                    'Efectivo'        => floatval($caja['total_efectivo']),
                    'Tarjeta'         => floatval($caja['total_tarjeta']),
                    'TarjetaCredito'  => floatval($caja['total_tarjeta_credito']), // <-- NUEVA LÍNEA
                    'Transferencia'   => floatval($caja['total_transferencia']),
                    'TotalMembresias' => floatval($caja['total_membresias']),
                ],
                'inscripciones' => intval($caja['total_inscripciones']),
                'renovaciones'  => intval($caja['total_renovaciones']),
                'tienda'        => $tienda,
                'corte_total'   => floatval($caja['total_membresias']) + $tienda
            ];
        }

        foreach ($reporteTienda as $t) {
            $userId = $t['users_id'];
            if (!in_array($userId, $usuariosProcesados)) {
                $detallesUser = $pagoModel->getDetallesUsuarioParaReporte($userId);
                
                if ($idGymActual !== 'TODOS' && ($detallesUser['id_gimnasio'] ?? 1) != $idGymActual) {
                    continue; 
                }

                $reporteFinal[] = [
                    'users_id'      => $userId,
                    'encargado'     => strtoupper($detallesUser['username'] ?? 'DESCONOCIDO'),
                    'sucursal'      => $detallesUser['sucursal'] ?? 'Matriz',
                    'caja'          => ['Efectivo' => 0, 'Tarjeta' => 0, 'TarjetaCredito' => 0, 'Transferencia' => 0, 'TotalMembresias' => 0],
                    'inscripciones' => 0,
                    'renovaciones'  => 0,
                    'tienda'        => floatval($t['total_tienda']),
                    'corte_total'   => floatval($t['total_tienda'])
                ];
            }
        }

        $data = [
            'titulo'       => 'Auditoría por Rangos | VitalGym',
            'username'     => obtener_username(),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin,
            'reporte'      => $reporteFinal,
            'esSuperAdmin' => $esSuperAdmin
        ];

        return view('html/main', $data)
             . view('html/ReporteRango', $data)
             . view('html/footer');
    }

    public function detallesRangoAjax()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'Petición no válida']);
        }

        $fechaInicio = $this->request->getGet('fecha_inicio');
        $fechaFin    = $this->request->getGet('fecha_fin');
        $userId      = $this->request->getGet('users_id');

        $membresiaModel = new RegistroMembresiaModel();
        $detalles = $membresiaModel->getDetalleTurnoPorRango($fechaInicio, $fechaFin, $userId);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $detalles
        ]);
    }
}