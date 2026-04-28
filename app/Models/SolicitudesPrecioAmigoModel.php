<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudesPrecioAmigoModel extends Model
{
    protected $table      = 'solicitudes_precio_amigo';
    protected $primaryKey = 'idSolicitud';
    
  protected $allowedFields = [
        'Clientes_IDClientes', 'Servicios_IDServicios', 'Pago_idPago', 'precio_solicitado', 
        'motivo', 'estado', 'users_id'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';


    public function obtenerPendientesConDetalles()
    {
        return $this->select('solicitudes_precio_amigo.*, clientes.Nombre as ClienteNombre, clientes.ApellidoP as ClienteApellido, servicios.NombreMembresia, users.username as Solicitante')
                    ->join('clientes', 'clientes.IDClientes = solicitudes_precio_amigo.Clientes_IDClientes')
                    ->join('servicios', 'servicios.IDServicios = solicitudes_precio_amigo.Servicios_IDServicios')
                    ->join('users', 'users.id = solicitudes_precio_amigo.users_id')
                    ->where('solicitudes_precio_amigo.estado', 'Pendiente')
                    ->orderBy('solicitudes_precio_amigo.created_at', 'DESC')
                    ->findAll();
    }
}