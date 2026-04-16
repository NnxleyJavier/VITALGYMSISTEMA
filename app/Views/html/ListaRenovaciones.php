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

    .search-box {
        background-color: #f8f9fa;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
    }

    .badge-vencido { background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
    .badge-porvencer { background-color: #ffc107; color: black; padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
    
    .table-custom th { background-color: #f8f9fa; text-transform: uppercase; font-size: 13px; color: #555; }
    .table-custom td { vertical-align: middle; font-size: 14px; }
    
    /* Paginación de CodeIgniter */
    .pagination { display: flex; justify-content: center; list-style: none; padding: 0; margin-top: 20px; }
    .pagination li { margin: 0 4px; }
    .pagination li a, .pagination li span { padding: 8px 14px; border: 1px solid #ddd; border-radius: 5px; color: #ff9800; text-decoration: none; }
    .pagination li.active a { background-color: #ff9800; color: white; border-color: #ff9800; }
</style>

<div class="container-fluid">
    <div class="card-panel">
        <h3 style="color: #ff9800; font-weight: 600; text-transform: uppercase; margin-top: 0;">
            <span class="glyphicon glyphicon-list-alt"></span> Control de Renovaciones
        </h3>
        <p class="text-muted">Clientes con membresías vencidas o próximas a vencer (5 días).</p>
        <hr>

        <div class="search-box">
            <form action="<?= base_url('/renovaciones') ?>" method="GET" class="form-inline" id="form-busqueda">
                <div class="form-group" style="width: 100%; display: flex; gap: 10px;">
                    <input type="text" name="telefono" class="form-control" placeholder="Buscar por número de teléfono..." value="<?= esc($busqueda) ?>" style="flex-grow: 1; border-radius: 6px;">
                    <button type="submit" class="btn btn-default" style="background-color: #6c757d; color: white; border: none; border-radius: 6px;">
                        <span class="glyphicon glyphicon-search"></span> Buscar
                    </button>
                    <?php if(!empty($busqueda)): ?>
                        <a href="<?= base_url('/renovaciones') ?>" class="btn btn-default" style="border-radius: 6px;">Limpiar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-custom" id="tabla-renovaciones">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Fecha Fin</th>
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
                                    <?php if($cliente['DiasVencidos'] > 0): ?>
                                        <span class="badge-vencido" style="background-color: #dc3545; color: white; padding: 3px 8px; border-radius: 4px; font-weight: bold;">
                                            Vencido hace <?= $cliente['DiasVencidos'] ?> días
                                        </span>
                                    
                                    <?php elseif($cliente['DiasVencidos'] == 0): ?>
                                        <span class="badge-vencido" style="background-color: #fd7e14; color: white; padding: 3px 8px; border-radius: 4px; font-weight: bold;">
                                            ¡Vence Hoy!
                                        </span>
                                    
                                    <?php else: ?>
                                        <span class="badge-porvencer" style="background-color: #17a2b8; color: white; padding: 3px 8px; border-radius: 4px; font-weight: bold;">
                                            Faltan <?= abs($cliente['DiasVencidos']) ?> días
                                        </span>
                                    <?php endif; ?>
                                    
                                    <br>
                                    <small style="color: #888; font-size: 11px;">(Fecha: <?= date('d/m/Y', strtotime($cliente['Fecha_Fin'])) ?>)</small>
                                </td>
                                <td><?= date('d/m/Y', strtotime($cliente['Fecha_Fin'])) ?></td>
                                <td class="text-center">
                                <a href="<?= base_url('/renovacionesRegistro' . $cliente['IDClientes']) ?>" class="btn btn-sm btn-warning">
                                 <span class="glyphicon glyphicon-refresh"></span> Renovar
                                </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 30px; color: #777;">
                                No se encontraron clientes pendientes de renovación.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="contenedor-paginacion" style="text-align: center; margin-top: 20px;">
            <?= $pager->links() ?>
        </div>

    </div>
</div>

<script>
    $(document).ready(function() {
        let temporizador;
        // Definimos la URL base a la que siempre le haremos las peticiones
        const urlBase = "<?= base_url('/renovaciones') ?>";

        // ==========================================
        // 1. EVENTO: EL USUARIO ESCRIBE EN EL BUSCADOR
        // ==========================================
        $('input[name="telefono"]').on('input', function() {
            let valor = $(this).val().replace(/[^0-9]/g, '');
            $(this).val(valor);

            clearTimeout(temporizador);

            if (valor.length >= 8 || valor.length === 0) {
                temporizador = setTimeout(function() {
                    // Armamos la URL con el parámetro de búsqueda
                    let urlDestino = urlBase + (valor ? "?telefono=" + valor : "");
                    cargarPaginaAjax(urlDestino);
                }, 300);
            }
        });

        // ==========================================
        // 2. EVENTO: EL USUARIO DA "ENTER" O CLIC EN BUSCAR
        // ==========================================
        $('#form-busqueda').on('submit', function(e) {
            e.preventDefault();
            let valor = $('input[name="telefono"]').val().replace(/[^0-9]/g, '');
            let urlDestino = urlBase + (valor ? "?telefono=" + valor : "");
            cargarPaginaAjax(urlDestino);
        });

        // ==========================================
        // 3. EVENTO: EL USUARIO HACE CLIC EN LA PAGINACIÓN (NUEVO)
        // ==========================================
        // Usamos $(document).on para que jQuery escuche los clics incluso en los botones nuevos que lleguen por AJAX
        $(document).on('click', '#contenedor-paginacion a', function(e) {
            e.preventDefault(); // Evitamos que el navegador recargue la página
            let urlPagina = $(this).attr('href'); // Extraemos la URL del botón (ej. ?page=2)
            cargarPaginaAjax(urlPagina);
        });

        // ==========================================
        // 4. FUNCIÓN MAESTRA AJAX
        // ==========================================
        function cargarPaginaAjax(url) {
            $('#tabla-renovaciones tbody').css('opacity', '0.4');

            $.ajax({
                url: url,
                type: "GET",
                success: function(respuesta) {
                    // Recortamos la tabla y el paginador de la respuesta HTML
                    let nuevoCuerpoTabla = $(respuesta).find('#tabla-renovaciones tbody').html();
                    let nuevaPaginacion = $(respuesta).find('#contenedor-paginacion').html();
                    
                    // Pegamos los datos frescos en nuestra pantalla
                    $('#tabla-renovaciones tbody').html(nuevoCuerpoTabla).css('opacity', '1');
                    $('#contenedor-paginacion').html(nuevaPaginacion);

                    // Actualizamos la URL en la barra de direcciones sin recargar
                    window.history.pushState({}, '', url);
                },
                error: function() {
                    $('#tabla-renovaciones tbody').css('opacity', '1');
                    console.log("Error al realizar la petición asíncrona.");
                }
            });
        }
    });
</script>