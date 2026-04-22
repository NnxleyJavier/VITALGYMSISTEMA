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
        // Calculamos la fecha límite (Hoy + 5 días)
        $limiteDias = date('Y-m-d', strtotime("+$diasAviso days"));

        $this->select('
                registros_membresia.Fecha_Fin,
                registros_membresia.Estatus_idEstatus,
                clientes.IDClientes, 
                clientes.Nombre, 
                clientes.ApellidoP, 
                clientes.Telefono, 
                DATEDIFF(CURDATE(), registros_membresia.Fecha_Fin) AS DiasVencidos
            ')
            ->join('clientes', 'clientes.IDClientes = registros_membresia.Clientes_IDClientes');

        // 🔥 LÓGICA COMBINADA: Inactivos OR (Activos a punto de vencer)
        $this->groupStart()
             // Condición 1: Todos los inactivos (Estatus 2)
             ->where('registros_membresia.Estatus_idEstatus !=', 1)
             
             // Condición 2: O los que son activos (Estatus 1) pero vencen pronto
             ->orGroupStart()
                 ->where('registros_membresia.Estatus_idEstatus', 1)
                 ->where('registros_membresia.Fecha_Fin <=', $limiteDias . ' 23:59:59')
             ->groupEnd()
        ->groupEnd();

        $this->groupBy('clientes.IDClientes');

        if (!empty($telefono)) {
            $this->groupStart()
                 ->like('clientes.Nombre', $telefono)
                 ->orLike('clientes.ApellidoP', $telefono)
                 ->orLike('clientes.Telefono', $telefono)
                 ->groupEnd();
        }

        // 🔥 DOBLE ORDENAMIENTO
        // 1ro: Inactivos (2) arriba, Activos (1) abajo
        $this->orderBy('registros_membresia.Estatus_idEstatus', 'DESC'); 
        // 2do: Dentro de cada grupo, los más recientes arriba
        $this->orderBy('registros_membresia.Fecha_Fin', 'DESC'); 
        
        return $this->paginate($porPagina);
    }
    
    public function renovarMembresiaTransaccion($datos)
    {
        // (Tu código original de transacciones se mantiene aquí intacto)
        // ...

           // Cargamos los modelos necesarios
        $pagoModel = model('PagoModel');
        $serviciosModel = model('Servicios');
        $membresiaExtrasModel = model('MembresiaExtras');

        $this->transStart();

        try {
            // 1. Obtener la información del nuevo servicio elegido
            $servicio = $serviciosModel->find($datos['Servicios_IDServicios']);
            if (!$servicio) {
                throw new \Exception("El servicio seleccionado no existe.");
            }

            // 2. Calcular las fechas inteligentemente
            // Buscamos si tiene una membresía previa para no robarle días
            $ultima = $this->where('Clientes_IDClientes', $datos['Clientes_IDClientes'])
                           ->orderBy('Fecha_Fin', 'DESC')
                           ->first();

            $hoy = date('Y-m-d');
            
            // Si la membresía actual aún no vence, el nuevo mes inicia cuando termine esa
            if ($ultima && $ultima['Fecha_Fin'] >= $hoy) {
                $fechaInicio = date('Y-m-d', strtotime($ultima['Fecha_Fin']));
            } else {
                // Si ya venció, inicia hoy
                $fechaInicio = $hoy;
            }

            // Calculamos el fin sumando los "LapsoDias" que trae el servicio en la BD
            $dias = $servicio['LapsoDias'] ?? 30; // Por si viene nulo, damos 30 por defecto
            $fechaFin = date('Y-m-d', strtotime($fechaInicio . " + $dias days"));

            // 3. Registrar el Pago
            $idPago = $pagoModel->insert([
                'Tipo_Pago'  => $datos['Tipo_Pago'] ?? 'Efectivo',
                'Concepto'   => 'Renovación: ' . $servicio['NombreMembresia'],
                'Monto'      => $datos['MontoTotal'],
                'Fecha_Pago' => date('Y-m-d H:i:s')
            ]);

            // 4. Registrar la nueva Membresía
            $idRegistroMembresia = $this->insert([
                'Clientes_IDClientes'   => $datos['Clientes_IDClientes'],
                'Pago_idPago'           => $idPago,
                'Estatus_idEstatus'     => 1, // 1 = Activo
                'Servicios_IDServicios' => $datos['Servicios_IDServicios'],
                'Fecha_Inicio'          => $fechaInicio . ' 00:00:00',
                'Fecha_Fin'             => $fechaFin . ' 23:59:59',
                'Aviso_Enviado'         => 0 // Reiniciamos el aviso de WhatsApp
            ]);

            // 5. Registrar los Extras (si seleccionó alguno)
            if (!empty($datos['Extras']) && is_array($datos['Extras'])) {
                foreach ($datos['Extras'] as $idExtra) {
                    $membresiaExtrasModel->insert([
                        'Registros_Membresia_id' => $idRegistroMembresia,
                        'Servicios_IDServicios'  => $idExtra
                    ]);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === FALSE) {
                throw new \Exception("Error al guardar en la base de datos.");
            }

            return ['success' => true, 'fecha_fin' => $fechaFin];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
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

    // ====================================================================
    // NUEVA FUNCIÓN: Obtener listado general de membresías con filtros
    // ====================================================================
    public function obtenerTodasLasMembresias($estado = 'todas', $busqueda = null, $porPagina = 1)
    {
        $this->select('registros_membresia.*, clientes.Nombre, clientes.ApellidoP, clientes.Telefono, servicios.NombreMembresia, estatus.EstadodeMembresia')
             ->join('clientes', 'clientes.IDClientes = registros_membresia.Clientes_IDClientes')
             ->join('servicios', 'servicios.IDServicios = registros_membresia.Servicios_IDServicios')
             ->join('estatus', 'estatus.idEstatus = registros_membresia.Estatus_idEstatus');

        if ($estado === 'activas') {
            $this->where('registros_membresia.Estatus_idEstatus', 1);
        } elseif ($estado === 'inactivas') {
            $this->where('registros_membresia.Estatus_idEstatus !=', 1);
        }

        if (!empty($busqueda)) {
            $this->groupStart()->like('clientes.Nombre', $busqueda)->orLike('clientes.ApellidoP', $busqueda)->orLike('clientes.Telefono', $busqueda)->groupEnd();
        }

        $this->orderBy('registros_membresia.Fecha_Fin', 'DESC'); // Ordenar por fecha de vencimiento (más recientes arriba)
        return $this->paginate($porPagina);
    }


    // Obtiene la membresía más reciente cruzada con su nombre y estatus
    public function obtenerMembresiaDetalladaReciente($idCliente)
    {
        return $this->select('registros_membresia.*, servicios.NombreMembresia, estatus.EstadodeMembresia')
                    ->join('servicios', 'servicios.IDServicios = registros_membresia.Servicios_IDServicios', 'left')
                    ->join('estatus', 'estatus.idEstatus = registros_membresia.Estatus_idEstatus', 'left')
                    ->where('Clientes_IDClientes', $idCliente)
                    ->orderBy('Fecha_Fin', 'DESC')
                    ->first();
    }
}