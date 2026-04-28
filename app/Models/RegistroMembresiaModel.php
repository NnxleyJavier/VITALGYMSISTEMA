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
        'Servicios_IDServicios', 'Fecha_Inicio', 'Fecha_Fin', 'Aviso_Enviado','users_id'
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
public function obtenerClientesParaRenovacion($busqueda = null, $estado = 'todas', $porPagina = 10)
    {
        // 1. Usamos $this->db nativo del modelo para crear la subconsulta
        // Esto aísla el último registro (el más reciente) de cada cliente
        $subquery = $this->db->table('registros_membresia')
                             ->select('Clientes_IDClientes, MAX(idRegistros_Membresia) as ultimo_registro')
                             ->groupBy('Clientes_IDClientes');

        // 2. Construimos la consulta principal referenciando directamente a $this
      $this->select('
                registros_membresia.*, 
                clientes.IDClientes, 
                clientes.Nombre, 
                clientes.ApellidoP, 
                clientes.Telefono, 
                servicios.NombreMembresia, 
                estatus.EstadodeMembresia, 
                DATEDIFF(CURDATE(), registros_membresia.Fecha_Fin) AS DiasVencidos
             ')
             ->join('clientes', 'clientes.IDClientes = registros_membresia.Clientes_IDClientes')
             ->join('servicios', 'servicios.IDServicios = registros_membresia.Servicios_IDServicios', 'left')
             ->join('estatus', 'estatus.idEstatus = registros_membresia.Estatus_idEstatus', 'left')
             // 3. Unimos la tabla principal con la subconsulta ya compilada
             ->join('(' . $subquery->getCompiledSelect() . ') as ultimos', 'ultimos.ultimo_registro = registros_membresia.idRegistros_Membresia');

        // 4. Aplicamos los filtros de estado
        if ($estado === 'activas') {
            $this->where('registros_membresia.Estatus_idEstatus', 1);
        } elseif ($estado === 'inactivas') {
            $this->where('registros_membresia.Estatus_idEstatus !=', 1);
        }

        // 5. Aplicamos la barra de búsqueda agrupando las condiciones
        if (!empty($busqueda)) {
            $this->groupStart()
                 ->like('clientes.Nombre', $busqueda)
                 ->orLike('clientes.ApellidoP', $busqueda)
                 ->orLike('clientes.Telefono', $busqueda)
                 ->groupEnd();
        }

        // 6. Ordenamos por fecha de vencimiento y aplicamos tu paginación
        $this->orderBy('registros_membresia.Fecha_Fin', 'ASC'); // Los que vencen primero aparecen arriba
        
        return $this->paginate($porPagina);
    }
    
  public function renovarMembresiaTransaccion($datos)
    {
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
            $ultima = $this->where('Clientes_IDClientes', $datos['Clientes_IDClientes'])
                           ->orderBy('Fecha_Fin', 'DESC')
                           ->first();

            $hoy = date('Y-m-d');
            
            if ($ultima && $ultima['Fecha_Fin'] >= $hoy) {
                $fechaInicio = date('Y-m-d', strtotime($ultima['Fecha_Fin']));
            } else {
                $fechaInicio = $hoy;
            }

            $dias = $servicio['LapsoDias'] ?? 30; 
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
                'Estatus_idEstatus'     => 1, 
                'Servicios_IDServicios' => $datos['Servicios_IDServicios'],
                'Fecha_Inicio'          => $fechaInicio . ' 00:00:00',
                'Fecha_Fin'             => $fechaFin . ' 23:59:59',
                'Aviso_Enviado'         => 0 
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

            // AQUI ESTÁ EL CAMBIO: Enviamos el idPago de regreso al controlador
            return [
                'success'   => true, 
                'fecha_fin' => $fechaFin, 
                'idPago'    => $idPago 
            ];

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