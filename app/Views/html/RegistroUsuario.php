<style>
    /* Importamos la tipografía moderna 'Poppins' */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    /* Tarjeta principal: Diseño Widescreen */
    .card-registro { 
        font-family: 'Poppins', sans-serif;
        margin: 15px auto 30px; 
        background-color: #ffffff; 
        color: #333333; 
        padding: 25px 50px; 
        border-radius: 10px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        border-top: 4px solid #8e44ad; /* Cambié el color a morado para diferenciarlo de clientes */
        border-bottom: #8e44ad 4px solid; 
        width: 100%; 
        max-width: 800px; /* Reduje un poco el max-width porque ya no hay tantos campos */
    }
    
    .card-registro hr { border-color: #f0f0f0; margin-bottom: 20px; }
    
    /* Títulos de sección finos y limpios */
    .form-section-title { 
        font-size: 14px; 
        color: #8e44ad; 
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 2px solid #f4f6f9; 
        padding-bottom: 8px; 
        margin-bottom: 15px; 
        margin-top: 15px; 
        font-weight: 600;
        text-align: center;
    }

    /* Caja de la Huella Biométrica compacta y clara */
    .huella-box { 
        background-color: #f8f9fa; 
        border: 2px dashed #ced4da; 
        padding: 25px; 
        text-align: center; 
        border-radius: 8px; 
        margin-bottom: 20px; 
    }
    .icon-huella { font-size: 45px; color: #adb5bd; margin-bottom: 10px; }
    .text-success, .status-success .icon-huella { color: #28a745 !important; }
    
    /* BOTÓN PRINCIPAL */
    .btn-secondary-custom {
        font-family: 'Poppins', sans-serif;
        background-color: #8e44ad; 
        color: #ffffff;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
        padding: 12px; 
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    .btn-secondary-custom:hover:not([disabled]) {
        background-color: #732d91; 
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(142, 68, 173, 0.3);
    }
    .btn-secondary-custom[disabled] {
        background-color: #e9ecef;
        color: #adb5bd;
        cursor: not-allowed;
    }
</style>

<div class="row">
    <div class="col-md-12"> 
        <div class="card-registro">
            <h3 class="text-center" style="margin-top: 0; font-weight: bold; letter-spacing: 1px;">VINCULAR HUELLA DE PERSONAL</h3>
            <p class="text-center text-muted" style="font-size: 16px; margin-bottom: 20px;">
                Usuario actual: <strong style="color: #333;"><?= esc($username) ?></strong>
            </p>
            <hr>
            
            <form id="formRegistroStaff" action="<?= base_url('/guardarHuellaUsuario') ?>" method="post">
                <input type="hidden" class="txt_csrfname" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

                <h4 class="form-section-title">Datos Biométricos</h4>

                <div class="col-md-12 text-center" style="margin-bottom: 20px; margin-top: 20px;">
                    <button type="button" class="btn btn-primary btn-lg" id="btnEscanear" style="margin-bottom: 20px;">
                        <span class="glyphicon glyphicon-qrcode"></span> Iniciar Captura de Huella
                    </button>

                    <div id="zonaCaptura" style="display:none;"> 
                        <div id="circuloEstado" class="huella-circle status-waiting" style="width: 100px; height: 100px; border-radius: 50%; background: #eee; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 40px; color: #aaa; border: 4px solid #ddd;">
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
                    <span class="glyphicon glyphicon-floppy-disk"></span> Guardar Huella en mi Perfil
                </button>
            </form>
        </div>
    </div>
</div>



<script src="<?= base_url('assets/scripts/es6-shim.js')?>"></script> 
<script src="<?= base_url('assets/scripts/websdk.client.bundle.min.js')?>"></script> 
<script src="<?= base_url('assets/scripts/fingerprint.sdk.min.js')?>"></script> 

<script src="<?= base_url('assets/js/enrolar_Usuario.js') ?>"></script>

<script>
    // Script visual original
    function actualizarVisuales(estado) {
        var circulo = $("#circuloEstado");
        var icono = $("#iconoEstado"); 
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
            icono.removeClass("glyphicon-hand-up").addClass("glyphicon-ok text-success"); 
            btnGuardar.addClass("active").prop("disabled", false);
        }
    }
</script>

