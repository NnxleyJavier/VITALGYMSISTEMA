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
                    
                    // Mostramos SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: '¡Operación Exitosa!',
                        text: respuesta.mensaje,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // ==========================================
                    // NUEVO: ABRIR WHATSAPP SI EXISTE LA RUTA
                    // ==========================================
                    if (respuesta.url_whatsapp) {
                        window.open(respuesta.url_whatsapp, '_blank');
                    }
                    // ==========================================

                    // --- INICIO LÓGICA DE IMPRESIÓN ---
                    if (respuesta.valoresdata) {
                        var queryString = $.param(respuesta.valoresdata);
                        let urlImpresion = "http://localhost/sistema/vendor/ticket15.php?" + queryString;

                        let iframe = document.createElement('iframe');
                        iframe.style.display = "none";
                        iframe.src = urlImpresion;
                        document.body.appendChild(iframe);

                        // Esperamos 3 segundos para que se mande a imprimir, y luego REGRESAMOS
                        setTimeout(function() {
                            window.history.back();
                        }, 3000);
                    } else {
                        setTimeout(function() {
                            window.history.back(); 
                        }, 1500);
                    }
                    // --- FIN LÓGICA DE IMPRESIÓN ---

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: respuesta.mensaje
                    });
                    btnSubmit.prop('disabled', false).html(textoOriginal); 
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Red',
                    text: 'Error de comunicación con el servidor. Revisa la conexión.'
                });
                btnSubmit.prop('disabled', false).html(textoOriginal);
            }
        });
    });

});