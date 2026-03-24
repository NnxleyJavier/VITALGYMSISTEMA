<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EstructuraGym extends Migration
{
    public function up()
    {
        // ---------------------------------------------------
        // 1. TABLAS CATÁLOGO (Sin dependencias)
        // ---------------------------------------------------

        // Tabla: Estatus
        $this->forge->addField([
            'idEstatus'         => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'EstadodeMembresia' => ['type' => 'VARCHAR', 'constraint' => 45],
        ]);
        $this->forge->addKey('idEstatus', true);
        $this->forge->createTable('Estatus');

        // Tabla: Gymnasios
        $this->forge->addField([
            'idGymnasios' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'Nombre'      => ['type' => 'VARCHAR', 'constraint' => 45],
            'Telefono'    => ['type' => 'VARCHAR', 'constraint' => 45],
            'Estado'      => ['type' => 'VARCHAR', 'constraint' => 45],
        ]);
        $this->forge->addKey('idGymnasios', true);
        $this->forge->createTable('Gymnasios');

        // Tabla: Servicios
        $this->forge->addField([
            'IDServicios'     => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'NombreMembresia' => ['type' => 'VARCHAR', 'constraint' => 45],
            'LapsoDias'       => ['type' => 'INT', 'constraint' => 11],
            'Costo'           => ['type' => 'DECIMAL', 'constraint' => '10,2'],
        ]);
        $this->forge->addKey('IDServicios', true);
        $this->forge->createTable('Servicios');

        // Tabla: Clientes
        $this->forge->addField([
            'IDClientes'    => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'Nombre'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'ApellidoP'     => ['type' => 'VARCHAR', 'constraint' => 45],
            'ApellidoM'     => ['type' => 'VARCHAR', 'constraint' => 45],
            // TIP: Cambié Telefono a VARCHAR para evitar errores con los ceros iniciales o ladas
            'Telefono'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true], 
            'Correo'        => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'Huella'        => ['type' => 'TEXT', 'null' => true],
            'Fecha_Ingreso' => ['type' => 'DATE'],
            'Acepta_WhatsApp' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addKey('IDClientes', true);
        $this->forge->createTable('Clientes');

        // Tabla: Pago
        $this->forge->addField([
            'idPago'     => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'Tipo_Pago'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'Concepto'   => ['type' => 'VARCHAR', 'constraint' => 40],
            'Monto'      => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'Fecha_Pago' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('idPago', true);
        $this->forge->createTable('Pago');

        // ---------------------------------------------------
        // 2. TABLAS INTERMEDIAS Y TRANSACCIONALES (Con FKs)
        // ---------------------------------------------------

        // Tabla: Gymnasios_has_Servicios
        $this->forge->addField([
            'Gymnasios_idGymnasios' => ['type' => 'INT', 'constraint' => 11],
            'Servicios_IDServicios' => ['type' => 'INT', 'constraint' => 11],
        ]);
        $this->forge->addForeignKey('Gymnasios_idGymnasios', 'Gymnasios', 'idGymnasios', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('Servicios_IDServicios', 'Servicios', 'IDServicios', 'CASCADE', 'CASCADE');
        $this->forge->createTable('Gymnasios_has_Servicios');

        // Tabla: Registros_Membresia
        $this->forge->addField([
            'idRegistros_Membresia' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'Clientes_IDClientes'   => ['type' => 'INT', 'constraint' => 11],
            'Pago_idPago'           => ['type' => 'INT', 'constraint' => 11],
            'Estatus_idEstatus'     => ['type' => 'INT', 'constraint' => 11],
            'Servicios_IDServicios' => ['type' => 'INT', 'constraint' => 11],
            'Fecha_Inicio'          => ['type' => 'DATE'],
            'Fecha_Fin'             => ['type' => 'DATE'],
            'Aviso_Enviado'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addKey('idRegistros_Membresia', true);
        $this->forge->addForeignKey('Clientes_IDClientes', 'Clientes', 'IDClientes', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('Pago_idPago', 'Pago', 'idPago', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('Estatus_idEstatus', 'Estatus', 'idEstatus', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('Servicios_IDServicios', 'Servicios', 'IDServicios', 'CASCADE', 'CASCADE');
        $this->forge->createTable('registros_membresia');
    }

    public function down()
    {
        // El orden de borrado debe ser inverso para no romper las llaves foráneas
        $this->forge->dropTable('registros_membresia', true);
        $this->forge->dropTable('Gymnasios_has_Servicios', true);
        $this->forge->dropTable('Pago', true);
        $this->forge->dropTable('Clientes', true);
        $this->forge->dropTable('Servicios', true);
        $this->forge->dropTable('Gymnasios', true);
        $this->forge->dropTable('Estatus', true);
    }
}