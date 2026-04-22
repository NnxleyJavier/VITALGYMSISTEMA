<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudesCambioFechaModel extends Model
{
    protected $table      = 'solicitudes_cambio_fecha';
    protected $primaryKey = 'idSolicitud';
    protected $allowedFields = [
        'registro_membresia_id', 'users_id', 'fecha_fin_anterior', 
        'fecha_fin_nueva', 'motivo', 'estado'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Función para mostrar las solicitudes en la vista
    public function obtenerPendientes()
    {
        return $this->select('solicitudes_cambio_fecha.*, users.username, clientes.Nombre, clientes.ApellidoP')
                    ->join('users', 'users.id = solicitudes_cambio_fecha.users_id')
                    ->join('registros_membresia', 'registros_membresia.idRegistros_Membresia = solicitudes_cambio_fecha.registro_membresia_id')
                    ->join('clientes', 'clientes.IDClientes = registros_membresia.Clientes_IDClientes')
                    ->where('solicitudes_cambio_fecha.estado', 'Pendiente')
                    ->orderBy('solicitudes_cambio_fecha.created_at', 'DESC')
                    ->findAll();
    }
}