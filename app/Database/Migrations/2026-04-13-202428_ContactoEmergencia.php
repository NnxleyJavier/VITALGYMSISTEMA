<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ContactoEmergencia extends Migration
{
    public function up()
    {
        // Definimos la nueva columna para el contacto de emergencia
        $fields = [
            'Contacto_Emergencia' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'Telefono', // Se colocará justo después del teléfono del cliente
            ],
        ];

        // Añadimos la columna a la tabla clientes
        $this->forge->addColumn('clientes', $fields);
    }

    public function down()
    {
        // Por si necesitas revertir la migración
        $this->forge->dropColumn('clientes', 'Contacto_Emergencia');
    }
}