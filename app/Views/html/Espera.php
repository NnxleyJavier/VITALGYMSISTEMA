<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    /* Estilos heredados de tu diseño original */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    .modal-registro-gym {
        font-family: 'Poppins', sans-serif;
        background-color: #ffffff;
        color: #333333;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border-top: 4px solid #4a90e2;
        border-bottom: 4px solid #4a90e2;
    }
    .form-section-title {
        font-size: 1.2em;
        color: #4a90e2;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 20px;
        margin-top: 0;
        font-weight: 600;
    }
</style>

<div class="container-fluid" style="padding-top: 20px;">
    <div class="panel panel-default shadow-sm border-0">
        <div class="panel-heading bg-white pt-4 pb-0">
            <h3 class="text-primary" style="margin-top: 0;"><i class="fas fa-users text-secondary"></i> Clientes en Espera de Pago</h3>
            <p class="text-muted small">Personas registradas en la tablet pendientes de cobro y huella.</p>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="tablaPendientes" class="table table-hover table-bordered w-100">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Teléfono</th>
                            <th>Firma Responsiva</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProcesar" tabindex="-1" role="dialog" aria-labelledby="modalProcesarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content modal-registro-gym">
      
      <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
            <span aria-hidden="true" style="font-size: 1.5em;">&times;</span>
        </button>
        <h3 class="modal-title text-center" id="modalProcesarLabel" style="font-weight: 600; color: #4a90e2;">Completar Inscripción</h3>
      </div>
      
      <form id="formProcesarPago">
          <div class="modal-body" style="padding: 25px 40px;">
              <input type="hidden" id="idClienteProcesar" name="id_cliente">
              
              <div class="alert alert-info" style="background-color: #f0f7ff; border-color: #cce5ff; color: #004085;">
                  <strong>Cliente en turno:</strong> <span id="nombreClienteModal" style="font-size: 1.2em; font-weight: 500;"></span>
              </div>

              <h4 class="form-section-title">Servicio y Pago</h4>
              
              <div class="row">
                  <div class="col-md-5">
                      <div class="form-group">
                          <label>Membresía Principal a Contratar:</label>
                          <select class="form-control" name="Servicios_IDServicios" id="selectServicio" required>
                              <option value="" disabled selected>Seleccione una membresía...</option>
                              <?php if(isset($servicios)): ?>
                                  <?php foreach($servicios as $servicio): ?>
                                      <option value="<?= $servicio['IDServicios'] ?>" data-costo="<?= $servicio['Costo'] ?? 0 ?>">
                                          <?= $servicio['NombreMembresia'] ?? 'Servicio' ?> - $<?= number_format($servicio['Costo'] ?? 0, 2) ?>
                                      </option>
                                  <?php endforeach; ?>
                              <?php endif; ?>
                          </select> 
                      </div>
                  </div>
                  
                  <div class="col-md-3">
                      <div class="form-group">
                          <label>Tipo de Pago:</label>
                          <select class="form-control" name="Tipo_Pago" required>
                              <option value="Efectivo">Efectivo</option>
                              <option value="Tarjeta">Tarjeta</option>
                              <option value="Transferencia">Transferencia</option>
                          </select>
                      </div>
                  </div>
                  
                  <div class="col-md-4">
                      <div class="form-group">
                          <label>Monto Total a Cobrar:</label>
                          <div class="input-group">
                              <span class="input-group-addon">$</span>
                              <input type="text" class="form-control" id="inputMonto" name="MontoTotal" placeholder="0.00" readonly>
                          </div>
                      </div>
                  </div>
              </div>

              <div class="row" style="margin-bottom: 20px;">
                  <div class="col-md-12">
                      <div id="contenedorServiciosExtra"></div>
                      
                      <button type="button" class="btn btn-info btn-sm" id="btnAgregarServicio" style="margin-top: 10px; background-color: #17a2b8; border:none; color: white;">
                          <span class="glyphicon glyphicon-plus"></span> Agregar servicio extra (Locker, Regadera...)
                      </button>
                  </div>
              </div>
          </div>
          
          <div class="modal-footer" style="border-top: 1px solid #eee; background-color: #fafafa; border-radius: 0 0 10px 10px;">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success" style="background-color: #28a745; border-color: #28a745;">
                <span class="glyphicon glyphicon-print"></span> Registrar y Generar Ticket
            </button>
          </div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const BASE_URL = '<?= base_url() ?>';
    
    // Convertimos el arreglo PHP de extras a un objeto JavaScript
    const EXTRAS_DISPONIBLES = <?= json_encode($extras ?? []) ?>;
    
    let tabla;
    $(document).ready(function() {
        tabla = $('#tablaPendientes').DataTable({
            "ajax": BASE_URL + "/obtenerPendientesAJAX",
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            "order": [[ 0, "desc" ]],
            "pageLength": 10
        });
    });
</script>

<script src="<?= base_url('assets/js/ventaMembresia2.js') ?>"></script>