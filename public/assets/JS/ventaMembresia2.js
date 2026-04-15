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
            // Usamos la constante BASE_URL que definiremos en la vista
            url: BASE_URL + 'guardarPagoEInscripcion', 
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                btnSubmit.prop('disabled', false).html(btnOriginalText);

                if(response.status === 'success') {
                    // Cerrar modal y recargar tabla
                    $('#modalProcesar').modal('hide');
                    if (typeof tabla !== 'undefined') {
                        tabla.ajax.reload();
                    }
                    
                   // Lógica de impresión silenciosa (Heredada de tu código original)
                    if (response.valoresdata) {
                        var queryString = $.param(response.valoresdata);
                        let urlTicket = BASE_URL + '/vendor/ticket14.php?' + queryString; 
                        
                        var nuevaVentana = window.open(urlTicket, '_blank');
                        setTimeout(function() {
                            if (nuevaVentana) {
                                nuevaVentana.close();
                            }
                        }, 5000);
                    }

                    // --- NUEVA LÓGICA DE WHATSAPP ---
                    // Si el servidor nos devolvió una URL de WhatsApp, la abrimos en otra pestaña
                    if (response.url_whatsapp) {
                        window.open(response.url_whatsapp, '_blank');
                    }
                    
                    // Preguntar por el enrolamiento biométrico
                    Swal.fire({
                        icon: 'success',
                        title: 'Inscripción Exitosa',
                        text: 'El pago se procesó y el ticket se envió a impresión. ¿Deseas proceder al enrolamiento de huella ahora?',
                        showCancelButton: true,
                        confirmButtonText: '<i class="glyphicon glyphicon-hand-up"></i> Enrolar Huella',
                        cancelButtonText: 'Más tarde',
                        confirmButtonColor: '#4a90e2'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = BASE_URL + '/enrolar/' + $('#idClienteProcesar').val();
                        }
                    });
                } else {
                    Swal.fire('Error', response.mensaje || 'Hubo un problema con el registro', 'error');
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