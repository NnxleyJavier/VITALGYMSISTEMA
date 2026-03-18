<?php

namespace App\Models;

use CodeIgniter\Model;

class AsistenciaChecador extends Model
{
    protected $table            = 'asistenciachecador';
    protected $primaryKey       = 'idAsistenciaChecador';

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['FechaHora_Registro_Entrada','FechaHora_Registro_Salida','users_id'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;


    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';


}
