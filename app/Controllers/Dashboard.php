<?php

namespace App\Controllers;

// Hasta arriba en tu Controlador
use App\Models\PagoModel;
use App\Models\RegistroMembresiaModel; 


class Dashboard extends BaseController
{
    public function paginaPrincipal()
    {
    $username = obtener_username();
        $pagoModel = new PagoModel();
        $membresiaModel = new RegistroMembresiaModel(); 

        // --- TAREA DE MANTENIMIENTO: Actualizar membresías vencidas ---
        // Esto es lo más optimizado porque se ejecuta en una sola consulta a la BD.
        // Cambia el estado a 'vencido' (0) para todas las membresías cuya fecha de fin ya pasó
        // y que todavía figuran como 'activas' (1), para mantener la integridad de los datos.
        $membresiaModel->where('Fecha_Fin <', date('Y-m-d'))
                       ->where('Estatus_idEstatus', 1)
                       ->set('Estatus_idEstatus', 2)
                       ->update();


        // 1. INGRESOS DE HOY
        $hoyMatriz = $pagoModel->ingresosMembresiasHoy('matriz');
        $hoyXoxo   = $pagoModel->ingresosMembresiasHoy('xoxo');
        $hoyTienda = $pagoModel->ingresosTiendaHoy();

        $ingresosHoy = [
            'membresias_matriz' => $hoyMatriz,
            'membresias_xoxo'   => $hoyXoxo,
            'membresias'        => $hoyMatriz + $hoyXoxo,
            'tienda'            => $hoyTienda,
            'total'             => $hoyMatriz + $hoyXoxo + $hoyTienda
        ];

        // --- NUEVO: MÉTODOS DE PAGO HOY ---
        $metodosHoy = [
            'efectivo'      => $pagoModel->ingresosMetodoPagoHoy('Efectivo'),
            'tarjeta'       => $pagoModel->ingresosMetodoPagoHoy('Tarjeta'),
            'transferencia' => $pagoModel->ingresosMetodoPagoHoy('Transferencia')
        ];

        // 2. INGRESOS DEL MES
        $mesMatriz = $pagoModel->ingresosMembresiasMes('matriz');
        $mesXoxo   = $pagoModel->ingresosMembresiasMes('xoxo');
        $mesTienda = $pagoModel->ingresosTiendaMes();

        $ingresosMes = [
            'membresias_matriz' => $mesMatriz,
            'membresias_xoxo'   => $mesXoxo,
            'membresias'        => $mesMatriz + $mesXoxo,
            'tienda'            => $mesTienda,
            'total'             => $mesMatriz + $mesXoxo + $mesTienda
        ];

        // --- NUEVO: MÉTODOS DE PAGO MES ---
        $metodosMes = [
            'efectivo'      => $pagoModel->ingresosMetodoPagoMes('Efectivo'),
            'tarjeta'       => $pagoModel->ingresosMetodoPagoMes('Tarjeta'),
            'transferencia' => $pagoModel->ingresosMetodoPagoMes('Transferencia')
        ];

        // 3. MÉTRICAS DE MEMBRESÍAS (Se queda igual que antes)
        $counters = [
            'activos'      => ['total' => $membresiaModel->contarMembresias('activas', 'matriz') + $membresiaModel->contarMembresias('activas', 'xoxo'), 'matriz' => $membresiaModel->contarMembresias('activas', 'matriz'), 'xoxo' => $membresiaModel->contarMembresias('activas', 'xoxo')],
            'nuevas'       => ['total' => $membresiaModel->contarMembresias('nuevas', 'matriz') + $membresiaModel->contarMembresias('nuevas', 'xoxo'), 'matriz' => $membresiaModel->contarMembresias('nuevas', 'matriz'), 'xoxo' => $membresiaModel->contarMembresias('nuevas', 'xoxo')],
            'renovaciones' => ['total' => $membresiaModel->contarMembresias('renovaciones', 'matriz') + $membresiaModel->contarMembresias('renovaciones', 'xoxo'), 'matriz' => $membresiaModel->contarMembresias('renovaciones', 'matriz'), 'xoxo' => $membresiaModel->contarMembresias('renovaciones', 'xoxo')],
            'por_vencer'   => ['total' => $membresiaModel->contarMembresias('por_vencer', 'matriz') + $membresiaModel->contarMembresias('por_vencer', 'xoxo'), 'matriz' => $membresiaModel->contarMembresias('por_vencer', 'matriz'), 'xoxo' => $membresiaModel->contarMembresias('por_vencer', 'xoxo')],
            'vencidas'     => ['total' => $membresiaModel->contarMembresias('vencidas', 'matriz') + $membresiaModel->contarMembresias('vencidas', 'xoxo'), 'matriz' => $membresiaModel->contarMembresias('vencidas', 'matriz'), 'xoxo' => $membresiaModel->contarMembresias('vencidas', 'xoxo')]
        ];

        // 4. PREPARAR Y ENVIAR A LA VISTA
        $data = [
            'titulo'      => 'Panel Principal',
            'username'    => $username,
            'ingresosHoy' => $ingresosHoy,
            'metodosHoy'  => $metodosHoy,  // <-- Mandamos datos a la vista
            'ingresosMes' => $ingresosMes,
            'metodosMes'  => $metodosMes,  // <-- Mandamos datos a la vista
            'counters'    => $counters
        ];

        return view('html/main', $data)
             . view('html/plantilla_dashboard', $data)
             . view('html/footer');
    }

    public function CambioFechas()
    {
        $username = obtener_username();
        $membresiaModel = new RegistroMembresiaModel(); 
        
        // Capturar búsqueda si existe
        $busqueda = $this->request->getGet('busqueda');

        // Obtener datos paginados
        $clientes = $membresiaModel->obtenerActivasParaCambioFecha($busqueda, 10);

        $data = [
            'titulo'   => 'Ajuste de Fechas | VitalGym',
            'username' => $username,
            'clientes' => $clientes,
            'pager'    => $membresiaModel->pager,
            'busqueda' => $busqueda
        ];

        return view('html/main', $data)
             . view('html/CambiodeFechas', $data) // Cargamos la nueva vista
             . view('html/footer');
    }

    public function actualizarFechaMembresia()
    {
        $idRegistro = $this->request->getPost('id');
        $nuevaFecha = $this->request->getPost('fecha');

        if (!$idRegistro || !$nuevaFecha) {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'Datos incompletos', 'token' => csrf_hash()]);
        }

        $membresiaModel = new RegistroMembresiaModel();
        
        // Actualizamos la fecha
        $actualizado = $membresiaModel->update($idRegistro, ['Fecha_Fin' => $nuevaFecha]);

        if ($actualizado) {
            return $this->response->setJSON(['status' => 'success', 'mensaje' => 'Fecha actualizada correctamente', 'token' => csrf_hash()]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'No se pudo actualizar en BD', 'token' => csrf_hash()]);
        }
    }




   }