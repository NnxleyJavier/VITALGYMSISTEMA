<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModificarGymnasiosYCrearAsistencia extends Migration
{
    public function up()
    {
        // =========================================================
        // 1. CREAR TABLA: AsistenciaChecador
        // =========================================================
        $this->forge->addField([
            'idAsistenciaChecador' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'FechaHora_Registro_Entrada' => [
                'type' => 'DATETIME',
            ],
            // TIP: Lo pongo en NULL temporalmente porque cuando el usuario 
            // entra, aún no tiene hora de salida registrada.
            'FechaHora_Registro_Salida' => [
                'type' => 'DATETIME',
                'null' => true, 
            ],
            'users_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true, // Debe ser UNSIGNED igual que el ID de 'users'
            ],
        ]);

        $this->forge->addKey('idAsistenciaChecador', true);
        
        // Llave foránea hacia la tabla 'users'
        $this->forge->addForeignKey('users_id', 'users', 'id', 'NO ACTION', 'NO ACTION');
        
        $this->forge->createTable('AsistenciaChecador');


        // =========================================================
        // 2. MODIFICAR TABLA: Gymnasios (Agregar users_id y FK)
        // =========================================================
        
        $nuevaColumna = [
            'users_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                // Nota: Lo ponemos como null=>true inicialmente por si ya tienes 
                // gimnasios guardados. Si lo pones NOT NULL y hay datos, MySQL dará error.
                'null'       => true, 
                'after'      => 'Estado'
            ],
        ];
        
        // Agregamos la columna
        $this->forge->addColumn('Gymnasios', $nuevaColumna);

        // Agregamos la restricción de llave foránea mediante SQL crudo
        // Es la forma más segura de hacerlo en tablas que ya existen en CI4
        $this->db->query('ALTER TABLE `Gymnasios` ADD CONSTRAINT `fk_Gymnasios_users1` FOREIGN KEY (`users_id`) REFERENCES `users`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION');
    }

    public function down()
    {
        // 1. Revertir Gymnasios (Primero quitamos la llave foránea, luego la columna)
        $this->db->query('ALTER TABLE `Gymnasios` DROP FOREIGN KEY `fk_Gymnasios_users1`');
        $this->forge->dropColumn('Gymnasios', 'users_id');

        // 2. Borrar tabla AsistenciaChecador
        $this->forge->dropTable('AsistenciaChecador', true);
    }
}