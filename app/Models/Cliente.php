<?php

namespace App\Models;

use CodeIgniter\Model;

class Cliente extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'IDClientes';
 
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
  
    protected $allowedFields    = ['Nombre','ApellidoP','ApellidoM','Telefono','Correo','Huella','Fecha_Ingreso','Acepta_WhatsApp'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';


    /**
     * Función Transaccional para guardar Cliente + Pago + Membresía + Extras
     * Respeta MVC encapsulando la lógica de base de datos aquí.
     */
    public function registrarClienteCompleto($datos)
    {
        // Cargamos los otros modelos necesarios
        $pagoModel      = model('PagoModel');
        $membresiaModel = model('RegistroMembresiaModel');
        $serviciosModel = model('Servicios');
        $membresiaextrasModel = model('MembresiaExtras');

        // INICIO DE LA TRANSACCIÓN
        $this->db->transStart();

        try {
            // 1. Consultar Costo Real (Membresía principal)
            $servicio = $serviciosModel->find($datos['Servicios_IDServicios']);
            if (!$servicio) {
                throw new \Exception("El servicio seleccionado no existe.");
            }

            // 2. Guardar Cliente 
            $dataCliente = [
                'Nombre'        => $datos['Nombre'],
                'ApellidoP'     => $datos['ApellidoP'],
                'ApellidoM'     => $datos['ApellidoM'],
                'Telefono'      => $datos['Telefono'],
                'Correo'        => $datos['Correo'],
                'Huella'        => $datos['Huella'],
                'Fecha_Ingreso' => $datos['Fecha_Ingreso'],
                'Acepta_WhatsApp' => $datos['Acepta_WhatsApp']
            ];
            $this->insert($dataCliente);
            $idCliente = $this->getInsertID(); 

            // 3. Guardar Pago
            // Le agregamos un distintivo al concepto si es que compró extras
            $conceptoPago = 'Inscripción - ' . $servicio['NombreMembresia'];
            if (!empty($datos['Servicios_Extra'])) {
                $conceptoPago .= ' + Extras';
            }

            $dataPago = [
                'Tipo_Pago'  => $datos['Tipo_Pago'],
                'Concepto'   => $conceptoPago,
                // 🔥 CAMBIO: Usamos el monto total que sumó el JavaScript
                'Monto'      => $datos['MontoTotal'], 
                'Fecha_Pago' => date('Y-m-d H:i:s')
            ];
            $pagoModel->insert($dataPago);
            $idPago = $pagoModel->getInsertID();

            // 4. Guardar Membresía Principal
            $fechaInicio = date('Y-m-d');
            
            // Evaluamos si el paquete es de 1 mes o más
            if ($servicio['LapsoDias'] >= 30) {
                $meses = round($servicio['LapsoDias'] / 30); 
                $fechaFin = date('Y-m-d', strtotime("+" . $meses . " months", strtotime($fechaInicio)));
            } else {
                $fechaFin = date('Y-m-d', strtotime("+" . $servicio['LapsoDias'] . " days", strtotime($fechaInicio)));
            }
            
            $dataMembresia = [
                'Clientes_IDClientes'   => $idCliente,
                'Pago_idPago'           => $idPago,
                'Servicios_IDServicios' => $datos['Servicios_IDServicios'],
                'Fecha_Inicio'          => $fechaInicio,
                'Fecha_Fin'             => $fechaFin,
                'Estatus_idEstatus'     => 1 // Activo
            ];
            $membresiaModel->insert($dataMembresia);
            
            // 🔥 CAMBIO: Capturamos el ID de la membresía para ligar los extras
            $idRegistroMembresia = $membresiaModel->getInsertID();

            // =========================================================
            // 5. GUARDAR SERVICIOS EXTRA (NUEVO)
            // =========================================================
            if (!empty($datos['Servicios_Extra']) && is_array($datos['Servicios_Extra'])) {
                
                // Usamos el Query Builder para insertar directo en la nueva tabla
           //     $builderExtras = $this->db->table('membresia_extras');
                
                foreach ($datos['Servicios_Extra'] as $idExtra) {
                    if (!empty($idExtra)) {
                        $membresiaextrasModel->insert([
                            'Registros_Membresia_id' => $idRegistroMembresia,
                            'Servicios_IDServicios'  => $idExtra
                        ]);
                    }
                }
            }
            // =========================================================

            // CIERRE DE TRANSACCIÓN
            $this->db->transComplete();

            // Retornamos el estado de la transacción
            return $this->db->transStatus();

        } catch (\Exception $e) {
            $this->db->transRollback();
            // log_message('error', "Error en transacción: " . $e->getMessage()); // Mejor usar el log de CI4 en producción
            return false;
        }
    }



    /**
     * Busca un cliente por su huella y trae su ULTIMA membresía registrada.
     * Retorna el array con datos o null si no existe.
     */
    public function buscarClientePorHuella($huella)
    {
        // Iniciamos el Query Builder sobre la tabla 'Clientes' (definida en $this->table)
        $this->select('
                IDClientes,
                Nombre,
                ApellidoP,
                Fecha_Fin,
                Estatus_idEstatus,
                NombreMembresia
            ');
            // Unimos con Membresías (LEFT JOIN por si el cliente existe pero no tiene historial)
            $this->join('registros_membresia', 'registros_membresia.Clientes_IDClientes = clientes.IDClientes', 'left');
            // Unimos con Servicios para saber el nombre del plan
            $this->join('servicios', 'servicios.IDServicios = registros_membresia.Servicios_IDServicios', 'left');
            // Filtramos por la huella recibida
           $this->where('Huella', $huella);
            // Importante: Ordenamos descendente para tomar SIEMPRE la última membresía comprada
            $this->orderBy('registros_membresia.idRegistros_Membresia', 'DESC');
            // Ejecutamos y devolvemos una sola fila
            $query = $this->first();

            return $query;  
    }




    
    public function obtenerTodosConHuella(){
              // Iniciamos el Query Builder sobre la tabla 'Clientes' (definida en $this->table)
        $this->select('
                IDClientes,
                Nombre,
                ApellidoP,
                Huella,
                Fecha_Fin,
            Estatus_idEstatus,
            NombreMembresia
            ');
            // Unimos con Membresías (LEFT JOIN por si el cliente existe pero no tiene historial)
            $this->join('registros_membresia', 'registros_membresia.Clientes_IDClientes = clientes.IDClientes', 'left');
            // Unimos con Servicios para saber el nombre del plan
            $this->join('servicios', 'servicios.IDServicios = registros_membresia.Servicios_IDServicios', 'left');
          
 // 3. Filtros: Solo traer usuarios que SÍ tengan huella registrada
        // Esto hace la consulta más rápida y evita errores con vacíos
        $this->where('clientes.Huella !=', '');
        $this->where('clientes.Huella IS NOT NULL');

        // 4. Ordenamos para que, si el cliente tiene varios pagos,
        // el sistema tome el registro más reciente (el de arriba)
        $this->orderBy('registros_membresia.idRegistros_Membresia', 'DESC');

        // 5. Retornamos el Array de resultados
        return $this->findAll();
    
    }   

    public function ObtenerclientesActivos(){
          // 1. Seleccionamos los datos del cliente (para no traer datos basura de la otra tabla)
        $this->select('IDClientes,Nombre,ApellidoP,Huella');
        
        // 2. Unimos con la tabla de membresías (Asegúrate que el nombre de la tabla sea exacto)
        $this->join('registros_membresia', 'registros_membresia.Clientes_IDClientes = clientes.IDClientes');
        
        // 3. Filtramos que la huella exista
        $this->where('clientes.Huella !=', '');
        $this->where('clientes.Huella IS NOT NULL');
        
        // 4. EL NUEVO FILTRO: Solo estatus 1 (Activos)
        $this->where('registros_membresia.Estatus_idEstatus', 1);
        
        // 5. Agrupamos por ID del cliente por si hay algún error y tiene 2 pagos activos, 
        // no nos traiga la misma huella dos veces y haga trabajar a PHP el doble.
        $this->groupBy('clientes.IDClientes');
        
        // 6. Ejecutamos
        $query = $this->findAll();
        return $query;
    }
}
