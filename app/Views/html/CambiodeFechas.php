<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .card-panel { 
        font-family: 'Poppins', sans-serif;
        margin: 20px auto; 
        background-color: #ffffff; 
        padding: 30px; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        border-top: 5px solid #17a2b8; /* Color Cyan para diferenciarlo */
        width: 100%; 
        max-width: 1100px; 
    }

    .search-box {
        background-color: #f8f9fa;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
    }
    
    .table-custom th { background-color: #f8f9fa; text-transform: uppercase; font-size: 13px; color: #555; vertical-align: middle; }
    .table-custom td { vertical-align: middle; font-size: 14px; }
    
    .input-fecha-custom {
        border: 1px solid #ced4da;
        border-radius: 5px;
        padding: 5px 10px;
        width: 100%;
        color: #495057;
    }

    /* Paginación */
    .pagination { display: flex; justify-content: center; list-style: none; padding: 0; margin-top: 20px; }
    .pagination li { margin: 0 4px; }
    .pagination li a, .pagination li span { padding: 8px 14px; border: 1px solid #ddd; border-radius: 5px; color: #17a2b8; text-decoration: none; }
    .pagination li.active a { background-color: #17a2b8; color: white; border-color: #17a2b8; }
</style>

<div class="container-fluid">
    <div class="card-panel">
        <h3 style="color: #17a2b8; font-weight: 600; text-transform: uppercase; margin-top: 0;">
            <span class="glyphicon glyphicon-calendar"></span> Ajuste de Fechas (Administrador)
        </h3>
        <p class="text-muted">Modifica manualmente la fecha de vencimiento de los miembros activos. Solo se muestran membresías activas.</p>
        <hr>

        <!-- BUSCADOR -->
        <div class="search-box">
            <form action="<?= base_url('/CambioFechas') ?>" method="GET" class="form-inline">
                <div class="form-group" style="width: 100%; display: flex; gap: 10px;">
                    <input type="text" name="busqueda" class="form-control" placeholder="Buscar por Nombre o Teléfono..." value="<?= esc($busqueda) ?>" style="flex-grow: 1; border-radius: 6px;">
                    <button type="submit" class="btn btn-info" style="border-radius: 6px;">
                        <span class="glyphicon glyphicon-search"></span> Buscar
                    </button>
                    <?php if(!empty($busqueda)): ?>
                        <a href="<?= base_url('/CambioFechas') ?>" class="btn btn-default" style="border-radius: 6px;">Limpiar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-custom">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th style="width: 250px;">Fecha de Vencimiento</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($clientes) && is_array($clientes)): ?>
                        <?php foreach($clientes as $cliente): ?>
                            <tr>
                                <td><strong><?= esc($cliente['Nombre'] . ' ' . $cliente['ApellidoP']) ?></strong></td>
                                <td><?= esc($cliente['Telefono']) ?></td>
                                <td>
                                    <!-- Input Date con la fecha actual del cliente -->
                                  <input type="date" class="input-fecha-custom" id="fecha_<?= $cliente['idRegistros_Membresia'] ?>" value="<?= date('Y-m-d', strtotime($cliente['Fecha_Fin'])) ?>">

                                    <input type="text" id="motivo_<?= $cliente['idRegistros_Membresia'] ?>" class="form-control mt-2" placeholder="Ej. Reposición por enfermedad..." style="font-size: 12px; margin-top: 5px;">
                                </td>
                                <td class="text-center">
                                    <button onclick="guardarNuevaFecha(<?= $cliente['idRegistros_Membresia'] ?>)" class="btn btn-sm btn-success">
                                        <span class="glyphicon glyphicon-floppy-disk"></span> Guardar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center" style="padding: 30px; color: #777;">No se encontraron membresías activas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <?= $pager->links() ?>
        </div>
    </div>

<div class="card-panel" style="border-top: 5px solid #ffc107; margin-top: 30px;">
    <h4 style="color: #ffc107; font-weight: 600;"><span class="glyphicon glyphicon-time"></span> Solicitudes de Cambio Pendientes</h4>
    <p class="text-muted" style="font-size: 13px;">Peticiones de los encargados en espera de autorización del Superadmin.</p>
    
    <div class="table-responsive">
        <table class="table table-hover table-custom mt-3">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Solicitado por</th>
                    <th>Fecha Anterior</th>
                     <th>Motivo</th>
                    <th>Fecha Solicitada</th>
                    <?php if($esSuperAdmin): ?>
                        <th class="text-center">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($solicitudes)): ?>
                    <?php foreach($solicitudes as $solicitud): ?>
                        <tr>
                            <td><strong><?= esc($solicitud['Nombre'] . ' ' . $solicitud['ApellidoP']) ?></strong></td>
                            <td><?= esc($solicitud['username']) ?></td>
                            <td><span style="color: #dc3545; text-decoration: line-through;"><?= date('d/m/Y', strtotime($solicitud['fecha_fin_anterior'])) ?></span></td>
                            <td><span style="color: #28a745; font-weight: bold;"><?= date('d/m/Y', strtotime($solicitud['fecha_fin_nueva'])) ?></span></td>
                            <td><?= esc($solicitud['motivo']) ?></td>
                            <?php if($esSuperAdmin): ?>
                            <td class="text-center">
                                <button class="btn btn-sm btn-success" onclick="procesarSolicitud(<?= $solicitud['idSolicitud'] ?>, 'aprobar')"><span class="glyphicon glyphicon-ok"></span></button>
                                <button class="btn btn-sm btn-danger" onclick="procesarSolicitud(<?= $solicitud['idSolicitud'] ?>, 'rechazar')"><span class="glyphicon glyphicon-remove"></span></button>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No hay solicitudes pendientes.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>

  function guardarNuevaFecha(idRegistro) {
        var nuevaFecha = $('#fecha_' + idRegistro).val();
        var motivoCambio = $('#motivo_' + idRegistro).val(); // <-- Capturamos el motivo
        
        if(!nuevaFecha) { alert("Por favor selecciona una fecha válida."); return; }
        if(!motivoCambio || motivoCambio.trim() === "") { alert("Debes escribir un motivo para justificar el cambio."); return; } // <-- Validamos
        if(!confirm("¿Estás seguro de solicitar el cambio de fecha?")) return;

        $.ajax({
            url: '<?= base_url("/actualizarFechaMembresia") ?>',
            type: 'POST',
            data: {
                id: idRegistro,
                fecha: nuevaFecha,
                motivo: motivoCambio, // <-- Lo mandamos al controlador
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>' 
            },
            success: function(response) {
                if(response.status === 'success') {
                    if(response.accion === 'directo') {
                        alert("✅ Fecha actualizada y aplicada inmediatamente.");
                    } else {
                        alert("⏳ Solicitud enviada al Superadmin para su autorización.");
                    }
                    location.reload(); 
                } else {
                    alert("❌ Error: " + response.mensaje);
                }
            }
        });
    }
    // Nueva función para aprobar o rechazar (Solo Superadmin)
    function procesarSolicitud(idSolicitud, accionMenu) {
        let texto = accionMenu === 'aprobar' ? "¿Aprobar este cambio de fecha?" : "¿Rechazar esta solicitud?";
        if(!confirm(texto)) return;

        $.ajax({
            url: '<?= base_url("/procesarSolicitudFecha") ?>',
            type: 'POST',
            data: {
                id: idSolicitud,
                accion: accionMenu,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(response) {
                if(response.status === 'success') {
                    alert("✅ Solicitud procesada correctamente.");
                    location.reload();
                } else {
                    alert("❌ Error: " + response.mensaje);
                }
            }
        });
    }
</script>