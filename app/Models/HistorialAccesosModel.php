<?php

namespace App\Models;

use CodeIgniter\Model;

class HistorialAccesosModel extends Model
{
    // 1. Apuntamos a tu tabla real
    protected $table = 'historial_accesos'; 
    protected $primaryKey = 'idRegistrosEntrada'; 

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['Clientes_IDClientes','FechaHora_Acceso','Estatus_idEstatus','Motivo'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function obtenerAsistenciasPorFecha($fecha)
    {
        // 1. Cargamos el Helper de sucursales para seguridad
        helper('gym');
        $idGymActual = obtener_id_gimnasio();

        // 2. Armamos la consulta usando los nombres exactos de tus columnas
        // Usamos el alias 'AS fecha_hora' para que empate con el diseño de la vista
        $this->select('historial_accesos.FechaHora_Acceso AS fecha_hora, clientes.Nombre, clientes.ApellidoP, clientes.Telefono')
             ->join('clientes', 'clientes.IDClientes = historial_accesos.Clientes_IDClientes');

        // 3. Filtramos por la fecha seleccionada en el calendario
        $this->where('DATE(historial_accesos.FechaHora_Acceso)', $fecha);

   

        // 5. Ordenamos por la hora de acceso, de la más reciente a la más antigua
        $this->orderBy('historial_accesos.FechaHora_Acceso', 'DESC');

        return $this->findAll();
    }
}