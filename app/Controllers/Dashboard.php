<?php

namespace App\Controllers;

// Hasta arriba en tu Controlador
use App\Models\PagoModel;
use App\Models\RegistroMembresiaModel; 
use App\Models\VentasproductosModel;


class Dashboard extends BaseController
{
    public function paginaPrincipal()
    {
    $username = obtener_username();
        $pagoModel = new PagoModel();
        $membresiaModel = new RegistroMembresiaModel(); 
        $ventasModel = new VentasproductosModel();


        // --- TAREA DE MANTENIMIENTO: Actualizar membresías vencidas ---
        // Esto es lo más optimizado porque se ejecuta en una sola consulta a la BD.
        // Cambia el estado a 'vencido' (0) para todas las membresías cuya fecha de fin ya pasó
        // y que todavía figuran como 'activas' (1), para mantener la integridad de los datos.
        $membresiaModel->where('Fecha_Fin <', date('Y-m-d'))
                       ->where('Estatus_idEstatus', 1)
                       ->set('Estatus_idEstatus', 2)
                       ->update();


        // 1. INGRESOS DE HOY
        $hoyMatriz = $pagoModel->ingresosMembresiasHoy('matriz');
        $hoyXoxo   = $pagoModel->ingresosMembresiasHoy('xoxo');
        $hoyTienda = $ventasModel->ingresosTiendaHoy();

        $ingresosHoy = [
            'membresias_matriz' => $hoyMatriz,
            'membresias_xoxo'   => $hoyXoxo,
            'membresias'        => $hoyMatriz + $hoyXoxo,
            'tienda'            => $hoyTienda,
            'total'             => $hoyMatriz + $hoyXoxo + $hoyTienda
        ];

        // --- NUEVO: MÉTODOS DE PAGO HOY ---
        $metodosHoy = [
            'efectivo'      => $pagoModel->ingresosMetodoPagoHoy('Efectivo'),
            'tarjeta'       => $pagoModel->ingresosMetodoPagoHoy('Tarjeta'),
            'TarjetaCredito' => $pagoModel->ingresosMetodoPagoHoy('TarjetaCredito'),
            'transferencia' => $pagoModel->ingresosMetodoPagoHoy('Transferencia')
        ];

        // 2. INGRESOS DEL MES
        $mesMatriz = $pagoModel->ingresosMembresiasMes('matriz');
        $mesXoxo   = $pagoModel->ingresosMembresiasMes('xoxo');
        $mesTienda = $ventasModel->ingresosTiendaMes();

        $ingresosMes = [
            'membresias_matriz' => $mesMatriz,
            'membresias_xoxo'   => $mesXoxo,
            'membresias'        => $mesMatriz + $mesXoxo,
            'tienda'            => $mesTienda,
            'total'             => $mesMatriz + $mesXoxo + $mesTienda
        ];

        // --- NUEVO: MÉTODOS DE PAGO MES ---
        $metodosMes = [
            'efectivo'      => $pagoModel->ingresosMetodoPagoMes('Efectivo'),
            'tarjeta'       => $pagoModel->ingresosMetodoPagoMes('Tarjeta'),
            'TarjetaCredito' => $pagoModel->ingresosMetodoPagoMes('TarjetaCredito'),
            'transferencia' => $pagoModel->ingresosMetodoPagoMes('Transferencia')
        ];

        // 3. MÉTRICAS DE MEMBRESÍAS (Se queda igual que antes)
        $counters = [
            'activos'      => ['total' => $membresiaModel->contarMembresias('activas', 'matriz') + $membresiaModel->contarMembresias('activas', 'xoxo'), 'matriz' => $membresiaModel->contarMembresias('activas', 'matriz'), 'xoxo' => $membresiaModel->contarMembresias('activas', 'xoxo')],
            'nuevas'       => ['total' => $membresiaModel->contarMembresias('nuevas', 'matriz') + $membresiaModel->contarMembresias('nuevas', 'xoxo'), 'matriz' => $membresiaModel->contarMembresias('nuevas', 'matriz'), 'xoxo' => $membresiaModel->contarMembresias('nuevas', 'xoxo')],
            'renovaciones' => ['total' => $membresiaModel->contarMembresias('renovaciones', 'matriz') + $membresiaModel->contarMembresias('renovaciones', 'xoxo'), 'matriz' => $membresiaModel->contarMembresias('renovaciones', 'matriz'), 'xoxo' => $membresiaModel->contarMembresias('renovaciones', 'xoxo')],
            'por_vencer'   => ['total' => $membresiaModel->contarMembresias('por_vencer', 'matriz') + $membresiaModel->contarMembresias('por_vencer', 'xoxo'), 'matriz' => $membresiaModel->contarMembresias('por_vencer', 'matriz'), 'xoxo' => $membresiaModel->contarMembresias('por_vencer', 'xoxo')],
            'vencidas'     => ['total' => $membresiaModel->contarMembresias('vencidas', 'matriz') + $membresiaModel->contarMembresias('vencidas', 'xoxo'), 'matriz' => $membresiaModel->contarMembresias('vencidas', 'matriz'), 'xoxo' => $membresiaModel->contarMembresias('vencidas', 'xoxo')]
        ];

        // 4. PREPARAR Y ENVIAR A LA VISTA
        $data = [
            'titulo'      => 'Panel Principal',
            'username'    => $username,
            'ingresosHoy' => $ingresosHoy,
            'metodosHoy'  => $metodosHoy,  // <-- Mandamos datos a la vista
            'ingresosMes' => $ingresosMes,
            'metodosMes'  => $metodosMes,  // <-- Mandamos datos a la vista
            'counters'    => $counters
        ];

        return view('html/main', $data)
             . view('html/plantilla_dashboard', $data)
             . view('html/footer');
    }

