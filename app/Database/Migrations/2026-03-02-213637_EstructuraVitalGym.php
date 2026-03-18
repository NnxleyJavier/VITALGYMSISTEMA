<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TablaMembresiaExtras extends Migration
{
    public function up()
    {
        // Definir los campos de la nueva tabla
        $this->forge->addField([
            'idExtra' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'Registros_Membresia_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'Servicios_IDServicios' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
        ]);

        // Llave primaria
        $this->forge->addKey('idExtra', true);

        // Llaves foráneas
        $this->forge->addForeignKey('Registros_Membresia_id', 'registros_membresia', 'idRegistros_Membresia', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('Servicios_IDServicios', 'servicios', 'IDServicios', 'CASCADE', 'CASCADE');

        // Crear la tabla
        $this->forge->createTable('membresia_extras');
    }

    public function down()
    {
        // Eliminar la tabla si hacemos un rollback
        $this->forge->dropTable('membresia_extras', true);
    }
}