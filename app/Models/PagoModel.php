<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoModel extends Model
{
    protected $table      = 'pago';
    protected $primaryKey = 'idPago';
    
    protected $allowedFields = ['Tipo_Pago', 'Concepto', 'Monto', 'Fecha_Pago'];
    protected $returnType    = 'array';

    // --- FUNCIONES PARA REPORTES (Lógica de Negocio) ---

    public function reportePorDia()
    {
        // Agrupa ventas por fecha exacta (YYYY-MM-DD)
        return $this->select("DATE(Fecha_Pago) as fecha, SUM(Monto) as total, COUNT(*) as transacciones")
                    ->groupBy("DATE(Fecha_Pago)")
                    ->orderBy("fecha", "DESC")
                    ->limit(30)
                    ->findAll();
    }

    public function reportePorSemana()
    {
        // Agrupa por semana del año
        return $this->select("YEARWEEK(Fecha_Pago, 1) as semana_id, MIN(DATE(Fecha_Pago)) as inicio_semana, SUM(Monto) as total, COUNT(*) as transacciones")
                    ->groupBy("semana_id")
                    ->orderBy("semana_id", "DESC")
                    ->limit(12)
                    ->findAll();
    }

    public function reportePorMes()
    {
        // Agrupa por Mes y Año
        return $this->select("DATE_FORMAT(Fecha_Pago, '%Y-%m') as mes_id, DATE_FORMAT(Fecha_Pago, '%M %Y') as nombre_mes, SUM(Monto) as total, COUNT(*) as transacciones")
                    ->groupBy("mes_id")
                    ->orderBy("mes_id", "DESC")
                    ->limit(12)
                    ->findAll();
    }
}