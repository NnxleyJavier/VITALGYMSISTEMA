<style>
    /* Importamos la tipografía moderna 'Poppins' */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    /* Tarjeta principal: Diseño Widescreen (Más ancha, menos alta) y color limpio */
    .card-registro { 
        font-family: 'Poppins', sans-serif;
        margin: 15px auto 30px; /* Reduje el margen superior para que suba un poco */
        background-color: #ffffff; /* Blanco limpio y súper agradable */
        color: #333333; /* Texto oscuro para lectura perfecta */
        padding: 25px 50px; /* Reduje la altura (25px) y mantuve lo ancho (50px) */
        border-radius: 10px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); /* Sombra muy suave y elegante */
        border-top: 4px solid #4a90e2; /* Azul relajante en la parte superior */
        border-bottom: #4a90e2 4px solid; /* Azul relajante en la parte inferior */
        width: 100%; /* Obliga a usar todo el espacio */
        max-width: 1200px; /* ¡Expansión horizontal máxima! */
    }
    
    .card-registro hr { border-color: #f0f0f0; margin-bottom: 20px; }
    
    /* Títulos de sección finos y limpios */
    .form-section-title { 
        font-size: 14px; 
        color: #4a90e2; 
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 2px solid #f4f6f9; 
        padding-bottom: 8px; 
        margin-bottom: 15px; 
        margin-top: 15px; 
        font-weight: 600;
    }

    /* Estilos de los inputs: Fondo gris súper claro para que destaquen sobre el blanco */
    .card-registro label { color: #666666; font-size: 13px; font-weight: 500; }
    .card-registro .form-control {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa; 
        border: 1px solid #dee2e6;
        color: #333333;
        border-radius: 6px; 
        padding: 8px 15px;
        height: auto; 
        box-shadow: none;
        transition: all 0.3s ease;
    }
    .card-registro .form-control:focus {
        border-color: #4a90e2; 
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
    }
    
    /* Campo de precio inalterable */
    .card-registro .input-group-addon {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #4a90e2;
        border-right: none;
        font-weight: bold;
    }
    .card-registro .form-control[readonly] {
        background-color: #e9ecef;
        color: #4a90e2;
        font-weight: 700;
        font-size: 16px;
    }

    /* Caja de la Huella Biométrica compacta y clara */
    .huella-box { 
        background-color: #f8f9fa; 
        border: 2px dashed #ced4da; 
        padding: 25px; /* Menos alta */
        text-align: center; 
        border-radius: 8px; 
        margin-bottom: 20px; 
    }
    .icon-huella { font-size: 45px; color: #adb5bd; margin-bottom: 10px; }
    .text-success, .status-success .icon-huella { color: #28a745 !important; }
    
    /* BOTÓN PRINCIPAL */
    .btn-secondary-custom {
        font-family: 'Poppins', sans-serif;
        background-color: #4a90e2; 
        color: #ffffff;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
        padding: 12px; /* Reduje un poco la altura del botón */
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    .btn-secondary-custom:hover:not([disabled]) {
        background-color: #357abd; 
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(74, 144, 226, 0.3);
    }
    .btn-secondary-custom[disabled] {
        background-color: #e9ecef;
        color: #adb5bd;
        cursor: not-allowed;
    }
</style>






<div class="row">
    <div class="col-md-12"> <div class="card-registro">
            <h3 class="text-center" style="margin-top: 0; font-weight: bold; letter-spacing: 1px;">NUEVO REGISTRO DE CLIENTE</h3>
            <hr>
            
            <form id="formRegistroUsuario" action="<?= base_url('/MandaraBDUsuario') ?>" method="post">
                <input type="hidden" class="txt_csrfname" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                
                <h4 class="form-section-title">Datos Personales</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group"><label>Nombre(s):</label><input type="text" class="form-control" name="Nombre" required></div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group"><label>Apellido Paterno:</label><input type="text" class="form-control" name="ApellidoP" required></div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group"><label>Apellido Materno:</label><input type="text" class="form-control" name="ApellidoM" ></div>
                    </div>
                </div>

                
                <div class="row">
                <div class="col-md-6">
                        <div class="form-group">
                            <label>Teléfono (WhatsApp):</label>
                            <input type="tel" class="form-control" name="Telefono" >
                            <div style="margin-top: 8px;">
                                <label style="font-size: 12px; font-weight: normal; color: #666; cursor: pointer;">
                                    <input type="checkbox" name="Acepta_WhatsApp" value="1" checked style="margin-right: 5px;"> 
                                    Acepta recibir recordatorios de vencimiento por WhatsApp
                                </label>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group"><label>Correo Electrónico:</label><input type="email" class="form-control" name="Correo"></div>
                    </div>
                </div>


                <h4 class="form-section-title">Servicio y Pago</h4>
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Membresía Principal a Contratar:</label>
                            <select class="form-control" name="Servicios_IDServicios" id="selectServicio" required>
                                <option value="" disabled selected>Seleccione una membresía...</option>
                                <?php if(isset($membresias)): ?>
                                    <?php foreach($membresias as $servicio): ?>
                                        <option value="<?= $servicio['IDServicios'] ?>" data-costo="<?= $servicio['Costo'] ?>">
                                            <?= $servicio['NombreMembresia'] ?> - $<?= $servicio['Costo'] ?>
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
                                <option value="TarjetaCredito">Tarjeta de Crédito</option>
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
                        <button type="button" class="btn btn-info btn-sm" id="btnAgregarServicio" style="margin-top: 10px; background-color: #17a2b8; border:none;">
                            <span class="glyphicon glyphicon-plus"></span> Agregar servicio extra (Locker, Regadera...)
                        </button>
                    </div>
                </div>

            


                <h4 class="form-section-title">Datos Biométricos</h4>

              
<div class="col-md-12 text-center" style="margin-bottom: 20px; margin-top: 20px;">
    <button type="button" class="btn btn-primary btn-lg" id="btnEscanear" style="margin-bottom: 20px;">
        <span class="glyphicon glyphicon-qrcode"></span> Iniciar Captura de Huella
    </button>

    <div id="zonaCaptura" style="display:none;"> <div id="circuloEstado" class="huella-circle status-waiting" style="width: 100px; height: 100px; border-radius: 50%; background: #eee; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 40px; color: #aaa; border: 4px solid #ddd;">
            <span id="iconoEstado" class="glyphicon glyphicon-hand-up" aria-hidden="true"></span>
        </div>
        
        <h4 id="mensajeHuella" style="margin-top: 15px; font-weight: bold; color: #555;">
            Coloque el dedo en el lector (0/6)
        </h4>

        <div class="progress" style="height: 15px; max-width: 300px; margin: 10px auto; background-color: #e9ecef; border-radius: 10px;">
            <div id="barraProgreso" class="progress-bar progress-bar-striped active" role="progressbar" style="width: 0%; background-color: #28a745;"></div>
        </div>

        <button type="button" class="btn btn-warning btn-sm" id="btnReiniciarHuella" style="display:none; margin-top:10px;">
            <span class="glyphicon glyphicon-refresh"></span> Reiniciar Captura
        </button>
    </div>

<input type="hidden" name="huella_1" id="huella_1">
    <input type="hidden" name="huella_2" id="huella_2">
    <input type="hidden" name="huella_3" id="huella_3">
    <input type="hidden" name="huella_4" id="huella_4">
    <input type="hidden" name="huella_5" id="huella_5"> 
    <input type="hidden" name="huella_6" id="huella_6">
</div>





<button type="submit" class="btn btn-secondary-custom btn-block btn-lg" id="btnGuardar" disabled>
                    <span class="glyphicon glyphicon-floppy-disk"></span> Guardar Cliente y Registrar Pago
                </button>
            
            </form> </div> </div> </div> 
            <div id="templateServicio" style="display: none;">
    <div class="row fila-servicio-extra" style="margin-top: 10px;">
        <div class="col-md-5">
           <select class="form-control select-extra" name="servicios_extra[]" required disabled>
                <option value="" disabled selected>Seleccione servicio extra...</option>
                <?php if(isset($extras)): ?>
                    <?php foreach($extras as $extra): ?>
                        <option value="<?= $extra['IDServicios'] ?>" data-costo="<?= $extra['Costo'] ?>">
                            <?= $extra['NombreMembresia'] ?> - $<?= $extra['Costo'] ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm btn-eliminar-extra" title="Quitar servicio">
                <span class="glyphicon glyphicon-trash"></span>
            </button>
        </div>
    </div>
</div>
                                    






<script src="<?= base_url('assets/scripts/es6-shim.js')?>"></script> 
<script src="<?= base_url('assets/scripts/websdk.client.bundle.min.js')?>"></script> 
<script src="<?= base_url('assets/scripts/fingerprint.sdk.min.js')?>"></script> 
<script src="<?= base_url('assets/js/registro-huellav2.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('#selectServicio').change(function() {
            var costoServicio = $(this).find(':selected').data('costo');
            $('#inputMonto').val(costoServicio ? costoServicio : '');
        });
    });
</script>

<script>
    // Script visual original que me pasaste
    function actualizarVisuales(estado) {
        var circulo = $("#circuloEstado");
        var icono = $("#iconoEstado"); // Corregido: En tu HTML se llama iconoEstado, no iconoHuella
        var btnGuardar = $("#btnGuardar");

        circulo.removeClass("status-waiting status-scanning status-success");

        if(estado === "esperando") {
            circulo.addClass("status-waiting");
            icono.removeClass("glyphicon-ok").addClass("glyphicon-hand-up");
        } 
        else if(estado === "escaneando") {
            circulo.addClass("status-scanning");
        }
        else if(estado === "exito") {
            circulo.addClass("status-success");
            icono.removeClass("glyphicon-hand-up").addClass("glyphicon-ok text-success"); // Agregué text-success para que se pinte verde
            btnGuardar.addClass("active").prop("disabled", false);
        }
    }
</script>
