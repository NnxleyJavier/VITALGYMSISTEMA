<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .card-perfil { 
        font-family: 'Poppins', sans-serif;
        margin: 40px auto; 
        background-color: #ffffff; 
        color: #333333; 
        padding: 40px 50px; 
        border-radius: 12px; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #4e54c8; 
        width: 100%; 
        max-width: 600px; 
    }
    .perfil-title { 
        color: #4e54c8; 
        font-weight: 700; 
        margin-bottom: 25px; 
        border-bottom: 1px solid #eee; 
        padding-bottom: 15px; 
        font-size: 20px;
    }
    .form-group label { font-weight: 500; color: #555; }
    .input-group-addon { background-color: #f8f9fa; border-right: none; }
    .form-control { border-left: none; box-shadow: none !important; border-color: #ccc; }
    .form-control:focus { border-color: #4e54c8; }
</style>

<div class="container-fluid" style="padding-top: 20px; min-height: 80vh;">
    <div class="card-perfil">
        <h3 class="perfil-title"><i class="fas fa-lock"></i> Actualizar mi Contraseña</h3>
        
        <form id="formPassword">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrf_token">

            <div class="form-group mb-4">
                <label>Contraseña Actual</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fas fa-key text-muted"></i></span>
                    <input type="password" name="pass_actual" id="pass_actual" class="form-control" placeholder="Ingresa tu contraseña actual" required>
                </div>
            </div>

            <div class="form-group mb-4">
                <label>Nueva Contraseña</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fas fa-shield-alt text-primary"></i></span>
                    <input type="password" name="pass_nueva" id="pass_nueva" class="form-control" placeholder="Mínimo 8 caracteres" required>
                </div>
            </div>

            <div class="form-group mb-4">
                <label>Confirmar Nueva Contraseña</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fas fa-check-double text-success"></i></span>
                    <input type="password" name="pass_confirm" id="pass_confirm" class="form-control" placeholder="Vuelve a escribir la nueva contraseña" required>
                </div>
            </div>

            <div style="margin-top: 35px;">
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="background-color: #4e54c8; border: none; border-radius: 8px;">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#formPassword').on('submit', function(e) {
        e.preventDefault();

        let btnSubmit = $(this).find('button[type="submit"]');
        let textoOriginal = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: '<?= base_url("/mi-perfil/actualizar-password") ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                
                $('#csrf_token').val(response.token); // Refrescar el candado de seguridad

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizada!',
                        text: response.mensaje,
                        confirmButtonColor: '#4e54c8'
                    }).then(() => {
                        // Limpiar el formulario por seguridad
                        $('#formPassword')[0].reset();
                        btnSubmit.prop('disabled', false).html(textoOriginal);
                    });
                } else {
                    Swal.fire('Error', response.mensaje, 'error');
                    btnSubmit.prop('disabled', false).html(textoOriginal);
                }
            },
            error: function() {
                Swal.fire('Error', 'Hubo un problema de comunicación con el servidor.', 'error');
                btnSubmit.prop('disabled', false).html(textoOriginal);
            }
        });
    });
});
</script>