<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaSolicitudesPrecioAmigo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idSolicitud' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'Clientes_IDClientes' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'Servicios_IDServicios' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'precio_solicitado' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
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
            'users_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true, // Coincide con la tabla users de Shield
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
        
        // Relaciones con las tablas principales
        $this->forge->addForeignKey('Clientes_IDClientes', 'clientes', 'IDClientes', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('Servicios_IDServicios', 'servicios', 'IDServicios', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('users_id', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('solicitudes_precio_amigo');
    }

    public function down()
    {
        $this->forge->dropForeignKey('solicitudes_precio_amigo', 'solicitudes_precio_amigo_Clientes_IDClientes_foreign');
        $this->forge->dropForeignKey('solicitudes_precio_amigo', 'solicitudes_precio_amigo_Servicios_IDServicios_foreign');
        $this->forge->dropForeignKey('solicitudes_precio_amigo', 'solicitudes_precio_amigo_users_id_foreign');
        $this->forge->dropTable('solicitudes_precio_amigo');
    }
}