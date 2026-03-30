<div class="card shadow-vital rounded-vital border-0 mb-4 bg-white">
                <div class="card-body p-4">
                    <form id="formFiltroAsistencia" class="row g-3 align-items-end">
                        <?= csrf_field() ?> 
                        
                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold mb-2">Staff</label>
                        <select name="usuario" class="form-control" style="border-radius: 10px; height: 45px; padding: 10px 15px;">
                                <option value="todos" <?= $usuarioSeleccionado == 'todos' ? 'selected' : '' ?>>Todos los usuarios</option>
                                <?php foreach($usuarios as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= $usuarioSeleccionado == $u['id'] ? 'selected' : '' ?>>
                                        <?= esc($u['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                        </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold mb-2">Fecha Inicio:</label>
                            <input type="date" class="form-control" name="fecha_inicio" value="<?= $fechaInicio ?>" style="border-radius: 10px; padding: 10px 15px;">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold mb-2">Fecha Fin:</label>
                            <input type="date" class="form-control" name="fecha_fin" value="<?= $fechaFin ?>" style="border-radius: 10px; padding: 10px 15px;">
                        </div>
                        <div class="col-md-3 d-flex" style="gap: 10px;">
                            <button type="button" id="btnFiltrar" class="btn btn-dark fw-bold w-100 shadow-sm" style="border-radius: 10px; padding: 11px 10px !important;">
                                <span class="glyphicon glyphicon-search"></span> Filtrar
                            </button>
                            <button type="button" id="btnExportar" class="btn btn-success fw-bold w-100 shadow-sm" style="border-radius: 10px; padding: 11px 10px !important;">
                                <span class="glyphicon glyphicon-export"></span> Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-vital rounded-vital border-0 bg-white">
                <div class="card-body p-4">
                    <div class="table-responsive">
                    <table id="tablaAsistenciasCompleta" class="table table-hover align-middle">
                            <thead class="text-muted" style="border-bottom: 2px solid #f4f6f9;">
                                <tr>
                                    <th class="pb-3 text-start">Miembro del Staff</th>
                                    <th class="pb-3 text-center">Día</th> <th class="pb-3 text-center">Fecha</th>
                                    <th class="pb-3 text-center">Hora Entrada</th>
                                    <th class="pb-3 text-center">Hora Salida</th>
                                    <th class="pb-3 text-center">Tiempo Trabajado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAsistencias">
                                <?php if(empty($asistencias)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <span class="glyphicon glyphicon-calendar" style="font-size: 3rem; opacity: 0.3; display:block; margin-bottom:15px;"></span>
                                            No hay registros de asistencia.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    // Arreglo para la carga principal en PHP
                                    $diasEspanol = [
                                        'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 
                                        'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
                                    ];
                                    
                                    foreach($asistencias as $a): 
                                        $entrada = new DateTime($a['FechaHora_Registro_Entrada']);
                                        $salida = !empty($a['FechaHora_Registro_Salida']) ? new DateTime($a['FechaHora_Registro_Salida']) : null;
                                        
                                        $diaTraducido = $diasEspanol[$entrada->format('l')];
                                        
                                        $horaSalida = $salida ? $salida->format('h:i A') : '<span class="badge bg-warning text-dark p-2" style="border-radius: 8px;">En turno</span>';
                                        $horasTrabajadas = $salida ? $entrada->diff($salida)->format('%h h %i min') : '<span class="text-muted">-</span>';
                                    ?>
                                    <tr>
                                        <td class="text-start fw-bold text-dark" style="font-size: 1.05rem;">
                                            <span class="glyphicon glyphicon-user text-muted" style="margin-right: 10px;"></span>
                                            <?= esc($a['username']) ?>
                                        </td>
                                        <td class="text-center fw-bold text-secondary"><?= $diaTraducido ?></td>
                                        
                                        <td class="text-center"><?= $entrada->format('d/m/Y') ?></td>
                                        <td class="text-center text-success fw-bold"><?= $entrada->format('h:i A') ?></td>
                                        <td class="text-center text-danger fw-bold"><?= $horaSalida ?></td>
                                        <td class="text-center text-primary fw-bold"><?= $horasTrabajadas ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

<script>
    $('#btnFiltrar').on('click', function() {
        // Obtenemos los datos del formulario (Fechas, Usuario y CSRF Token)
        let datosFiltro = $('#formFiltroAsistencia').serialize();
        
        // Cambiamos el texto del botón temporalmente
        let btn = $(this);
        let textoOriginal = btn.html();
        btn.html('<span class="glyphicon glyphicon-refresh"></span> Cargando...').prop('disabled', true);

        // Hacemos la petición AJAX
        $.post('<?= base_url('/verAsistencias') ?>', datosFiltro, function(res) {
            if(res.status === 'success') {
                let html = '';
                
            if(res.datos.length === 0) {
                    html = `<tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <span class="glyphicon glyphicon-calendar" style="font-size: 3rem; opacity: 0.3; display:block; margin-bottom:15px;"></span>
                                    No se encontraron registros en estas fechas.
                                </td>
                            </tr>`;
                } else {
                    res.datos.forEach(function(item) {
                        html += `<tr>
                                    <td class="text-start fw-bold text-dark" style="font-size: 1.05rem;">
                                        <span class="glyphicon glyphicon-user text-muted" style="margin-right: 10px;"></span>
                                        ${item.username}
                                    </td>
                                    <td class="text-center fw-bold text-secondary">${item.dia}</td>
                                    <td class="text-center">${item.fecha}</td>
                                    <td class="text-center text-success fw-bold">${item.entrada}</td>
                                    <td class="text-center text-danger fw-bold">${item.salida}</td>
                                    <td class="text-center text-primary fw-bold">${item.trabajado}</td>
                                </tr>`;
                    });
                }
                
                // Inyectamos el nuevo HTML en la tabla sin recargar la página
                $('#tablaAsistencias').html(html);
            }
            
            // Restauramos el botón
            btn.html(textoOriginal).prop('disabled', false);
            
        }, 'json').fail(function() {
            alert("Ocurrió un error de conexión al filtrar los datos.");
            btn.html(textoOriginal).prop('disabled', false);
        });
    });
    // --- EXPORTAR A EXCEL ---
    $('#btnExportar').on('click', function() {
        // 1. Obtenemos la tabla por su ID
        let tabla = document.getElementById("tablaAsistenciasCompleta");
        
        // 2. Convertimos la tabla HTML a un libro de Excel
        let libro = XLSX.utils.table_to_book(tabla, {sheet: "Asistencias"});
        
        // 3. Obtenemos las fechas para ponerlas en el nombre del archivo
        let fechaInicio = $('input[name="fecha_inicio"]').val();
        let fechaFin = $('input[name="fecha_fin"]').val();
        let nombreArchivo = `Reporte_Staff_${fechaInicio}_al_${fechaFin}.xlsx`;
        
        // 4. Descargamos el archivo
        XLSX.writeFile(libro, nombreArchivo);
    });
</script>