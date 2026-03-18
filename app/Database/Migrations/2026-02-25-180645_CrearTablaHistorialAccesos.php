<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaHistorialAccesos extends Migration
{
public function up()
    {
        $this->forge->addField([
            'idRegistrosEntrada' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'Clientes_IDClientes' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'FechaHora_Acceso' => [
                'type' => 'DATETIME',
            ],
            'Estatus_idEstatus' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'Motivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true, 
            ],
        ]);

        // Llave primaria principal
        $this->forge->addKey('idRegistrosEntrada', true);

        // Llaves Foráneas (Relaciones)
        $this->forge->addForeignKey('Clientes_IDClientes', 'Clientes', 'IDClientes', 'NO ACTION', 'NO ACTION');
        $this->forge->addForeignKey('Estatus_idEstatus', 'Estatus', 'idEstatus', 'NO ACTION', 'NO ACTION');

        // Crear la tabla
        $this->forge->createTable('Historial_Accesos');
    }

    public function down()
    {
        $this->forge->dropTable('Historial_Accesos', true);
    }
}