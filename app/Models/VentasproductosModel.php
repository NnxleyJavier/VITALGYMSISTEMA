<?php

namespace App\Models;

use CodeIgniter\Model;

class VentasproductosModel extends Model
{
    protected $table            = 'ventas_productos';
    protected $primaryKey       = 'idVentaProducto';
   
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
  
    protected $allowedFields    = ['Producto_idProducto', 'users_id', 'Cantidad_Vendida', 'Total_Venta', 'Fecha_Venta'];

   

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';


public function procesarVenta(array $productos, int $idUsuario): bool
    {
        $productoModel = new ProductoModel();

        $this->db->transStart();

        foreach ($productos as $item) {
            $idProducto = $item['id'];
            $cantidad   = $item['cantidad'];
            $precio     = $item['precio'];
            $totalVenta = $cantidad * $precio;

            // 1. VALIDACIÓN DE SEGURIDAD: Consultar el stock actual en tiempo real
            $productoDB = $productoModel->find($idProducto);
            
            // Si el producto no existe o la cantidad solicitada es mayor al stock...
            if (!$productoDB || $productoDB['Stock'] < $cantidad) {
                // Cancelamos todo el proceso de inmediato
                $this->db->transRollback();
                return false; 
            }

            // 2. Insertamos la venta 
            $this->insert([
                'Producto_idProducto' => $idProducto,
                'users_id'            => $idUsuario,
                'Cantidad_Vendida'    => $cantidad,
                'Total_Venta'         => $totalVenta,
                'Fecha_Venta'         => date('Y-m-d H:i:s')
            ]);

            // 3. Descontamos el stock
            $productoModel->set('Stock', 'Stock - ' . (int)$cantidad, false)
                          ->where('idProducto', $idProducto)
                          ->update();
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
    
    // ====================================================================
    // CONSULTAS PARA EL DASHBOARD: VENTAS DE LA TIENDA
    // ====================================================================

    public function ingresosTiendaHoy()
    {
        $hoy = date('Y-m-d');
        
        // Sumamos la columna 'Total_Venta' filtrando por las horas de hoy
        $row = $this->select('SUM(Total_Venta) as total_monto')
                    ->where('Fecha_Venta >=', $hoy . ' 00:00:00')
                    ->where('Fecha_Venta <=', $hoy . ' 23:59:59')
                    ->first();

        return $row['total_monto'] ?? 0.00;
    }

    public function ingresosTiendaMes()
    {
        $mesActual = date('m');
        $anioActual = date('Y');
        
        $row = $this->select('SUM(Total_Venta) as total_monto')
                    ->where('MONTH(Fecha_Venta)', $mesActual)
                    ->where('YEAR(Fecha_Venta)', $anioActual)
                    ->first();

        return $row['total_monto'] ?? 0.00;
    }
}
