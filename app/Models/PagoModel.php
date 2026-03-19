<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoModel extends Model
{
    protected $table            = 'pago';
    protected $primaryKey       = 'idPago';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['Tipo_Pago', 'Concepto', 'Monto', 'Fecha_Pago'];

    /**
     * Helper interno para mapear el texto a los IDs reales de tu BD.
     * Asumo que Matriz = 1 y Xoxo = 2. Ajusta estos números si en tu tabla 'gymnasios' son diferentes.
     */
    private function getSucursalId($sucursal)
    {
        if ($sucursal === 'matriz') return 1; 
        if ($sucursal === 'xoxo') return 2;
        return null;
    }

    // ====================================================================
    // CONSULTAS PARA EL DASHBOARD PRINCIPAL (CORREGIDAS)
    // ====================================================================

    public function ingresosMembresiasHoy($sucursal)
    {
        $idGym = $this->getSucursalId($sucursal);
        $hoy = date('Y-m-d'); // Ej: 2026-03-19

        $row = $this->select('SUM(pago.Monto) as total_monto')
                    ->join('registros_membresia rm', 'pago.idPago = rm.Pago_idPago')
                    ->join('gymnasios_has_servicios ghs', 'rm.Servicios_IDServicios = ghs.Servicios_IDServicios')
                    // Quitamos el filtro Tipo_Pago='Membresia' porque el JOIN ya asegura que es una membresía
                    ->where('ghs.Gymnasios_idGymnasios', $idGym)
                    // Filtramos por el rango de horas del día de hoy
                    ->where('pago.Fecha_Pago >=', $hoy . ' 00:00:00')
                    ->where('pago.Fecha_Pago <=', $hoy . ' 23:59:59')
                    ->first();

        return $row['total_monto'] ?? 0.00;
    }

    public function ingresosMembresiasMes($sucursal)
    {
        $idGym = $this->getSucursalId($sucursal);
        $mesActual = date('m');
        $anioActual = date('Y');
        
        $row = $this->select('SUM(pago.Monto) as total_monto')
                    ->join('registros_membresia rm', 'pago.idPago = rm.Pago_idPago')
                    ->join('gymnasios_has_servicios ghs', 'rm.Servicios_IDServicios = ghs.Servicios_IDServicios')
                    ->where('ghs.Gymnasios_idGymnasios', $idGym)
                    ->where('MONTH(pago.Fecha_Pago)', $mesActual)
                    ->where('YEAR(pago.Fecha_Pago)', $anioActual)
                    ->first();

        return $row['total_monto'] ?? 0.00;
    }

    public function ingresosTiendaHoy()
    {
        $hoy = date('Y-m-d');

        $row = $this->select('SUM(Monto) as total_monto')
                    // OJO: Si para tienda sí guardas "Tienda" en Tipo_Pago, déjalo. Si guardas "Efectivo", 
                    // tendrás que buscar por el 'Concepto' (ej. ->like('Concepto', 'Tienda'))
                    ->where('Tipo_Pago', 'Tienda') 
                    ->where('Fecha_Pago >=', $hoy . ' 00:00:00')
                    ->where('Fecha_Pago <=', $hoy . ' 23:59:59')
                    ->first();

        return $row['total_monto'] ?? 0.00;
    }

    public function ingresosTiendaMes()
    {
        $mesActual = date('m');
        $anioActual = date('Y');

        $row = $this->select('SUM(Monto) as total_monto')
                    ->where('Tipo_Pago', 'Tienda')
                    ->where('MONTH(Fecha_Pago)', $mesActual)
                    ->where('YEAR(Fecha_Pago)', $anioActual)
                    ->first();

        return $row['total_monto'] ?? 0.00;
    }

    // ====================================================================
    // TUS FUNCIONES ORIGINALES DE REPORTES
    // ====================================================================

    public function reportePorDia()
    {
        $row = $this->select('SUM(Monto) as total_monto')
                    ->where('DATE(Fecha_Pago)', date('Y-m-d'))
                    ->first();
        return $row['total_monto'] ?? 0;
    }

    public function reportePorSemana()
    {
        $row = $this->select('SUM(Monto) as total_monto')
                    ->where('YEARWEEK(Fecha_Pago, 1)', date('YW'))
                    ->first();
        return $row['total_monto'] ?? 0;
    }

    public function reportePorMes()
    {
        $row = $this->select('SUM(Monto) as total_monto')
                    ->where('MONTH(Fecha_Pago)', date('m'))
                    ->where('YEAR(Fecha_Pago)', date('Y'))
                    ->first();
        return $row['total_monto'] ?? 0;
    }

    // ====================================================================
    // DESGLOSE POR MÉTODO DE PAGO
    // ====================================================================

    public function ingresosMetodoPagoHoy($metodo)
    {
        $hoy = date('Y-m-d');
        
        $row = $this->select('SUM(Monto) as total_monto')
                    ->where('Tipo_Pago', $metodo)
                    ->where('Fecha_Pago >=', $hoy . ' 00:00:00')
                    ->where('Fecha_Pago <=', $hoy . ' 23:59:59')
                    ->first();

        return $row['total_monto'] ?? 0.00;
    }

    public function ingresosMetodoPagoMes($metodo)
    {
        $mesActual = date('m');
        $anioActual = date('Y');
        
        $row = $this->select('SUM(Monto) as total_monto')
                    ->where('Tipo_Pago', $metodo)
                    ->where('MONTH(Fecha_Pago)', $mesActual)
                    ->where('YEAR(Fecha_Pago)', $anioActual)
                    ->first();

        return $row['total_monto'] ?? 0.00;
    }
}