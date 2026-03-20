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
        <p class="text-muted">Modifica manualmente la fecha de vencimiento de los miembros activos.</p>
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
</div>

<script>
    function guardarNuevaFecha(idRegistro) {
        var nuevaFecha = $('#fecha_' + idRegistro).val();
        
        if(!nuevaFecha) { alert("Por favor selecciona una fecha válida."); return; }
        if(!confirm("¿Estás seguro de cambiar la fecha de vencimiento?")) return;

        $.ajax({
            url: '<?= base_url("/actualizarFechaMembresia") ?>',
            type: 'POST',
            data: {
                id: idRegistro,
                fecha: nuevaFecha,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>' // Token CSRF actual (aunque lo ideal es refrescarlo dinámicamente)
            },
            success: function(response) {
                if(response.status === 'success') {
                    alert("✅ Fecha actualizada correctamente.");
                    if(response.token) { 
                        // Actualizamos token por si el usuario hace otro cambio inmediato
                        // Nota: En tu implementación real de JS deberías tener una variable global para el token si usas CSRF estricto
                    }
                } else {
                    alert("❌ Error: " + response.mensaje);
                }
            },
            error: function() {
                alert("Error de conexión con el servidor.");
            }
        });
    }
</script>