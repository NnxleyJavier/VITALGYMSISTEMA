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


    
// ====================================================================
    // REPORTE DIARIO DE TURNOS (CON FILTRO DE PRIVACIDAD DE CAJERO)
    // ====================================================================
    public function getResumenTurnosAgrupado($fecha, $idGym, $idUsuarioFiltro = null)
    {
        $sql = "
            SELECT
                p.users_id,
                u.username as encargado,
                g.Nombre as sucursal, 
                SUM(CASE WHEN p.Tipo_Pago = 'Efectivo' THEN p.Monto ELSE 0 END) as total_efectivo,
                SUM(CASE WHEN p.Tipo_Pago = 'Tarjeta' THEN p.Monto ELSE 0 END) as total_tarjeta,
                SUM(CASE WHEN p.Tipo_Pago = 'Transferencia' THEN p.Monto ELSE 0 END) as total_transferencia,
                SUM(p.Monto) as total_membresias,
                SUM(CASE WHEN p.Concepto LIKE '%Inscrip%' THEN 1 ELSE 0 END) as total_inscripciones,
                SUM(CASE WHEN p.Concepto NOT LIKE '%Inscrip%' THEN 1 ELSE 0 END) as total_renovaciones
            FROM pago p
            JOIN users u ON u.id = p.users_id
            LEFT JOIN gymnasios g ON g.idGymnasios = p.id_gimnasio
            WHERE DATE(p.Fecha_Pago) = ?
        ";

        $bindings = [$fecha];

        // Filtro 1: Sucursal (Si no es superadmin global)
        if ($idGym !== 'TODOS' && !empty($idGym)) {
            $sql .= " AND p.id_gimnasio = ?";
            $bindings[] = $idGym;
        }

        // Filtro 2: Privacidad de Cajero (Solo ve su propio dinero)
        if ($idUsuarioFiltro !== null) {
            $sql .= " AND p.users_id = ?";
            $bindings[] = $idUsuarioFiltro;
        }

        $sql .= " GROUP BY p.users_id";

        return $this->db->query($sql, $bindings)->getResultArray();
    }

    // Pequeño Helper MVC para obtener datos del usuario sin ensuciar el Controlador
    public function getDetallesUsuarioParaReporte($userId)
    {
        return $this->db->table('users u')
            ->select('u.username, g.Nombre as sucursal, u.id_gimnasio')
            ->join('gymnasios g', 'g.idGymnasios = u.id_gimnasio', 'left')
            ->where('u.id', $userId)
            ->get()->getRowArray();
    }


    public function getResumenTurnosPorRango($fechaInicio, $fechaFin, $idGym, $idUsuarioFiltro = null)
    {
        $builder = $this->db->table('pago p');
        
        $builder->select("
            p.users_id,
            u.username as encargado,
            g.nombre as sucursal,
            SUM(CASE WHEN p.Tipo_Pago = 'Efectivo' THEN p.Monto ELSE 0 END) as total_efectivo,
            SUM(CASE WHEN p.Tipo_Pago IN ('Tarjeta', 'TarjetaCredito') THEN p.Monto ELSE 0 END) as total_tarjeta,
            SUM(CASE WHEN p.Tipo_Pago = 'Transferencia' THEN p.Monto ELSE 0 END) as total_transferencia,
            SUM(p.Monto) as total_membresias,
            SUM(CASE WHEN p.Concepto LIKE '%Inscrip%' THEN 1 ELSE 0 END) as total_inscripciones,
            SUM(CASE WHEN p.Concepto NOT LIKE '%Inscrip%' THEN 1 ELSE 0 END) as total_renovaciones
        ");
        
        $builder->join('users u', 'u.id = p.users_id');
        $builder->join('gymnasios g', 'g.idGymnasios = p.id_gimnasio', 'left');
        $builder->where('DATE(p.Fecha_Pago) >=', $fechaInicio);
        $builder->where('DATE(p.Fecha_Pago) <=', $fechaFin);

        if ($idGym !== 'TODOS' && !empty($idGym)) {
            $builder->where('p.id_gimnasio', $idGym);
        }

        if ($idUsuarioFiltro !== null) {
            $builder->where('p.users_id', $idUsuarioFiltro);
        }

        $builder->groupBy('p.users_id');

        return $builder->get()->getResultArray();
    }

    
}