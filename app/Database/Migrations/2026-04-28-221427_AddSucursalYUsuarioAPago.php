<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSucursalYUsuarioAPago extends Migration
{
    public function up()
    {
        // 1. Agregamos las columnas a la tabla 'pago'
        $campos = [
            'id_gimnasio' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'users_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true, // En Shield, los IDs de usuario son unsigned
                'null'       => true,
            ],
        ];

        $this->forge->addColumn('pago', $campos);

        // 2. Asignamos a todos los pagos viejos la sucursal 1 y al usuario 1 (Superadmin) 
        // para que no haya datos nulos que afecten gráficas anteriores
        $this->db->query("UPDATE `pago` SET `id_gimnasio` = 1, `users_id` = 1 WHERE `id_gimnasio` IS NULL");

        // 3. Agregamos las Restricciones (Llaves Foráneas) reales a nivel BD
        $this->db->query('ALTER TABLE `pago` ADD CONSTRAINT `fk_pago_gymnasio` FOREIGN KEY (`id_gimnasio`) REFERENCES `gymnasios`(`idGymnasios`) ON DELETE SET NULL ON UPDATE CASCADE;');
        $this->db->query('ALTER TABLE `pago` ADD CONSTRAINT `fk_pago_user` FOREIGN KEY (`users_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;');
    }

    public function down()
    {
        // Si necesitas revertir la migración, borramos las llaves y luego las columnas
        $this->db->query('ALTER TABLE `pago` DROP FOREIGN KEY `fk_pago_gymnasio`;');
        $this->db->query('ALTER TABLE `pago` DROP FOREIGN KEY `fk_pago_user`;');
        
        $this->forge->dropColumn('pago', ['id_gimnasio', 'users_id']);
    }
}