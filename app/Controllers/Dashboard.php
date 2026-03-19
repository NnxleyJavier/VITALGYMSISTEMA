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




}