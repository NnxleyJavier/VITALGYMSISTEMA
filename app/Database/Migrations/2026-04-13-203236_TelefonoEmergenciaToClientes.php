<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TelefonoEmergenciaToClientes extends Migration
{
    public function up()
    {
        $fields = [
            'Telefono_Emergencia' => [
                'type'       => 'VARCHAR',
                'constraint' => '20', // 20 caracteres es suficiente para números telefónicos
                'null'       => true,
                'after'      => 'Contacto_Emergencia', // Se colocará justo después del nombre del contacto
            ],
        ];

        $this->forge->addColumn('clientes', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('clientes', 'Telefono_Emergencia');
    }
}