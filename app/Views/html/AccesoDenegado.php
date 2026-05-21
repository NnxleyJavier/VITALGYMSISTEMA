<div style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px);">
    <div style="max-width: 500px; width: 100%; padding: 15px;">
        <div class="card shadow" style="border: none; border-radius: 20px; background-color: #fff; text-align: center; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            
            <div style="margin-bottom: 25px;">
                <span class="glyphicon glyphicon-remove-sign" style="font-size: 7rem; color: #dc3545;"></span>
            </div>
            
            <h1 style="font-weight: bold; color: #343a40; margin-bottom: 15px; font-size: 2.5rem;">Acceso Denegado</h1>
            
            <p style="color: #6c757d; font-size: 1.1rem; margin-bottom: 0;">
                Lo sentimos, no tienes los permisos necesarios para acceder a esta área.
            </p>
            
            <hr style="margin: 30px 0; border-top: 1px solid #e9ecef;">
            
            <div>
                <p style="font-size: 1.05rem; margin-bottom: 25px; color: #495057;">
                    Serás redirigido a la página principal en <strong id="countdown" style="color: #dc3545; font-size: 1.3rem;">5</strong> segundos...
                </p>
                <a href="<?= base_url('/recepcion') ?>" class="btn btn-danger btn-lg" style="border-radius: 50px; padding: 12px 35px; font-weight: bold; background-color: #dc3545; border-color: #dc3545;">
                    <span class="glyphicon glyphicon-arrow-left" style="margin-right: 8px;"></span> Volver al inicio
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para la redirección automática después de 5 segundos
    let timeLeft = 5;
    const countdownElement = document.getElementById('countdown');
    
    const timer = setInterval(() => {
        timeLeft--;
        countdownElement.textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            // Redirigimos al inicio
            window.location.href = "<?= base_url('/recepcion') ?>";
        }
    }, 1000);
</script>
