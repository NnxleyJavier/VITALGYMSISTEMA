<?php

namespace App\Models;

use CodeIgniter\Model;

class GymnasiosModel extends Model
{
    protected $table            = 'gymnasios';
    protected $primaryKey       = 'idGymnasios';

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['Nombre','Telefono','Estado','users_id'];


    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

}
