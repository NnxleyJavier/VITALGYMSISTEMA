<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AgregarHuellaAUsers extends Migration
{
    public function up()
    {
        // Definimos el nuevo campo que queremos agregar
        $nuevoCampo = [
            'Huella' => [
                'type' => 'TEXT',
                'null' => true, // Permite nulos para no romper los usuarios existentes
                'after' => 'username' // Lo colocará justo después del nombre de usuario en la BD
            ],
        ];

        // Le decimos a Forge que agregue la columna a la tabla 'users'
        $this->forge->addColumn('users', $nuevoCampo);
    }

    public function down()
    {
        // Si hacemos rollback, simplemente borramos la columna
        $this->forge->dropColumn('users', 'Huella');
    }
}