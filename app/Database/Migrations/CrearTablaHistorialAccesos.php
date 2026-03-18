<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaHistorialAccesos extends Migration
{
    public function up()
    {
        // 1. Definimos los campos de la nueva tabla
        $this->forge->addField([
            'idAcceso' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'Clientes_IDClientes' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            // DATETIME es perfecto porque guarda Fecha y Hora (ej. 2026-02-25 08:30:00)
            'FechaHora_Acceso' => [
                'type' => 'DATETIME',
            ],
            // Guardará 'Permitido' o 'Denegado'
            'Estatus_Acceso' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            // Guardará el por qué (Ej: 'Membresía Vigente' o 'Vencida hace 3 días')
            'Motivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true, 
            ],
        ]);

        // 2. Definimos la Llave Primaria
        $this->forge->addKey('idAcceso', true);

        // 3. Creamos la Relación (Llave Foránea) con la tabla Clientes
        // Si borras un cliente, se borra su historial (CASCADE)
        $this->forge->addForeignKey('Clientes_IDClientes', 'Clientes', 'IDClientes', 'CASCADE', 'CASCADE');

        // 4. Creamos la tabla
        $this->forge->createTable('Historial_Accesos');
    }

    public function down()
    {
        // Si hacemos rollback, simplemente borramos la tabla
        $this->forge->dropTable('Historial_Accesos', true);
    }
}