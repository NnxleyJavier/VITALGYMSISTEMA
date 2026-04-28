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

    <div class="panel panel-default shadow-sm border-0" style="margin-bottom: 30px; border-top: 4px solid #17a2b8;">
        <div class="panel-body" style="padding: 25px;">
            <div class="row">
                <div class="col-md-5">
                    <h4 style="color: #17a2b8; font-weight: 600; margin-top: 0;"><i class="fas fa-search"></i> Consulta Rápida</h4>
                    <p class="text-muted small">Busca a un socio por su ID o Teléfono para ver su vigencia.</p>
                    
                    <div class="input-group">
                        <span class="input-group-addon" style="background: white;"><i class="fas fa-user"></i></span>
                        <input type="text" id="inputBusquedaSocio" class="form-control" placeholder="ID o Teléfono..." onkeypress="if(event.keyCode==13) buscarSocioRapido();">
                        <div class="input-group-btn">
                            <button class="btn btn-info" onclick="buscarSocioRapido()">Buscar</button>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div id="resultadoConsulta" style="display: none; background: #f8f9fa; border-radius: 10px; padding: 15px; border: 1px solid #e9ecef;">
                        <div class="row">
                            <div class="col-xs-8">
                                <h4 id="resNombre" style="margin: 0; font-weight: 700; color: #333;">Nombre Cliente</h4>
                                <p class="text-muted mb-0" style="font-size: 13px;"><i class="fas fa-phone"></i> <span id="resTelefono"></span> | ID: <span id="resID"></span></p>
                                <hr style="margin: 10px 0;">
                                <p style="margin: 0; font-weight: 600; color: #555;" id="resMembresia"><i class="fas fa-id-card"></i> Membresía</p>
                                <p style="font-size: 12px; color: #777; margin: 0;">Vence el: <strong id="resFechaFin"></strong></p>
                            </div>
                            <div class="col-xs-4 text-center" style="display: flex; flex-direction: column; justify-content: center;">
                                <div id="badgeDias" style="padding: 10px; border-radius: 10px; color: white; font-weight: bold; text-align: center;">
                                    <span style="font-size: 24px; display: block; line-height: 1;" id="resDias">0</span>
                                    <span style="font-size: 11px; text-transform: uppercase;">Días</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="mensajeErrorConsulta" class="alert alert-danger" style="display: none; margin-top: 15px; padding: 10px;"></div>
                </div>
            </div>
        </div>
    </div>

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

            <div class="form-group mt-3" style="border: 1px solid #ff9800; padding: 15px; border-radius: 8px; background: #fffdf5; margin-bottom: 20px;">
                <div class="checkbox" style="margin: 0;">
                    <label style="font-weight: 600; color: #d84315; font-size: 14px;">
                        <input type="checkbox" id="checkAmigoRecepcion" onchange="$('#panelAmigoRecepcion').slideToggle();"> 
                        <span class="glyphicon glyphicon-star"></span> Solicitar Precio Especial al Superadmin
                    </label>
                </div>
                
                <div id="panelAmigoRecepcion" style="display: none; margin-top: 15px; border-top: 1px dashed #ffb74d; padding-top: 15px;">
                    <div class="row">
                        <div class="col-sm-4">
                            <label style="font-size: 13px;">Precio Propuesto ($)</label>
                            <input type="number" step="0.50" name="precio_amigo" class="form-control" placeholder="Ej. 250" style="border-radius: 6px;">
                        </div>
                        <div class="col-sm-8">
                            <label style="font-size: 13px;">Motivo</label>
                            <input type="text" name="motivo_amigo" class="form-control" placeholder="Ej. Familiar, convenio..." style="border-radius: 6px;">
                        </div>
                    </div>
                    <small class="text-muted" style="display: block; margin-top: 10px; line-height: 1.2;">
                        * Cobra e imprime el ticket de manera normal. El ajuste en caja se hará tras la autorización.
                    </small>
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
        // 1. Inicializamos la tabla
        tabla = $('#tablaPendientes').DataTable({
            "ajax": BASE_URL + "/obtenerPendientesAJAX",
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            "order": [[ 0, "desc" ]],
            "pageLength": 10
        });

        // =========================================================
        // 2. ACTUALIZACIÓN AUTOMÁTICA SILENCIOSA CADA 5 SEGUNDOS
        // =========================================================
        setInterval(function() {
            if(tabla) {
                // El parámetro 'null' mantiene los datos actuales si falla, 
                // y el 'false' evita que la paginación o búsqueda se reinicien.
                tabla.ajax.reload(null, false); 
            }
        }, 5000);
        // =========================================================
    });

    function buscarSocioRapido() {
    let termino = $('#inputBusquedaSocio').val();
    
    if(!termino) {
        alert("Por favor ingresa un ID o Teléfono.");
        return;
    }

    // Ocultamos resultados previos
    $('#resultadoConsulta').hide();
    $('#mensajeErrorConsulta').hide();

    $.ajax({
        url: BASE_URL + "/consultaRapidaSocio",
        type: "POST",
        data: {
            termino: termino,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
       success: function(response) {
            if(response.status === 'success') {
                // Llenamos los datos básicos
                $('#resNombre').text(response.cliente.Nombre + ' ' + response.cliente.ApellidoP);
                $('#resTelefono').text(response.cliente.Telefono || 'Sin teléfono');
                $('#resID').text(response.cliente.IDClientes);

                if(response.tiene_membresia) {
                    $('#resMembresia').html('<i class="fas fa-id-card"></i> ' + response.membresia.NombreMembresia);
                    
                    let fechaPartes = response.membresia.Fecha_Fin.split(' ')[0].split('-');
                    $('#resFechaFin').text(fechaPartes[2] + '/' + fechaPartes[1] + '/' + fechaPartes[0]);
                    
                    let numDias = response.dias_restantes;
                    let badge = $('#badgeDias');
                    
                    if(response.estado_vigencia === 'activa' || response.estado_vigencia === 'vence_hoy') {
                        $('#resDias').text(response.estado_vigencia === 'vence_hoy' ? 'HOY' : numDias);
                        badge.css('background-color', response.estado_vigencia === 'vence_hoy' ? '#fd7e14' : '#28a745');
                        
                        // ALERTA DE ÉXITO (Reemplaza los textos)
                        Swal.fire({
                            icon: 'success',
                            title: '¡Acceso Concedido!',
                            text: 'La asistencia se ha registrado en el sistema.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                    } else {
                        $('#resDias').text(Math.abs(numDias));
                        badge.css('background-color', '#dc3545');
                        $('#resDias').siblings('span').text('Días Vencido');

                        // ALERTA DE BLOQUEO
                        Swal.fire({
                            icon: 'error',
                            title: 'Acceso Denegado',
                            text: 'La membresía está vencida.',
                        });
                    }
                } else {
                    $('#resMembresia').html('<i class="fas fa-exclamation-triangle"></i> Sin membresía registrada');
                    $('#resFechaFin').text('N/A');
                    $('#resDias').text('-');
                    $('#badgeDias').css('background-color', '#6c757d').find('span:last').text('Sin Datos');
                    
                    Swal.fire('Atención', 'Este socio no tiene membresías registradas.', 'warning');
                }

                $('#resultadoConsulta').fadeIn();
                $('#inputBusquedaSocio').val(''); 
                
            } else {
                $('#mensajeErrorConsulta').html('<i class="fas fa-times-circle"></i> ' + response.message).fadeIn();
            }
        },
        error: function() {
            alert("Error de comunicación con el servidor.");
        }
    });
}
</script>

<script src="<?= base_url('assets/js/ventaMembresia2.js') ?>"></script>