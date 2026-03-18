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
            <form action="<?= base_url('/renovaciones/panel') ?>" method="GET" class="form-inline">
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
            <table class="table table-hover table-custom">
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
                                    <?php if($cliente['DiasRestantes'] < 0): ?>
                                        <span class="badge-vencido">Vencido hace <?= abs($cliente['DiasRestantes']) ?> días</span>
                                    <?php elseif($cliente['DiasRestantes'] == 0): ?>
                                        <span class="badge-vencido" style="background-color: #fd7e14;">¡Vence Hoy!</span>
                                    <?php else: ?>
                                        <span class="badge-porvencer">Faltan <?= $cliente['DiasRestantes'] ?> días</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($cliente['Fecha_Fin'])) ?></td>
                                <td class="text-center">
                                <a href="<?= base_url('/renovacionesRegistro/' . $cliente['IDClientes']) ?>" class="btn btn-sm btn-warning">
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

        // 1. Evitamos que el formulario recargue la página si dan "Enter"
        $('form').on('submit', function(e) {
            e.preventDefault();
            let valor = $('input[name="telefono"]').val().replace(/[^0-9]/g, '');
            buscarClientes(valor);
        });

        // 2. Detectamos cada vez que el usuario teclea algo en el buscador
        $('input[name="telefono"]').on('input', function() {
            
            // Forzamos a que solo se puedan escribir números (limpia letras al vuelo)
            let valor = $(this).val().replace(/[^0-9]/g, '');
            $(this).val(valor);

            // Reiniciamos el reloj si el usuario sigue tecleando rápido
            clearTimeout(temporizador);

            // 3. Evaluamos: ¿Ya hay 8 o más números? ¿O borró todo para ver la lista completa?
            if (valor.length >= 8 || valor.length === 0) {
                
                // Usamos un retraso (debounce) de 300ms para no saturar tu base de datos
                temporizador = setTimeout(function() {
                    buscarClientes(valor);
                }, 300);
            }
        });

        // 4. La función que va a buscar los datos a tu Controlador
        function buscarClientes(telefono) {
            // Efecto visual: opacamos un poco la tabla para indicar que está "pensando"
            $('.table-custom tbody').css('opacity', '0.4');

            $.ajax({
                url: "<?= base_url('/renovaciones') ?>",
                type: "GET",
                data: { telefono: telefono },
                success: function(respuesta) {
                    // MAGIA JQUERY: El servidor nos devuelve la página entera, pero 
                    // nosotros "recortamos" solo los datos de la tabla y la paginación.
                    let nuevoCuerpoTabla = $(respuesta).find('.table-custom tbody').html();
                    let nuevaPaginacion = $(respuesta).find('#contenedor-paginacion').html();
                    
                    // Inyectamos los nuevos resultados y regresamos la opacidad a la normalidad
                    $('.table-custom tbody').html(nuevoCuerpoTabla).css('opacity', '1');
                    $('#contenedor-paginacion').html(nuevaPaginacion);

                    // Opcional y elegante: Actualizamos la URL del navegador sin recargar la página.
                    // Así, si el usuario refresca la página (F5), no pierde su búsqueda.
                    let nuevaUrl = telefono ? "?telefono=" + telefono : window.location.pathname;
                    window.history.pushState({}, '', nuevaUrl);
                },
                error: function() {
                    $('.table-custom tbody').css('opacity', '1');
                    console.log("Error al realizar la búsqueda silenciosa.");
                }
            });
        }
    });
</script>