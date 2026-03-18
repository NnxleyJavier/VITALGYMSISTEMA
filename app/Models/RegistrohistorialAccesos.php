<?php

namespace App\Models;

use CodeIgniter\Model;

class RegistrohistorialAccesos extends Model
{
    protected $table            = 'historial_accesos';
    protected $primaryKey       = 'idRegistrosEntrada';
 
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
   
      protected $allowedFields = ['Clientes_IDClientes', 'FechaHora_Acceso', 'Estatus_idEstatus','Motivo']; 


    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';



    
}