 // Asegúrate de importar el nuevo modelo hasta arriba del archivo:
    // use App\Models\SolicitudesCambioFechaModel;

    public function CambioFechas()
    {
        $username = obtener_username();
        $membresiaModel = new RegistroMembresiaModel();
        $solicitudesModel = new \App\Models\SolicitudesCambioFechaModel();
        
        $busqueda = $this->request->getGet('busqueda');
        $clientes = $membresiaModel->obtenerActivasParaCambioFecha($busqueda, 10);

        // Verificamos si es superadmin para mandar esa bandera a la vista
        $esSuperAdmin = auth()->user()->inGroup('superadmin');

        $data = [
            'titulo'   => 'Ajuste de Fechas | VitalGym',
            'username' => $username,
            'clientes' => $clientes,
            'pager'    => $membresiaModel->pager,
            'busqueda' => $busqueda,
            'esSuperAdmin' => $esSuperAdmin,
            'solicitudes'  => $solicitudesModel->obtenerPendientes() // Mandamos la lista de pendientes
        ];

        return view('html/main', $data)
             . view('html/CambiodeFechas', $data)
             . view('html/footer');
    }

  public function actualizarFechaMembresia()
    {
        $idRegistro = $this->request->getPost('id');
        $nuevaFecha = $this->request->getPost('fecha');
        $motivo     = $this->request->getPost('motivo'); // <-- 1. Recibimos el motivo

        // Validamos que vengan los 3 datos
        if (!$idRegistro || !$nuevaFecha || !$motivo) {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'Faltan datos o el motivo está vacío', 'token' => csrf_hash()]);
        }

        $membresiaModel = new RegistroMembresiaModel();
        $esSuperAdmin = auth()->user()->inGroup('superadmin');

        if ($esSuperAdmin) {
            // LÓGICA SUPERADMIN: Cambia directo
            // Opcional: Podrías guardar el motivo en un log de movimientos si lo deseas
            $actualizado = $membresiaModel->update($idRegistro, ['Fecha_Fin' => $nuevaFecha]);

            if ($actualizado) {
                return $this->response->setJSON(['status' => 'success', 'accion' => 'directo', 'token' => csrf_hash()]);
            }
        } else {
            // LÓGICA ADMIN: Crea la solicitud con el motivo
            $solicitudesModel = new \App\Models\SolicitudesCambioFechaModel();
            $membresiaActual = $membresiaModel->find($idRegistro);

            $solicitudesModel->insert([
                'registro_membresia_id' => $idRegistro,
                'users_id'              => auth()->user()->id,
                'fecha_fin_anterior'    => $membresiaActual['Fecha_Fin'],
                'fecha_fin_nueva'       => $nuevaFecha,
                'motivo'                => $motivo, // <-- 2. Guardamos el motivo en la BD
                'estado'                => 'Pendiente'
            ]);

            return $this->response->setJSON(['status' => 'success', 'accion' => 'solicitud', 'token' => csrf_hash()]);
        }

        return $this->response->setJSON(['status' => 'error', 'mensaje' => 'No se pudo procesar la solicitud', 'token' => csrf_hash()]);
    }

    public function procesarSolicitudFecha()
    {
        // Solo el superadmin puede autorizar
        if (!auth()->user()->inGroup('superadmin')) {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'No tienes permisos']);
        }

        $idSolicitud = $this->request->getPost('id');
        $accion = $this->request->getPost('accion'); // 'aprobar' o 'rechazar'

        $solicitudesModel = new \App\Models\SolicitudesCambioFechaModel();
        $membresiaModel = new RegistroMembresiaModel();
        
        $solicitud = $solicitudesModel->find($idSolicitud);

        if ($accion === 'aprobar') {
            // Actualizamos la fecha en la membresía real
            $membresiaModel->update($solicitud['registro_membresia_id'], ['Fecha_Fin' => $solicitud['fecha_fin_nueva']]);
            $solicitudesModel->update($idSolicitud, ['estado' => 'Aprobada']);
        } else {
            $solicitudesModel->update($idSolicitud, ['estado' => 'Rechazada']);
        }

        return $this->response->setJSON(['status' => 'success', 'token' => csrf_hash()]);
    }


    // 1. Carga la vista con la tabla de peticiones
    public function autorizarPrecios()
    {
        // Solo el superadmin puede entrar aquí
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->to('/')->with('error', 'No tienes permisos para acceder a esta área.');
        }

        $solicitudesModel = new \App\Models\SolicitudesPrecioAmigoModel();
        
        $data = [
            'titulo'      => 'Autorizar Precios Especiales | VitalGym',
            'username'    => obtener_username(),
            'solicitudes' => $solicitudesModel->obtenerPendientesConDetalles()
        ];

        return view('html/main', $data)
             . view('html/AutorizarPrecios', $data)
             . view('html/footer');
    }

    // 2. Procesa la aprobación o rechazo
