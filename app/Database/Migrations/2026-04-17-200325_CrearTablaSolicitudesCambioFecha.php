<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaSolicitudesCambioFecha extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idSolicitud' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'registro_membresia_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'users_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true, // Coincide con la estructura de Shield para users
            ],
            'fecha_fin_anterior' => [
                'type' => 'DATETIME',
            ],
            'fecha_fin_nueva' => [
                'type' => 'DATETIME',
            ],
            'motivo' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['Pendiente', 'Aprobada', 'Rechazada'],
                'default'    => 'Pendiente',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('idSolicitud', true);
        
        // Relación con la membresía que se quiere modificar (Ajustado al nombre de tu PK)
        $this->forge->addForeignKey('registro_membresia_id', 'registros_membresia', 'idRegistros_Membresia', 'CASCADE', 'CASCADE');
        // Relación con el encargado que hace la solicitud
        $this->forge->addForeignKey('users_id', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('solicitudes_cambio_fecha');
    }

    public function down()
    {
        // Si necesitas revertir, primero se eliminan las llaves foráneas para evitar errores
        $this->forge->dropForeignKey('solicitudes_cambio_fecha', 'solicitudes_cambio_fecha_registro_membresia_id_foreign');
        $this->forge->dropForeignKey('solicitudes_cambio_fecha', 'solicitudes_cambio_fecha_users_id_foreign');
        
        $this->forge->dropTable('solicitudes_cambio_fecha');
    }
}