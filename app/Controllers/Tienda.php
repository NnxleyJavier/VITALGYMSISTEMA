<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Tienda extends BaseController
{
    public function index()
    {
        
        $productoModel = new \App\Models\ProductoModel();

            $data['productos'] = $productoModel->where('Stock >', 0)->get()->getResult();;
            $data['titulo'] = 'Tienda - VitalGym';

           return view('html/main', $data)
             . view('html/Tiendaview', $data)
             . view('html/footer');
    }
public function registrarVenta()
    {
        // 1. Verificamos que sea una petición AJAX
        if ($this->request->isAJAX()) {
            
            $productos = $this->request->getPost('productos');

            if (empty($productos)) {
                return $this->response->setJSON([
                    'status'  => 'error', 
                    'mensaje' => 'El carrito está vacío.'
                ]);
            }

            // Obtenemos el ID del usuario en sesión
            $idUsuario =  auth()->id() ?? 2; 

            // 2. Instanciamos el modelo de ventas
            $ventasModel = new \App\Models\VentasproductosModel();

            // 3. Le pasamos la responsabilidad al Modelo
            $ventaExitosa = $ventasModel->procesarVenta($productos, $idUsuario);

            // 4. Respondemos a la vista según el resultado
            if (!$ventaExitosa) {
                return $this->response->setJSON([
                    'status'  => 'error', 
                    'mensaje' => 'Error al guardar la venta en la base de datos.'
                ]);
            }

            return $this->response->setJSON([
                'status'  => 'success', 
                'mensaje' => 'Venta procesada y stock actualizado correctamente.'
            ]);
        }
        
        return $this->response->setStatusCode(400)->setBody('Petición no válida');
    }


    // --- 1. FUNCIÓN PARA MOSTRAR LA VISTA DE INVENTARIO ---
    public function inventario()
    {
        $productoModel = new \App\Models\ProductoModel();

        // Usamos findAll() para traer todos, incluso los que tienen Stock 0
        $data['productos'] = $productoModel->findAll();
        $data['titulo'] = 'Inventario - VitalGym';

        return view('html/main', $data)
             . view('html/Inventarioview', $data)
             . view('html/footer');
    }


    
    // --- 2. FUNCIÓN PARA GUARDAR O ACTUALIZAR PRODUCTOS ---
    public function guardarProducto()
    {
        if ($this->request->isAJAX()) {
            $productoModel = new \App\Models\ProductoModel();

            // Recibimos los datos del formulario
            $datos = [
                'Nombre' => $this->request->getPost('nombre'),
                'Precio' => $this->request->getPost('precio'),
                'Stock'  => $this->request->getPost('stock')
            ];

            // Si el formulario envió un ID, lo agregamos al arreglo para que el Modelo sepa que es un UPDATE
            $idProducto = $this->request->getPost('idProducto');
            if (!empty($idProducto)) {
                $datos['idProducto'] = $idProducto;
            }

            // Llamamos a la función que creamos en el modelo
            if ($productoModel->gestionarProducto($datos)) {
                return $this->response->setJSON(['status' => 'success', 'mensaje' => 'Inventario actualizado correctamente.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'mensaje' => 'Error al guardar el producto.']);
            }
        }
        return $this->response->setStatusCode(400)->setBody('Petición no válida');
    }


}
