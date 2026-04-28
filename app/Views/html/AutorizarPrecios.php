<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .card-panel { 
        font-family: 'Poppins', sans-serif;
        margin: 20px auto; 
        background-color: #ffffff; 
        padding: 30px; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        border-top: 5px solid #ff9800; 
        width: 100%; 
        max-width: 1100px; 
    }
    
    .precio-destacado { font-size: 18px; font-weight: 700; color: #ff9800; }
    .motivo-box { font-size: 13px; color: #666; background: #f8f9fa; padding: 10px; border-radius: 8px; border-left: 3px solid #ff9800; }
</style>

<div class="container-fluid">
    <div class="card-panel">
        <h3 style="color: #ff9800; font-weight: 600; margin-top: 0;">
            <span class="glyphicon glyphicon-star"></span> Autorización de Precios Especiales
        </h3>
        <p class="text-muted">Revisa las solicitudes enviadas por recepción. Al aprobarlas, la membresía se activará automáticamente.</p>
        <hr>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Membresía</th>
                        <th>Solicitante</th>
                        <th class="text-center">Precio Propuesto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($solicitudes)): ?>
                        <?php foreach($solicitudes as $sol): ?>
                            <tr id="fila_<?= $sol['idSolicitud'] ?>">
                                <td style="vertical-align: middle;"><?= date('d/m/Y h:i A', strtotime($sol['created_at'])) ?></td>
                                <td style="vertical-align: middle;"><strong><?= esc($sol['ClienteNombre'] . ' ' . $sol['ClienteApellido']) ?></strong></td>
                                <td style="vertical-align: middle;">
                                    <span class="label label-info" style="font-size: 12px;"><?= esc($sol['NombreMembresia']) ?></span>
                                    <div class="motivo-box mt-2" style="margin-top: 8px;">
                                        <strong>Motivo:</strong> <?= esc($sol['motivo']) ?>
                                    </div>
                                </td>
                                <td style="vertical-align: middle;"><?= esc($sol['Solicitante']) ?></td>
                                <td class="text-center" style="vertical-align: middle;">
                                    <span class="precio-destacado">$<?= number_format($sol['precio_solicitado'], 2) ?></span>
                                </td>
                                <td class="text-center" style="vertical-align: middle; min-width: 120px;">
                                    <button class="btn btn-sm btn-success" onclick="procesarSolicitud(<?= $sol['idSolicitud'] ?>, 'aprobar')" title="Aprobar y Activar">
                                        <span class="glyphicon glyphicon-ok"></span>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="procesarSolicitud(<?= $sol['idSolicitud'] ?>, 'rechazar')" title="Rechazar">
                                        <span class="glyphicon glyphicon-remove"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 40px;">No hay solicitudes de precios especiales pendientes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function procesarSolicitud(idSolicitud, accion) {
    let titulo = accion === 'aprobar' ? '¿Aprobar precio especial?' : '¿Rechazar solicitud?';
    let texto = accion === 'aprobar' ? 'Se registrará el pago y se activará la membresía del cliente.' : 'La solicitud será eliminada.';
    let colorBtn = accion === 'aprobar' ? '#28a745' : '#dc3545';

    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: colorBtn,
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url("/procesarPrecioAmigoAjax") ?>',
                type: 'POST',
                data: {
                    id: idSolicitud,
                    accion: accion,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if(response.status === 'success') {
                        Swal.fire('¡Listo!', response.message, 'success').then(() => {
                            $('#fila_' + idSolicitud).fadeOut(); // Ocultamos la fila sin recargar la página
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
}
</script>