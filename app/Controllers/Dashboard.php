<?php

namespace App\Controllers;

use App\Models\Servicios;
use App\Models\Cliente;
use App\Models\UsersModel;
use App\Models\RegistroMembresiaModel;
use App\Models\PagoModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Authentication\Authenticators\Session;


class Dashboard extends BaseController
{
    public function paginaPrincipal()
    {
        $username = obtener_username();
        
        $data = [
            'titulo'   => 'Panel Principal',
            'username' => $username
        ];

        return view('html/main', $data)
             . view('html/DashboardInicio', $data)
             . view('html/footer');
    }

    public function reporteIngresos()
    {
        $pagoModel = new PagoModel();

        $data = [
            'titulo'    => 'Reporte Financiero | VitalGym',
            'username'  => obtener_username(),
            'porDia'    => $pagoModel->reportePorDia(),
            'porSemana' => $pagoModel->reportePorSemana(),
            'porMes'    => $pagoModel->reportePorMes()
        ];

        return view('html/main', $data)
             . view('html/ReporteIngresos', $data)
             . view('html/footer');
    }



}