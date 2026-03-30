<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablasTienda extends Migration
{
    public function up()
    {
        // ---------------------------------------------------
        // 1. TABLA: Productos (Tu inventario)
        // ---------------------------------------------------
        $this->forge->addField([
            'idProducto' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'Nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'Precio' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'Stock' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0, 
            ],
                'Imagen' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true, // Permite que el campo sea nulo
                ],
        ]);
        $this->forge->addKey('idProducto', true);
        $this->forge->createTable('Productos');

        // ---------------------------------------------------
        // 2. TABLA: Ventas_Productos (Tu historial de ingresos)
        // ---------------------------------------------------
        $this->forge->addField([
            'idVentaProducto' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'Producto_idProducto' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            // Vinculación directa al usuario que realiza la venta
            'users_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true, // Obligatorio para relacionar con la tabla 'users' de Shield/IonAuth
            ],
            'Cantidad_Vendida' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'Total_Venta' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'Fecha_Venta' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('idVentaProducto', true);
        
        // Llaves Foráneas
        $this->forge->addForeignKey('Producto_idProducto', 'Productos', 'idProducto', 'CASCADE', 'CASCADE');
        // Relación con tu tabla de usuarios
        $this->forge->addForeignKey('users_id', 'users', 'id', 'NO ACTION', 'NO ACTION');
        
        $this->forge->createTable('Ventas_Productos');
    }

    public function down()
    {
        $this->forge->dropTable('Ventas_Productos', true);
        $this->forge->dropTable('Productos', true);
    }
}