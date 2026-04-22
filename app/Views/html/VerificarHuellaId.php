<div class="form-group text-center" style="margin-top: 40px;">
    <label style="color: #666; font-weight: 500;">¿Problemas con el lector biométrico?</label>
    <div class="input-group" style="max-width: 300px; margin: 10px auto;">
        <input type="number" id="inputNumeroSocio" class="form-control" placeholder="Ingresa tu ID de socio..." onkeypress="if(event.keyCode==13) procesarAccesoManual();">
        <div class="input-group-btn">
            <button class="btn btn-primary" onclick="procesarAccesoManual()" style="background-color: #4a90e2; border: none;">Entrar</button>
        </div>
    </div>
</div>

<script>
function procesarAccesoManual() {
    let numeroSocio = $('#inputNumeroSocio').val();
    
    if(!numeroSocio) {
        alert("Por favor, ingresa tu número de socio.");
        return;
    }

    $.ajax({
        url: '<?= base_url("/verificarPorID") ?>',
        type: 'POST',
        data: {
            numero_socio: numeroSocio,
            id_gimnasio: 1, // IMPORTANTE: Cambia este 1 por el ID de la sucursal donde esté instalada la tablet
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if(response.status === 'success') {
                // Aquí puedes detonar la misma pantalla verde / abrir torniquete que usas con la huella
                alert("✅ Bienvenido, " + response.nombre);
                $('#inputNumeroSocio').val(''); // Limpiamos el input para el siguiente cliente
            } else {
                // Aquí detonas la pantalla roja de error
                alert("❌ " + response.message);
            }
        },
        error: function() {
            alert("Error de conexión con el servidor.");
        }
    });
}
</script>