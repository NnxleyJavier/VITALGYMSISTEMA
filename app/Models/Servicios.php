<?php

namespace App\Models;

use CodeIgniter\Model;

class Servicios extends Model
{
    protected $table            = 'servicios';
    protected $primaryKey       = 'IDServicios';
 
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
  
    protected $allowedFields    = ['NombreMembresia','LapsoDias','Costo',];




    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';


public function Buscar_Costo_Membresia(){


		$this->select('IDServicios,Costo,NombreMembresia');
		$query = $this->findAll();
		return $query;


	}


}
