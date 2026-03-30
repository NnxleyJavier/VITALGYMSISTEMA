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


/**
     * Obtiene las asistencias en un rango de fechas, uniendo con la tabla users
     */
   /**
     * Obtiene las asistencias filtrando por fechas y opcionalmente por un usuario en específico
     */
    public function obtenerAsistenciasPorRango($fechaInicio, $fechaFin, $userId = 'todos')
    {
        $builder = $this->select('asistenciachecador.*, users.username')
                    ->join('users', 'users.id = asistenciachecador.users_id')
                    ->where('DATE(FechaHora_Registro_Entrada) >=', $fechaInicio)
                    ->where('DATE(FechaHora_Registro_Entrada) <=', $fechaFin);

        // Si el administrador seleccionó a alguien en particular, filtramos por su ID
        if ($userId !== 'todos' && !empty($userId)) {
            $builder->where('asistenciachecador.users_id', $userId);
        }

        return $builder->orderBy('FechaHora_Registro_Entrada', 'DESC')->findAll();
    }

}
