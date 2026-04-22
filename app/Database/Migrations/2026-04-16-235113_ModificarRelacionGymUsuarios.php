<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModificarRelacionGymUsuarios extends Migration
{
    public function up()
    {
        // =======================================================
        // 1. AGREGAR COLUMNA 'id_gimnasio' A LA TABLA 'users'
        // =======================================================
        $this->forge->addColumn('users', [
            'id_gimnasio' => [
                'type'       => 'INT',
                'constraint' => 11,
                // Si la llave primaria idGymnasios es UNSIGNED, descomenta la siguiente línea:
                // 'unsigned'   => true, 
                'null'       => true,
            ]
        ]);

        // =======================================================
        // 2. CREAR LA LLAVE FORÁNEA EN 'users'
        // (Usamos DB Query porque en tablas ya existentes es más exacto y seguro)
        // =======================================================
        $this->db->query('ALTER TABLE `users` ADD CONSTRAINT `fk_user_gym` FOREIGN KEY (`id_gimnasio`) REFERENCES `gymnasios`(`idGymnasios`) ON DELETE SET NULL ON UPDATE CASCADE');

// =======================================================
        // 3. ELIMINAR LA COLUMNA VIEJA 'users_id' DE 'gymnasios'
        // =======================================================
        
        // Primero eliminamos la llave foránea que nos indicó el error
        $this->db->query('ALTER TABLE `gymnasios` DROP FOREIGN KEY `fk_Gymnasios_users1`');
        
        // Ahora sí, borramos la columna tranquilamente
        $this->forge->dropColumn('gymnasios', 'users_id');
    }

    public function down()
    {
        // =======================================================
        // REVERSIÓN: QUÉ HACER SI ALGUIEN EJECUTA "php spark migrate:rollback"
        // =======================================================
        
        // 1. Agregamos de vuelta la columna a gymnasios
        $this->forge->addColumn('gymnasios', [
            'users_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true, // Shield usa unsigned por defecto para users
                'null'       => true,
            ]
        ]);

        // 2. Eliminamos la llave foránea y la columna nueva de users
        $this->db->query('ALTER TABLE `users` DROP FOREIGN KEY `fk_user_gym`');
        $this->forge->dropColumn('users', 'id_gimnasio');
    }
}