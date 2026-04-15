<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFirmaToClientes extends Migration
{
    public function up()
    {
        // Definimos las propiedades de la nueva columna
        $fields = [
            'Firma' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'Correo', // Esto coloca la columna justo después de "Correo"
            ],
        ];

        // Añadimos la columna a la tabla 'clientes'
        $this->forge->addColumn('clientes', $fields);
    }

    public function down()
    {
        // Si revertimos la migración, eliminamos la columna 'Firma' de la tabla 'clientes'
        $this->forge->dropColumn('clientes', 'Firma');
    }
}