public function procesarPrecioAmigoAjax()
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
        }

        $idSolicitud = $this->request->getPost('id');
        $accion      = $this->request->getPost('accion');

        $solicitudesModel = new \App\Models\SolicitudesPrecioAmigoModel();
        $solicitud = $solicitudesModel->find($idSolicitud);

        if ($accion === 'rechazar') {
            $solicitudesModel->update($idSolicitud, ['estado' => 'Rechazada']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Solicitud rechazada.']);
        }

        if ($accion === 'aprobar') {
            // Instanciamos los modelos para actualizar el pago original
            $pagoModel = new \App\Models\PagoModel();
            $servicioModel = model(\App\Models\Servicios::class);

            $servicio = $servicioModel->find($solicitud['Servicios_IDServicios']);
            
            // Armamos el nuevo concepto
            $nuevoConcepto = 'Precio Amigo Autorizado: ' . strtoupper($servicio['NombreMembresia']);

            // 1. VAMOS AL PAGO ORIGINAL Y LO ACTUALIZAMOS
            $pagoModel->update($solicitud['Pago_idPago'], [
                'Monto'    => $solicitud['precio_solicitado'],
                'Concepto' => $nuevoConcepto
            ]);

            // 2. Marcamos la solicitud como Aprobada
            $solicitudesModel->update($idSolicitud, ['estado' => 'Aprobada']);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Precio autorizado. El corte de caja se ha actualizado a la nueva cantidad.']);
        }
    }



   }