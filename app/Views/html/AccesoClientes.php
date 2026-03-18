<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    .kiosco-wrapper {
        display: flex; justify-content: center; align-items: center;
        min-height: 75vh; width: 100%; font-family: 'Poppins', sans-serif;
    }
    .card-acceso {
        background-color: #ffffff; width: 100%; max-width: 550px; padding: 40px;
        border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        border-top: 6px solid #4a90e2; text-align: center; position: relative;
    }
    .titulo-kiosco { font-weight: 800; color: #333; font-size: 28px; margin: 0 0 5px 0; text-transform: uppercase; }
    .subtitulo { color: #4a90e2; font-weight: 600; font-size: 13px; margin-bottom: 30px; display: block; letter-spacing: 2px; text-transform: uppercase; }
    
    /* Botón Lector */
    .scanner-trigger {
        width: 160px; height: 160px; background-color: #f8f9fa; border: 4px dashed #dee2e6;
        border-radius: 50%; margin: 0 auto 30px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.3s ease;
    }
    .scanner-trigger:hover { border-color: #4a90e2; background-color: #eaf4ff; transform: scale(1.05); }
    .scanner-trigger.scanning { border-color: #2ecc71; animation: pulse 1.5s infinite; }
    .icon-grande { font-size: 70px; color: #adb5bd; transition: color 0.3s; }
    .scanner-trigger:hover .icon-grande { color: #4a90e2; }
    .scanner-trigger.scanning .icon-grande { color: #2ecc71; }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
        70% { box-shadow: 0 0 0 20px rgba(46, 204, 113, 0); }
        100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
    }

    /* Modal */
    .modal-content-custom { border-radius: 15px; border: none; overflow: hidden; text-align: center; font-family: 'Poppins', sans-serif;}
    .modal-header-custom { padding: 30px 20px 10px; border: none; }
    .modal-body-custom { padding: 10px 30px 40px; }
    .estado-ok { background-color: #e8f5e9; color: #2e7d32; }
    .estado-error { background-color: #ffebee; color: #c62828; }
    .dias-restantes-num { font-size: 80px; font-weight: 800; line-height: 1; margin: 10px 0; color: #4a90e2; }
    .foto-avatar { font-size: 40px; background: #fff; width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 15px; }
</style>

<div class="kiosco-wrapper">
    <div class="card-acceso">
        <h2 class="titulo-kiosco">PUNTO DE ACCESO</h2>
        <span class="subtitulo">Escanea tu huella para ingresar</span>
        <div class="scanner-trigger" id="btnActivarLector" title="Clic para activar lector">
            <span class="glyphicon glyphicon-print icon-grande"></span>
        </div>
        <h4 id="estadoTexto" style="font-weight: 600; color: #666; margin-top: 20px;">Toca la huella para identificarte</h4>
        <p class="text-muted" style="font-size: 12px; margin-top: 10px;">Sistema Biométrico v2.0</p>
    </div>
</div>

<div class="modal fade" id="modalRespuesta" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-sm" role="document" style="margin-top: 15vh;">
        <div class="modal-content modal-content-custom">
            <div id="modalHeader" class="modal-header-custom">
                <div class="foto-avatar"><span id="iconoResultado" class="glyphicon glyphicon-user"></span></div>
                <h3 id="modalTitulo" style="font-weight: 800; margin: 0;">Hola!</h3>
            </div>
            <div class="modal-body-custom">
                <h4 id="nombreCliente" style="margin-top: 5px; font-weight: 600;">Usuario</h4>
                <p id="nombreMembresia" style="color: #777;">Verificando...</p>
                <hr>
                <div id="infoDias">
                    <small style="text-transform: uppercase; letter-spacing: 1px; color: #888;">Te quedan</small>
                    <div class="dias-restantes-num" id="numDias">0</div>
                    <small style="text-transform: uppercase; letter-spacing: 1px; color: #888;">Días</small>
                </div>
                <div id="infoError" style="display: none;">
                    <p id="mensajeError" style="font-weight: bold; font-size: 18px; color: #c62828; margin-top: 20px;">Acceso Denegado</p>
                </div>
                <br>
                <button type="button" class="btn btn-default btn-block" data-dismiss="modal" style="border-radius: 20px;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/scripts/es6-shim.js')?>"></script> 
<script src="<?= base_url('assets/scripts/websdk.client.bundle.min.js')?>"></script> 
<script src="<?= base_url('assets/scripts/fingerprint.sdk.min.js')?>"></script> 

<script>
    var AppConfig = {
        baseURL: "<?= base_url() ?>",
        csrfTokenName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>"
    };
</script>

<script src="<?= base_url('assets/js/kiosco.js') ?>"></script>