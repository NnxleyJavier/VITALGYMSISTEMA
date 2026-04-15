
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .card-registro { 
        font-family: 'Poppins', sans-serif;
        margin: 15px auto 30px; 
        background-color: #ffffff; 
        color: #333333; 
        padding: 25px 50px; 
        border-radius: 10px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        border-top: 4px solid #4a90e2; /* Azul VitalGym */
        border-bottom: #4a90e2 4px solid; 
        width: 100%; 
        max-width: 800px;
    }
    
    .card-registro hr { border-color: #f0f0f0; margin-bottom: 20px; }
</style>

<div class="container-fluid">
    <div class="card-registro">
        <h3 class="text-center" style="color: #4a90e2; font-weight: 600;">Enrolamiento Biométrico</h3>
        <h4 class="text-center text-muted"><?= $cliente['Nombre'] . ' ' . $cliente['ApellidoP'] . ' ' . $cliente['ApellidoM'] ?></h4>
        <hr>
        
        <input type="hidden" id="idCliente" value="<?= $cliente['IDClientes'] ?>">

        <div class="row text-center" style="margin-bottom: 20px;">
            <div class="col-md-12">
                <div id="circuloEstado" class="status-circle status-waiting mx-auto" style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid #eee; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <span id="iconoEstado" class="glyphicon glyphicon-hand-up" style="font-size: 50px; color: #999;"></span>
                </div>
                <h4 id="textoEstado" style="margin-top: 15px; font-weight: 500;">Listo para capturar</h4>
                <p class="text-muted" id="instruccionEstado">Coloque el dedo del cliente en el lector 6 veces para generar un registro de alta calidad.</p>
                
                <div class="progress" style="height: 10px; margin-top: 15px; border-radius: 10px;">
                    <div id="barraProgreso" class="progress-bar progress-bar-striped active" role="progressbar" style="width: 0%; background-color: #4a90e2;"></div>
                </div>
                <p id="contadorMuestras" class="text-muted small">0 de 6 muestras capturadas</p>
            </div>
        </div>

        <div class="text-center" style="margin-bottom: 20px;">
            <button type="button" class="btn btn-primary btn-lg" id="btnEscanear" style="background-color: #4a90e2; border-color: #4a90e2; padding: 10px 30px;">
                <span class="glyphicon glyphicon-hand-up"></span> Iniciar Escaneo
            </button>
            <button type="button" class="btn btn-warning btn-lg" id="btnReiniciarHuella" style="display: none;">
                <span class="glyphicon glyphicon-refresh"></span> Reiniciar
            </button>
        </div>
        
     <form id="formRegistroCliente" action="<?= base_url('guardarHuellaCliente') ?>">
       <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="txt_csrfname">
    <input type="hidden" name="id_cliente" id="idCliente" value="<?= $cliente['IDClientes'] ?>">
    
    <input type="hidden" id="huella_1">
    <input type="hidden" id="huella_2">
    <input type="hidden" id="huella_3">
    <input type="hidden" id="huella_4">
    <input type="hidden" id="huella_5">
    <input type="hidden" id="huella_6">
    
    <button type="submit" class="btn btn-success btn-block btn-lg" id="btnGuardar" disabled>
        <span class="glyphicon glyphicon-floppy-disk"></span> Guardar Huella del Cliente
    </button>
        </form>

    </div>
</div>

<script src="<?= base_url('assets/scripts/es6-shim.js')?>"></script> 
<script src="<?= base_url('assets/scripts/websdk.client.bundle.min.js')?>"></script> 
<script src="<?= base_url('assets/scripts/fingerprint.sdk.min.js')?>"></script> 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/enrolar_Usuario.js') ?>"></script>