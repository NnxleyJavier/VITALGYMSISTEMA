<?php

namespace App\Models;

use CodeIgniter\Model;

class RegistroMembresiaModel extends Model
{
    protected $table      = 'registros_membresia';
    protected $primaryKey = 'idRegistros_Membresia'; // Ajusta a tu PK real
 
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
  
    protected $allowedFields = [
        'Clientes_IDClientes', 'Pago_idPago', 'Estatus_idEstatus',
        'Servicios_IDServicios', 'Fecha_Inicio', 'Fecha_Fin', 'Aviso_Enviado'
    ]; 

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function obtenerFechaMembresia($clienteId)
    {
        $query = $this->select('Fecha_Inicio, Fecha_Fin')
                    ->where('Clientes_IDClientes', $clienteId)
                    ->orderBy('Fecha_Fin', 'DESC');
        $query = $this->findAll();
        return $query;
    }

    public function obtenerClientesPorVencer($dias, $porPagina = 10)
    {
        // 1. Calculamos las fechas límite
        $hoy = date('Y-m-d'); 
        $fechaCorte = date('Y-m-d', strtotime("+$dias days"));

        // 2. Construimos la consulta con Query Builder
        return $this->select('
                registros_membresia.idRegistros_Membresia,
                clientes.Nombre, 
                clientes.ApellidoP, 
                clientes.Telefono, 
                clientes.Acepta_WhatsApp, 
                registros_membresia.Fecha_Fin,
                DATEDIFF(registros_membresia.Fecha_Fin, CURDATE()) AS DiasRestantes
            ')
            ->join('clientes', 'clientes.IDClientes = registros_membresia.Clientes_IDClientes')
            ->where('registros_membresia.Fecha_Fin >=', $hoy . ' 00:00:00')
            ->where('registros_membresia.Fecha_Fin <=', $fechaCorte . ' 23:59:59')
            ->where('registros_membresia.Estatus_idEstatus', 1) // 1 = Activo
            ->where('registros_membresia.Aviso_Enviado', 0) // ¡Magia! Solo trae los que NO se han enviado
            ->orderBy('DiasRestantes', 'ASC') // Ordenamos para que los que vencen hoy salgan primero
            ->paginate($porPagina); // Paginación automática de 10 en 10
    }

    public function obtenerClientesParaRenovacion($telefono = null, $diasAviso = 5, $porPagina = 10)
    {
        // Límite: Hoy + los días de aviso (ej. 5 días)
        $limiteDias = date('Y-m-d', strtotime("+$diasAviso days"));

        // Seleccionamos los datos y calculamos los días restantes
        $this->select('
                clientes.IDClientes, 
                clientes.Nombre, 
                clientes.ApellidoP, 
                clientes.Telefono, 
                registros_membresia.Fecha_Fin, 
                DATEDIFF(registros_membresia.Fecha_Fin, CURDATE()) AS DiasRestantes
            ')
            ->join('clientes', 'clientes.IDClientes = registros_membresia.Clientes_IDClientes')
            // TRUCO SQL: Subconsulta para traer SOLO el registro más reciente de cada cliente
            ->where('registros_membresia.Fecha_Fin = (SELECT MAX(Fecha_Fin) FROM registros_membresia rm2 WHERE rm2.Clientes_IDClientes = registros_membresia.Clientes_IDClientes)')
            // Filtramos: Que su fecha más reciente sea menor o igual al límite (incluye los ya vencidos que son fechas pasadas)
            ->where('registros_membresia.Fecha_Fin <=', $limiteDias . ' 23:59:59');

        // Si el usuario escribió un teléfono en el buscador, lo filtramos
        if (!empty($telefono)) {
            $this->like('clientes.Telefono', $telefono);
        }

        // Ordenamos: Los más atrasados (números negativos) salen primero
        $this->orderBy('DiasRestantes', 'ASC');
        
        // Retornamos con paginación
        return $this->paginate($porPagina);
    }
    
    public function renovarMembresiaTransaccion($datos)
    {
        // (Tu código original de transacciones se mantiene aquí intacto)
        // ...
    }

    // ====================================================================
    // NUEVA FUNCIÓN: Obtener membresías activas para cambio de fecha
    // ====================================================================
    public function obtenerActivasParaCambioFecha($busqueda = null, $porPagina = 10)
    {
        $this->select('registros_membresia.idRegistros_Membresia, clientes.Nombre, clientes.ApellidoP, clientes.Telefono, registros_membresia.Fecha_Fin')
             ->join('clientes', 'clientes.IDClientes = registros_membresia.Clientes_IDClientes')
             ->where('registros_membresia.Estatus_idEstatus', 1); // Solo activas

        if (!empty($busqueda)) {
            $this->groupStart()
                 ->like('clientes.Nombre', $busqueda)
                 ->orLike('clientes.ApellidoP', $busqueda)
                 ->orLike('clientes.Telefono', $busqueda)
                 ->groupEnd();
        }

        $this->orderBy('registros_membresia.Fecha_Fin', 'ASC');
        return $this->paginate($porPagina);
    }

    // ====================================================================
    // NUEVA FUNCIÓN PARA EL DASHBOARD: CONTAR MEMBRESÍAS
    // ====================================================================
    public function contarMembresias($tipo, $sucursal)
    {
        // 1. Obtener ID de la sucursal (Ajusta si tus IDs son diferentes)
        $idGym = ($sucursal === 'matriz') ? 1 : (($sucursal === 'xoxo') ? 2 : 0);

        // 2. Iniciamos el Query Builder uniendo con los servicios para saber la sucursal
        $builder = $this->db->table($this->table . ' rm');
        $builder->join('gymnasios_has_servicios ghs', 'rm.Servicios_IDServicios = ghs.Servicios_IDServicios');
        
        // Evitar duplicados en caso de que un servicio esté registrado raro
        $builder->distinct();
        $builder->select('rm.idRegistros_Membresia'); 
        
        $builder->where('ghs.Gymnasios_idGymnasios', $idGym);

        $hoy = date('Y-m-d');
        $mesActual = date('m');
        $anioActual = date('Y');

        // 3. Filtramos dependiendo de lo que el dashboard nos pida
        switch ($tipo) {
            case 'activas':
                $builder->where('rm.Fecha_Fin >=', $hoy . ' 00:00:00');
                $builder->where('rm.Estatus_idEstatus', 1);
                break;
                
            case 'vencidas':
                $builder->where('rm.Fecha_Fin <', $hoy . ' 00:00:00');
                break;
                
            case 'por_vencer':
                // Que venzan en los próximos 5 días
                $fechaCorte = date('Y-m-d', strtotime('+5 days'));
                $builder->where('rm.Fecha_Fin >=', $hoy . ' 00:00:00');
                $builder->where('rm.Fecha_Fin <=', $fechaCorte . ' 23:59:59');
                $builder->where('rm.Estatus_idEstatus', 1);
                break;
                
            case 'nuevas':
                // Iniciaron este mes y el pago NO dice "Renovación"
                $builder->join('pago p', 'p.idPago = rm.Pago_idPago');
                $builder->where('MONTH(rm.Fecha_Inicio)', $mesActual);
                $builder->where('YEAR(rm.Fecha_Inicio)', $anioActual);
                $builder->notLike('p.Concepto', 'Renovación');
                break;
                
            case 'renovaciones':
                // Iniciaron este mes y el pago SÍ dice "Renovación"
                $builder->join('pago p', 'p.idPago = rm.Pago_idPago');
                $builder->where('MONTH(rm.Fecha_Inicio)', $mesActual);
                $builder->where('YEAR(rm.Fecha_Inicio)', $anioActual);
                $builder->like('p.Concepto', 'Renovación');
                break;
        }

        return $builder->countAllResults();
    }
}