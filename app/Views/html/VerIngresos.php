<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .card-panel { 
        font-family: 'Poppins', sans-serif;
        margin: 20px auto; 
        background-color: #ffffff; 
        padding: 30px; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        border-top: 5px solid #28a745; /* Verde para asistencias */
        width: 100%; 
        max-width: 900px; 
    }

    .filtro-fecha {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .filtro-fecha label {
        font-weight: 600;
        color: #555;
        margin: 0;
        font-size: 15px;
    }

    .input-fecha {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 8px 15px;
        font-family: 'Poppins', sans-serif;
        color: #333;
        font-weight: 500;
        outline: none;
    }

    .input-fecha:focus {
        border-color: #28a745;
        box-shadow: 0 0 5px rgba(40, 167, 69, 0.3);
    }
</style>

<div class="container-fluid">
    <div class="card-panel">
        <h3 style="color: #28a745; font-weight: 600; text-transform: uppercase; margin-top: 0;">
            <span class="glyphicon glyphicon-check"></span> Registro de Asistencias
        </h3>
        <p class="text-muted">Consulta los socios que han ingresado al gimnasio por día.</p>
        <hr>

        <div class="filtro-fecha">
            <label for="fechaConsulta"><span class="glyphicon glyphicon-calendar"></span> Seleccionar Fecha:</label>
            <input type="date" id="fechaConsulta" class="input-fecha" value="<?= esc($fecha) ?>" onchange="cambiarFecha(this.value)">
            
            <?php if($fecha == date('Y-m-d')): ?>
                <span class="label label-success" style="padding: 6px 10px; border-radius: 20px; font-size: 12px;">Mostrando Hoy</span>
            <?php else: ?>
                <a href="<?= base_url('/verAsistencias') ?>" class="btn btn-sm btn-default" style="border-radius: 20px;">Volver a Hoy</a>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th class="text-center" style="width: 15%;">Hora de Entrada</th>
                        <th>Socio</th>
                        <th>Teléfono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($asistencias) && is_array($asistencias)): ?>
                        <?php foreach($asistencias as $asistencia): ?>
                            <tr>
                                <td class="text-center" style="font-weight: bold; color: #495057;">
                                    <span class="glyphicon glyphicon-time" style="color: #28a745; margin-right: 5px;"></span>
                                    <?= date('H:i A', strtotime($asistencia['fecha_hora'])) ?>
                                </td>
                                <td style="vertical-align: middle;">
                                    <?= esc($asistencia['Nombre'] . ' ' . $asistencia['ApellidoP']) ?>
                                </td>
                                <td style="vertical-align: middle;">
                                    <?= esc($asistencia['Telefono'] ?? 'N/A') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center" style="padding: 40px; color: #777;">
                                <span class="glyphicon glyphicon-info-sign" style="font-size: 24px; color: #ccc; display: block; margin-bottom: 10px;"></span>
                                No hay registros de asistencia para la fecha seleccionada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    // Función que se ejecuta cuando el usuario selecciona un día en el calendario
    function cambiarFecha(nuevaFecha) {
        if(nuevaFecha) {
            // Recarga la página enviando la variable por GET
            window.location.href = "<?= base_url('/verIngresos') ?>?fecha=" + nuevaFecha;
        }
    }
</script>