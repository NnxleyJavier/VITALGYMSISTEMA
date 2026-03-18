<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .card-tabla { 
        font-family: 'Poppins', sans-serif;
        margin: 15px auto 30px; 
        background-color: #ffffff; 
        color: #333333; 
        padding: 25px 40px; 
        border-radius: 10px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        border-top: 4px solid #28a745; /* Verde para destacar que es un área de ingresos/renovaciones */
        width: 100%; 
        max-width: 1200px; 
    }
    
    .card-tabla hr { border-color: #f0f0f0; margin-bottom: 20px; }
    
    .section-title { 
        font-size: 16px; 
        color: #28a745; 
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .table-custom {
        margin-top: 20px;
    }
    
    .table-custom thead th {
        background-color: #f8f9fa;
        color: #555;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 1px;
        border-bottom: 2px solid #dee2e6;
    }

    .table-custom tbody td {
        vertical-align: middle;
        font-size: 14px;
        color: #444;
    }

    .btn-whatsapp {
        background-color: #25D366;
        color: white;
        font-weight: 600;
        border: none;
        border-radius: 5px;
        padding: 6px 12px;
        transition: all 0.3s ease;
    }

    .btn-whatsapp:hover {
        background-color: #1ebe57;
        color: white;
        box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
    }
    
    .badge-dias {
        background-color: #ffc107;
        color: #000;
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
    }

    /* Estilos personalizados para la Paginación de CodeIgniter 4 */
    .pagination { 
        display: flex; 
        justify-content: center; 
        list-style: none; 
        padding: 0; 
        margin-top: 25px; 
    }
    
    .pagination li { 
        margin: 0 4px; 
    }
    
    .pagination li a, 
    .pagination li span {
        display: inline-block;
        color: #28a745; /* Color verde a juego con la tarjeta */
        padding: 8px 16px; 
        text-decoration: none;
        background-color: #fff;
        border: 1px solid #e0e0e0; 
        border-radius: 6px; 
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .pagination li.active a,
    .pagination li.active span { 
        background-color: #28a745; 
        color: white; 
        border-color: #28a745; 
        box-shadow: 0 2px 5px rgba(40, 167, 69, 0.3);
    }
    
    .pagination li a:hover { 
        background-color: #f8f9fa; 
        color: #1e7e34;
        border-color: #28a745;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card-tabla">
            <h3 class="section-title text-center">Próximos Vencimientos</h3>
            <p class="text-center text-muted" style="font-size: 13px;">Clientes a los que les faltan 3 días o menos para renovar.</p>
            <hr>

            <div class="table-responsive">
                <table class="table table-hover table-custom">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Días Restantes</th>
                            <th>Fecha de Fin</th>
                            <th class="text-center">Acción (WhatsApp)</th>
                        </tr>
                    </thead>
<tbody>
                        <?php if(!empty($clientesProximos) && is_array($clientesProximos)): ?>
                            <?php foreach($clientesProximos as $cliente): ?>
                                <tr id="fila-<?= $cliente['idRegistros_Membresia'] ?>">
                                    <td>
                                        <strong><?= esc($cliente['Nombre'] . ' ' . $cliente['ApellidoP']) ?></strong>
                                    </td>
                                    <td><?= esc($cliente['Telefono']) ?></td>
                                    <td><span class="badge-dias"><?= esc($cliente['DiasRestantes']) ?> días</span></td>
                                    <td><?= esc(date('d/m/Y', strtotime($cliente['Fecha_Fin']))) ?></td>
                                    <td class="text-center">
                                        <?php if($cliente['Acepta_WhatsApp'] == 1 && !empty($cliente['Telefono'])): ?>
                                            <?php 
                                                $numeroLimpio = "52" . preg_replace('/[^0-9]/', '', $cliente['Telefono']);
                                                $mensaje = "¡Hola " . trim($cliente['Nombre']) . "! 👋 Tu membresía en VitalGym vence en " . $cliente['DiasRestantes'] . " días. ¡Te esperamos para renovar! 💪";
                                                $urlWA = "https://wa.me/" . $numeroLimpio . "?text=" . urlencode($mensaje);
                                            ?>
                                            <a href="<?= $urlWA ?>" target="_blank" class="btn btn-whatsapp btn-sm" onclick="marcarComoEnviado(<?= $cliente['idRegistros_Membresia'] ?>)">
                                                <span class="glyphicon glyphicon-send"></span> Enviar Aviso
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 12px;"><span class="glyphicon glyphicon-ban-circle text-danger"></span> No acepta/Sin número</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted" style="padding: 30px;">No hay membresías por vencer.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <?= $pager->links() ?>
            </div>

        </div>
    </div>
</div>

<script>
    function marcarComoEnviado(idRegistro) {
        // 1. Hacemos la petición silenciosa al servidor
        $.post("<?= base_url('/marcarAvisoEnviado') ?>", {
            idRegistro: idRegistro,
            // Enviamos el token CSRF por seguridad basándonos en tu AppConfig
            "<?= csrf_token() ?>": "<?= csrf_hash() ?>" 
        }, function(respuesta) {
            if(respuesta.status === 'success') {
                // 2. Si todo salió bien en la BD, ocultamos la fila con una animación suave
                $("#fila-" + idRegistro).fadeOut("slow");
            }
        });
    }
</script>