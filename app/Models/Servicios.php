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


    /**
     * Obtiene los servicios filtrados por la sucursal del usuario activo
     * y por el tipo de catálogo (MEMBRESIA o EXTRAS)
     */
    public function obtenerServiciosPorSucursal($tipoCatalogo = 'MEMBRESIA')
    {
        // 1. Cargamos el helper para saber quién está solicitando la info
        helper('gym'); 
        $idGymActual = obtener_id_gimnasio();

        // 2. Si es Superadmin (o no tiene restricción), le damos todo el catálogo
        if ($idGymActual === 'TODOS' || $idGymActual === null) {
            return $this->where('Catalogo', $tipoCatalogo)->findAll();
        } 
        
        // 3. Si es un encargado, hacemos el JOIN para darle solo lo de su sucursal
        return $this->select('servicios.*')
            ->join('gymnasios_has_servicios', 'gymnasios_has_servicios.Servicios_IDServicios = servicios.IDServicios')
            ->where('gymnasios_has_servicios.Gymnasios_idGymnasios', $idGymActual)
            ->where('servicios.Catalogo', $tipoCatalogo)
            ->findAll();
    }


}
