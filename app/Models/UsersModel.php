<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
  
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
 
    protected $allowedFields    = ['username', 'active','Huella']; // Asegúrate de incluir 'Huella' aquí para permitir su inserción/actualización


    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';




}
