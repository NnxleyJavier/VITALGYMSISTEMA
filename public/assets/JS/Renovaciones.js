$(document).ready(function() {
    
    // ========================================================
    // 1. LÓGICA PARA CALCULAR EL TOTAL EN VIVO
    // ========================================================
    function calcularTotal() {
        let total = 0;
        
        // A. Obtener costo de la membresía principal
        let costoMembresia = $('#selectServicio').find(':selected').data('costo');
        if (costoMembresia) {
            total += parseFloat(costoMembresia);
        }
        
        // B. Sumar el costo de los checkboxes seleccionados (Extras)
        $('.extra-checkbox:checked').each(function() {
            let costoExtra = $(this).data('costo');
            if (costoExtra) {
                total += parseFloat(costoExtra);
            }
        });
        
        // C. Actualizar los números en la pantalla y en el input oculto
        $('#lblTotal').text(total.toFixed(2));
        $('#inputMontoTotal').val(total.toFixed(2));
    }

    // Escuchar cambios: Si el staff cambia la membresía o marca un extra, recalcula
    $('#selectServicio, .extra-checkbox').change(function() {
        calcularTotal();
    });


    // ========================================================
    // 2. LÓGICA PARA ENVIAR EL FORMULARIO (AJAX)
    // ========================================================
    $('#formRenovacion').on('submit', function(e) {
        e.preventDefault(); // Evita el pantallazo blanco de recarga

        // Validamos que no intenten cobrar sin elegir paquete
        if ($('#selectServicio').val() === "") {
            alert("Por favor, selecciona el tipo de membresía.");
            return;
        }

        let form = $(this); // Guardamos el formulario en una variable
        
        // Leemos las rutas de CodeIgniter que inyectamos en el HTML (Vital)
        let urlGuardar = form.data('url-guardar');
        let urlPanel   = form.data('url-panel');

        // Congelamos el botón para evitar que el staff cobre 2 veces por error
        let btnSubmit = form.find('button[type="submit"]');
        let textoOriginal = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<span class="glyphicon glyphicon-hourglass"></span> Procesando pago...');

        // Recolectamos todos los datos (Monto, ID Cliente, Extras, etc.)
        let formData = form.serialize();

        // Enviamos la petición silenciosa a PHP
        $.ajax({
            url: urlGuardar, 
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(respuesta) {
                
                // Refrescamos el candado de seguridad (CSRF) de CodeIgniter
                $('#csrf_token').val(respuesta.token);

                if (respuesta.status === 'success') {
                    // Si todo salió bien en la base de datos:
                    alert(respuesta.mensaje);
                    window.location.href = urlPanel; // Mandamos al panel principal
                } else {
                    // Si hubo un problema (ej. base de datos caída):
                    alert(respuesta.mensaje);
                    btnSubmit.prop('disabled', false).html(textoOriginal); // Soltamos el botón
                }
            },
            error: function() {
                alert("Error de comunicación con el servidor. Revisa la conexión de red.");
                btnSubmit.prop('disabled', false).html(textoOriginal);
            }
        });
    });

});