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

    public function obtenerClientesPorVencer($dias,$porPagina = 10)
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
             ->where('registros_membresia.Estatus_idEstatus', 1) // Descomenta esta línea si manejas un estatus "1 = Activo"
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
        // Cargamos los modelos necesarios
        $pagoModel = model('PagoModel');
        $serviciosModel = model('Servicios');
        $membresiaExtrasModel = model('MembresiaExtras');

        $this->db->transStart();

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

}
