<style>
    .responsiva-wrapper {
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 30px;
        max-width: 900px;
        margin: 0 auto 30px auto;
        font-family: 'Poppins', sans-serif;
        border-top: 4px solid #4a90e2;
    }
    .texto-responsiva {
        height: 220px;
        overflow-y: auto;
        background: #f8f9fa;
        padding: 20px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        font-size: 14px;
        text-align: justify;
        margin-bottom: 25px;
        color: #555;
    }
    .signature-container {
        position: relative;
        border: 2px dashed #b0c4de;
        border-radius: 8px;
        background-color: #fcfcfc;
        margin-bottom: 15px;
    }
    canvas {
        width: 100%;
        height: 250px;
        border-radius: 8px;
        touch-action: none; /* Crucial para tablets: Evita el scroll al firmar */
    }
    .btn-limpiar {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
        background: rgba(255, 255, 255, 0.9);
    }
    .form-section-title {
        font-size: 1.2em;
        color: #4a90e2;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 20px;
        margin-top: 25px;
        font-weight: 600;
    }
</style>

<div class="container-fluid" style="padding-top: 20px;">
    <div class="responsiva-wrapper">
        <h2 class="text-center" style="color: #4a90e2; font-weight: 600; margin-top: 0;">VITAL GYM</h2>
        <h4 class="text-center mb-4">Carta de Liberación de Responsabilidades</h4>
        
        <div class="texto-responsiva">
            <p>Por medio de la presente, acepto voluntariamente participar en las actividades físicas, uso de aparatos, pesas y demás instalaciones que ofrece <strong>VitalGym</strong>.</p>
            <p><strong>1. Asunción de Riesgos:</strong> Reconozco y entiendo que el entrenamiento físico y el uso de las instalaciones conllevan riesgos inherentes de lesiones físicas. Asumo total responsabilidad por cualquier lesión, daño o pérdida que pueda sufrir durante mi estancia.</p>
            <p><strong>2. Estado de Salud:</strong> Declaro que me encuentro en buenas condiciones físicas y de salud para realizar ejercicio, y que no padezco ninguna enfermedad o condición que me impida realizar actividad física de manera segura.</p>
            <p><strong>3. Reglas del Gimnasio:</strong> Me comprometo a respetar el reglamento interno, a utilizar el equipo correctamente y a seguir las indicaciones del personal.</p>
            <p><strong>4. Liberación de Responsabilidad:</strong> Libero a <strong>VitalGym</strong>, a sus propietarios, instructores y empleados de cualquier reclamo o demanda legal derivada de accidentes, lesiones o pérdida de objetos personales dentro de las instalaciones.</p>
            <p>Al firmar este documento y completar mi registro, confirmo que he leído, entendido y aceptado los términos aquí expuestos.</p>
        </div>

        <form id="registroForm">
            <h4 class="form-section-title">Datos Personales</h4>
            
            <div class="row">
                <div class="col-md-4 form-group">
                    <label class="control-label fw-bold">Nombre(s) *</label>
                    <input type="text" class="form-control input-lg" id="nombre" required>
                </div>
                <div class="col-md-4 form-group">
                    <label class="control-label fw-bold">Apellido Paterno *</label>
                    <input type="text" class="form-control input-lg" id="apellido_p" required>
                </div>
                <div class="col-md-4 form-group">
                    <label class="control-label fw-bold">Apellido Materno </label>
                    <input type="text" class="form-control input-lg" id="apellido_m" >
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="control-label fw-bold">Teléfono (WhatsApp) *</label>
                    <input type="tel" class="form-control input-lg" id="telefono" required>
                </div>
                <div class="col-md-6 form-group">
                    <label class="control-label fw-bold">Correo Electrónico </label>
                    <input type="email" class="form-control input-lg" id="correo" >
                </div>
            </div>

            <h4 class="form-section-title" style="color: #e74c3c;">Contacto de Emergencia</h4>
            <div class="row">
                <div class="col-md-7 form-group">
                    <label class="control-label fw-bold">En caso de emergencia llamar a (Nombre / Parentesco): </label>
                    <input type="text" class="form-control input-lg" id="emergencia_nombre" placeholder="Ej. María López (Madre)" >
                </div>
                <div class="col-md-5 form-group">
                    <label class="control-label fw-bold">Teléfono de Emergencia: *</label>
                    <input type="tel" class="form-control input-lg" id="emergencia_telefono" placeholder="10 dígitos" required>
                </div>
            </div>

            <div class="row" style="margin-top: 15px;">
                <div class="col-md-12">
                    <div class="well" style="background-color: #f9f9f9; border: 1px solid #e3e3e3;">
                        <label class="control-label" style="font-size: 1.1em; margin-bottom: 10px; display:block;">
                            ¿Acepta recibir recordatorios de vencimiento de su membresía por WhatsApp?
                        </label>
                        <label class="radio-inline text-success" style="font-weight: bold; font-size: 1.1em;">
                            <input type="radio" name="recordatorio_wa" value="SI" checked> SÍ, acepto
                        </label>
                        <label class="radio-inline text-danger" style="font-weight: bold; font-size: 1.1em; margin-left: 20px;">
                            <input type="radio" name="recordatorio_wa" value="NO"> NO, gracias
                        </label>
                    </div>
                </div>
            </div>

            <h4 class="form-section-title">Firma de Aceptación</h4>
            <p class="text-muted small"><span class="glyphicon glyphicon-pencil"></span> Por favor, firme en el recuadro inferior usando su dedo o un stylus.</p>
            
            <div class="signature-container">
                <button type="button" class="btn btn-default btn-sm btn-limpiar" id="clearSignature">
                    <span class="glyphicon glyphicon-trash"></span> Limpiar
                </button>
                <canvas id="signaturePad"></canvas>
            </div>

            <div class="row" style="margin-top: 30px;">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-lg" style="padding: 15px 40px; font-size: 1.2em; background-color: #4a90e2; border-color: #4a90e2;">
                        Aceptar Términos y Registrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const canvas = document.getElementById('signaturePad');
        
        // Ajustar resolución del canvas para tablets
        function resizeCanvas() {
            const ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }
        
        window.onresize = resizeCanvas;
        resizeCanvas();

        const signaturePad = new SignaturePad(canvas, {
            penColor: "rgb(0, 0, 128)",
            backgroundColor: "rgba(255, 255, 255, 0)"
        });

        document.getElementById('clearSignature').addEventListener('click', function () {
            signaturePad.clear();
        });

        $('#registroForm').on('submit', function(e) {
            e.preventDefault();

            if (signaturePad.isEmpty()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Falta tu firma',
                    text: 'Por favor, firma en el recuadro antes de continuar.'
                });
                return;
            }

            // Unir el nombre y el teléfono de emergencia para mandarlo ordenado
            let contactoEmergencia = $('#emergencia_nombre').val() + ' - Tel: ' + $('#emergencia_telefono').val();

            const formData = {
                nombre: $('#nombre').val(),
                apellido_p: $('#apellido_p').val(),
                apellido_m: $('#apellido_m').val(),
                telefono: $('#telefono').val(),
                correo: $('#correo').val(),
               contacto_emergencia: $('#emergencia_nombre').val(),    // <- Solo el nombre
                telefono_emergencia: $('#emergencia_telefono').val(),  // <- Nuevo: Solo el teléfono
                recordatorio_wa: $('input[name="recordatorio_wa"]:checked').val(),
                firma_base64: signaturePad.toDataURL('image/png') 
            };

            // Bloquear botón para evitar múltiples envíos
            let btnSubmit = $(this).find('button[type="submit"]');
            btnSubmit.prop('disabled', true).text('Guardando...');

            $.ajax({
                url: '<?= base_url('/GuardarResponsiva') ?>',
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function(response) {
                    btnSubmit.prop('disabled', false).text('Aceptar Términos y Registrar');
                    
                    if(response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Registro Exitoso!',
                            text: 'Pasa a recepción para completar tu inscripción.',
                            confirmButtonText: 'Terminar',
                            timer: 4000
                        }).then(() => {
                            document.getElementById('registroForm').reset();
                            signaturePad.clear();
                        });
                    } else {
                        Swal.fire('Error', response.mensaje, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    btnSubmit.prop('disabled', false).text('Aceptar Términos y Registrar');
                    Swal.fire('Error', 'Hubo un problema de comunicación con el servidor.', 'error');
                    console.log(xhr.responseText);
                }
            });
        });
    });
</script>