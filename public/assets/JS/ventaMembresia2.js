// Variables globales
let contadorExtras = 0;

$(document).ready(function() {
    
    // --- LÓGICA DE COSTOS Y EXTRAS ---

    // Al seleccionar la membresía principal
    $('#selectServicio').change(function() {
        calcularTotal();
    });

// Botón para agregar un servicio extra desde la Base de Datos
    $('#btnAgregarServicio').click(function() {
        contadorExtras++;
        
        // Construimos las opciones del Select leyendo la constante global
        let opcionesHTML = '<option value="" disabled selected>Seleccione un extra...</option>';
        
        if (typeof EXTRAS_DISPONIBLES !== 'undefined' && EXTRAS_DISPONIBLES.length > 0) {
            EXTRAS_DISPONIBLES.forEach(function(extra) {
                // Usamos NombreMembresia o Nombre_Servicio dependiendo de cómo se llame tu columna
                let nombreExtra = extra.NombreMembresia || extra.Nombre_Servicio; 
                let costoExtra = extra.Costo || 0;
                let idExtra = extra.IDServicios;
                
                opcionesHTML += `<option value="${idExtra}" data-costo="${costoExtra}">${nombreExtra} - $${costoExtra}</option>`;
            });
        } else {
            opcionesHTML += '<option value="" disabled>No hay extras registrados</option>';
        }

        let htmlExtra = `
            <div class="row" style="margin-top: 10px; padding: 10px; background-color: #f9f9f9; border-radius: 5px;" id="extra_${contadorExtras}">
                <div class="col-md-5">
                    <select class="form-control input-sm select-extra" name="ExtraID[]" required onchange="actualizarCostoExtra(this)">
                        ${opcionesHTML}
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon">$</span>
                        <input type="number" class="form-control input-sm costo-extra" name="ExtraCosto[]" placeholder="0.00" step="0.01" readonly required>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarExtra(${contadorExtras})">
                        <span class="glyphicon glyphicon-trash"></span> Quitar
                    </button>
                </div>
            </div>
        `;
        $('#contenedorServiciosExtra').append(htmlExtra);
    });




    // --- ENVÍO DEL FORMULARIO Y TICKET ---
 $('#formProcesarPago').submit(function(e) {
    e.preventDefault();
    
    // Deshabilitar botón para evitar dobles cobros accidentales
    let btnSubmit = $(this).find('button[type="submit"]');
    let btnOriginalText = btnSubmit.html();
    btnSubmit.prop('disabled', true).html('<span class="glyphicon glyphicon-refresh fast-spin"></span> Procesando...');

    let formData = $(this).serialize();

    $.ajax({
        url: BASE_URL + 'guardarPagoEInscripcion', 
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            btnSubmit.prop('disabled', false).html(btnOriginalText);

            if(response.status === 'success') {
                
                // 1. Cerrar modal y recargar tabla de fondo
                $('#modalProcesar').modal('hide');
                if (typeof tabla !== 'undefined') {
                    tabla.ajax.reload();
                }
                
                // 2. Armar el diseño de los botones para el SweetAlert
                let htmlBotones = '<div style="display: flex; flex-direction: column; gap: 12px; margin-top: 20px; margin-bottom: 10px;">';
                
                // Botón para Ticket Físico (Localhost)
                if (response.valoresdata) {
                    let queryString = $.param(response.valoresdata);
                    let urlTicket = 'http://localhost/sistema/vendor/ticket14.php?' + queryString; 
                    
                    htmlBotones += `<a href="${urlTicket}" target="_blank" class="btn btn-info btn-lg" style="border-radius: 8px; font-weight: bold; color: white; padding: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <i class="fas fa-print fa-lg"></i> Imprimir Ticket
                    </a>`;
                }

                // Botón para WhatsApp
                if (response.url_whatsapp) {
                    htmlBotones += `<a href="${response.url_whatsapp}" target="_blank" class="btn btn-success btn-lg" style="border-radius: 8px; font-weight: bold; color: white; padding: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <i class="fab fa-whatsapp fa-lg"></i> Enviar Recibo por WhatsApp
                    </a>`;
                }
                
                htmlBotones += '</div>';

                // 3. Mostrar la alerta TODO EN UNO (Ticket, WA y Huella)
                Swal.fire({
                    icon: 'success',
                    title: '¡Inscripción Exitosa!',
                    html: 'El pago se procesó correctamente. ¿Qué deseas hacer ahora?' + htmlBotones,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-fingerprint"></i> Enrolar Huella',
                    cancelButtonText: 'Terminar (Más tarde)',
                    confirmButtonColor: '#4a90e2',
                    cancelButtonColor: '#7e8299',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Si hace clic en "Enrolar Huella", lo mandamos a la ruta de biometría
                        window.location.href = BASE_URL + 'enrolar/' + $('#idClienteProcesar').val();
                    }
                });

            } else {
                Swal.fire('Error', response.message || response.mensaje || 'Hubo un problema con el registro', 'error');
            }
        },
        error: function(xhr) {
            btnSubmit.prop('disabled', false).html(btnOriginalText);
            Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
            console.log(xhr.responseText);
        }
    });
});


});

// --- NUEVA FUNCIÓN PARA AUTOMATIZAR EL PRECIO DEL EXTRA ---
// Debes colocar esta función suelta en el archivo, fuera del document.ready
function actualizarCostoExtra(elementoSelect) {
    // Buscar el precio en el atributo data-costo de la opción seleccionada
    let costoSeleccionado = $(elementoSelect).find(':selected').data('costo');
    
    // Buscar el input de costo que está en la misma fila (row) y asignarle el valor
    $(elementoSelect).closest('.row').find('.costo-extra').val(costoSeleccionado);
    
    // Recalcular el total a pagar
    calcularTotal();
}
// --- FUNCIONES AUXILIARES ---

function eliminarExtra(id) {
    $('#extra_' + id).remove();
    calcularTotal();
}

function calcularTotal() {
    let costoBase = parseFloat($('#selectServicio').find(':selected').data('costo')) || 0;
    let costoExtras = 0;
    
    $('.costo-extra').each(function() {
        costoExtras += parseFloat($(this).val()) || 0;
    });
    
    $('#inputMonto').val((costoBase + costoExtras).toFixed(2));
}

function abrirModalProcesar(idCliente, nombreCliente) {
    $('#idClienteProcesar').val(idCliente);
    $('#nombreClienteModal').text(nombreCliente);
    
    // Resetear todo al abrir un cliente nuevo
    $('#selectServicio').val('');
    $('#inputMonto').val('');
    $('#contenedorServiciosExtra').empty();
    contadorExtras = 0;
    
    $('#modalProcesar').modal('show');
}