<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoModel extends Model
{
    protected $table            = 'pago';
    protected $primaryKey       = 'idPago';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // 1. Agregamos las columnas a los campos permitidos
    protected $allowedFields    = ['Tipo_Pago', 'Concepto', 'Monto', 'Fecha_Pago', 'id_gimnasio', 'users_id'];

    // 2. DISPARADOR AUTOMÁTICO: Se ejecuta SIEMPRE antes de guardar en la BD
    protected $beforeInsert = ['asignarDatosAutomaticos'];

    protected function asignarDatosAutomaticos(array $data)
    {
        // Capturamos todos los datos del usuario activo desde la sesión de Shield
        $usuarioActivo = auth()->user();

        if ($usuarioActivo) {
            // A) Asignar el ID del usuario que está procesando el cobro
            if (!isset($data['data']['users_id'])) {
                $data['data']['users_id'] = $usuarioActivo->id;
            }

            // B) Asignar la sucursal física real a la que pertenece el usuario en la tabla 'users'.
            // Esto asegura que si el Superadmin cobra, el dinero entra a la sucursal donde él está asignado.
            if (!isset($data['data']['id_gimnasio'])) {
                $data['data']['id_gimnasio'] = $usuarioActivo->id_gimnasio;
            }
        }

        return $data;
    }

    /**
     * Helper interno para mapear el texto del dashboard a los IDs reales
     */
    private function getSucursalId($sucursal)
    {
        if ($sucursal === 'matriz') return 1; 
        if ($sucursal === 'xoxo') return 2;
        return 1; // Por defecto fallback
    }

    // ====================================================================
    // CONSULTAS PARA EL DASHBOARD (EXACTAS Y SEPARADAS POR SUCURSAL)
    // ====================================================================

    public function ingresosMembresiasHoy($sucursal)
    {
        $idGym = $this->getSucursalId($sucursal);
        $row = $this->select('SUM(Monto) as total_monto')
                    ->where('id_gimnasio', $idGym)
                    ->where('DATE(Fecha_Pago)', date('Y-m-d'))
                    ->first();
        return $row['total_monto'] ?? 0;
    }

    public function ingresosMembresiasSemana($sucursal)
    {
        $idGym = $this->getSucursalId($sucursal);
        $row = $this->select('SUM(Monto) as total_monto')
                    ->where('id_gimnasio', $idGym)
                    ->where('YEARWEEK(Fecha_Pago, 1)', date('YW'))
                    ->first();
        return $row['total_monto'] ?? 0;
    }

    public function ingresosMembresiasMes($sucursal)
    {
        $idGym = $this->getSucursalId($sucursal);
        $row = $this->select('SUM(Monto) as total_monto')
                    ->where('id_gimnasio', $idGym)
                    ->where('MONTH(Fecha_Pago)', date('m'))
                    ->where('YEAR(Fecha_Pago)', date('Y'))
                    ->first();
        return $row['total_monto'] ?? 0;
    }

    // ====================================================================
    // REPORTES GLOBALES (Suma real de toda la empresa sin duplicados)
    // ====================================================================

    public function reportePorHoy()
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

    public function ingresosMetodoPagoHoy($metodo, $sucursal = 'TODOS')
    {
        $builder = $this->select('SUM(Monto) as total_monto')
                        ->where('Tipo_Pago', $metodo)
                        ->where('DATE(Fecha_Pago)', date('Y-m-d'));
        
        if ($sucursal !== 'TODOS') {
            $builder->where('id_gimnasio', $this->getSucursalId($sucursal));
        }

        $row = $builder->first();
        return $row['total_monto'] ?? 0.00;
    }

    public function ingresosMetodoPagoMes($metodo, $sucursal = 'TODOS')
    {
        $builder = $this->select('SUM(Monto) as total_monto')
                        ->where('Tipo_Pago', $metodo)
                        ->where('MONTH(Fecha_Pago)', date('m'))
                        ->where('YEAR(Fecha_Pago)', date('Y'));

        if ($sucursal !== 'TODOS') {
            $builder->where('id_gimnasio', $this->getSucursalId($sucursal));
        }

        $row = $builder->first();
        return $row['total_monto'] ?? 0.00;
    }
}