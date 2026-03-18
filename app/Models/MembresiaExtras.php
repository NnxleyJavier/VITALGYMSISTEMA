<?php

namespace App\Models;

use CodeIgniter\Model;

class MembresiaExtras extends Model
{
    protected $table            = 'membresia_extras';
    protected $primaryKey       = 'idExtra';
   
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
  
    protected $allowedFields    = ['Registros_Membresia_id','Servicios_IDServicios'];



    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';


}
