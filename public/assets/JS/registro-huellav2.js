/**
 * Lógica: Captura IMAGEN RAW -> Envío a PHP -> Python extrae FMD -> BD
 */

// VARIABLES GLOBALES
var conteoMuestras = 0;
var readerID = ""; 
var sdk = null; 

$(document).ready(function() {
    // 1. Inicializar SDK al cargar la página
    inicializarLector();

    // Listener para el cambio de membresía
    $('#selectServicio').on('change', manejarCambioDeMembresia);

    // 2. INICIALIZAR CALCULADORA DE SERVICIOS
    inicializarServiciosDinamicos();

    // BOTÓN INICIAR ESCANEO
    $("#btnEscanear").click(function() {
        $("#zonaCaptura").show(); 
        $(this).hide(); 
        comenzarCaptura();
    });

    // BOTÓN REINICIAR 
    $("#btnReiniciarHuella").click(function() {
        reiniciarProceso();
    });

    // ENVÍO DEL FORMULARIO
    $("#formRegistroUsuario").submit(function(e) {
        e.preventDefault();

        var seleccion = $('#selectServicio').find(':selected').text();
        var esPaseDiario = seleccion.toLowerCase().includes('1 día');

        // Si NO es pase diario, se exige la huella.
        if (!esPaseDiario && conteoMuestras < 6) {
            alert("⚠️ Debe capturar la huella 6 veces antes de guardar.");
            return;
        }

        var datosFormulario = $(this);
        var csrfName = $('.txt_csrfname').attr('name'); 
        var csrfHash = $('.txt_csrfname').val(); 

        ajaxGuardarUsuario(datosFormulario, "MandaraBDUsuario", csrfName, csrfHash);
    });

    // Ejecutar la lógica de membresía al cargar la página por si hay algo pre-seleccionado
    manejarCambioDeMembresia();
});

/**
 * Función que se activa cada vez que el usuario cambia la membresía.
 * Decide si mostrar u ocultar la sección de huella y ajustar campos requeridos.
 */
function manejarCambioDeMembresia() {
    var seleccion = $('#selectServicio').find(':selected').text();
    var esPaseDiario = seleccion.toLowerCase().includes('1 día');

    var tituloBiometria = $('h4.form-section-title:contains("Datos Biométricos")');
    var seccionCaptura = tituloBiometria.next('div.col-md-12.text-center');
    var btnGuardar = $('#btnGuardar');
    var inputApellidoP = $('input[name="ApellidoP"]');

    if (esPaseDiario) {
        tituloBiometria.hide();
        seccionCaptura.hide();
        btnGuardar.prop('disabled', false).addClass('btn-success').removeClass('btn-secondary-custom');
        inputApellidoP.prop('required', false);
    } else {
        tituloBiometria.show();
        seccionCaptura.show();
        inputApellidoP.prop('required', true);
        if (conteoMuestras < 6) {
            btnGuardar.prop('disabled', true).removeClass('btn-success').addClass('btn-secondary-custom');
        }
    }
}

/* ==========================================================
   --- LÓGICA DE SERVICIOS EXTRA Y CÁLCULO DE PAGOS ---
========================================================== */

function inicializarServiciosDinamicos() {
    // Función maestra que suma todo el dinero
    function recalcularTotalPago() {
        var total = 0;

        // 1. Sumar el costo de la membresía principal
        var costoPrincipal = parseFloat($('#selectServicio').find(':selected').data('costo')) || 0;
        total += costoPrincipal;

        // 2. Sumar el costo de todos los extras que existan en el DOM
        $('.select-extra').each(function() {
            var costoExtra = parseFloat($(this).find(':selected').data('costo')) || 0;
            total += costoExtra;
        });

        // 3. Imprimir el total
        if (total > 0) {
            $('#inputMonto').val(total.toFixed(2));
        } else {
            $('#inputMonto').val('');
        }
    }

    // Si cambian la membresía principal, recalcular
    $('#selectServicio').change(recalcularTotalPago);

    // Si dan clic en "Agregar servicio extra"
    $('#btnAgregarServicio').click(function() {
   // 1. Tomamos el HTML del molde
        var $nuevaFila = $($('#templateServicio').html());
        
        // 2. FORZAMOS a quitar el disabled para que se envíe a PHP
        $nuevaFila.find('select').removeAttr('disabled');
        
        // 3. Lo pegamos en el contenedor visible
        $('#contenedorServiciosExtra').append($nuevaFila);
    });

    // Si cambian el valor de un servicio extra, recalcular
    $('#contenedorServiciosExtra').on('change', '.select-extra', function() {
        recalcularTotalPago();
    });

    // Si le dan clic al botón rojo de basura (eliminar fila)
    $('#contenedorServiciosExtra').on('click', '.btn-eliminar-extra', function() {
        $(this).closest('.fila-servicio-extra').remove();
        recalcularTotalPago(); // Restamos ese dinero del total al eliminar
    });
}

/* --- FUNCIONES DEL SDK --- */

function inicializarLector() {
    console.log("--> Iniciando SDK WebApi...");
    sdk = new Fingerprint.WebApi;
    
    sdk.onDeviceConnected = function(e) { buscarLectores(); };
    sdk.onDeviceDisconnected = function(e) { mostrarEstado("Lector desconectado", "error"); };
    sdk.onSamplesAcquired = onSamplesAcquired; 
    sdk.onError = function(e) { mostrarEstado("Error SDK: " + e.message, "error"); };

    buscarLectores();
}

function buscarLectores() {
    sdk.enumerateDevices().then(function(devices) {
        if (devices.length > 0) {
            readerID = devices[0];
            console.log("Lector conectado: " + readerID);
            $("#btnEscanear").prop("disabled", false).text("Iniciar Captura de Huella");
        } else {
            $("#btnEscanear").prop("disabled", true).text("Lector no detectado");
        }
    }, function(error) {
        alert("Error buscando lectores: " + error.message);
    });
}

// 1. FORZAMOS EL FORMATO RAW USANDO EL NÚMERO 1
function comenzarCaptura() {
    if (readerID === "") return;

    // --- CAMBIO CLAVE ---
    // 1 = Raw (El servicio lo está bloqueando)
    // 5 = PngImage (Este SÍ suele pasar y trae dimensiones)
    var formato = 5; 

    console.log("🔵 [JS] Solicitando formato PNG (ID: 5)...");

    sdk.startAcquisition(formato, readerID).then(function() {
        console.log("✅ [JS] Captura iniciada.");
        $("#circuloEstado").css("border-color", "#3498db"); 
    }, function(error) {
        alert("Error al iniciar: " + error.message);
    });
}

// 2. DIAGNÓSTICO TOTAL DE LO QUE RECIBIMOS
var isProcessing = false; // Nuevo semáforo

function onSamplesAcquired(s) {
    if (isProcessing) return; 
    isProcessing = true; // Bloqueamos para evitar doble captura

    console.log("📦 [JS] Procesando muestra...");
    
    // APAGAMOS EL LECTOR INMEDIATAMENTE PARA FORZAR A LEVANTAR EL DEDO
    sdk.stopAcquisition().then(function() {
        $(".scanner-trigger").removeClass("scanning");

        var samples = JSON.parse(s.samples);
        var rawSample = samples[0]; 

        if (typeof rawSample === 'string') {
            rawSample = { "Data": rawSample, "Format": "DirectString" };
        }

        if (rawSample && rawSample.Data) {
            if (conteoMuestras < 6) {
                conteoMuestras++;
                
                var jsonString = JSON.stringify(rawSample);
                $("#huella_" + conteoMuestras).val(jsonString);
                
                console.log("🚀 [JS] Muestra " + conteoMuestras + " lista.");
                
                animarLecturaExito();
                
                    // 🔥 CAMBIO CLAVE: Actualizamos el progreso EN CADA captura
                actualizarProgreso(conteoMuestras);

                if (conteoMuestras < 6) {
                    $("#mensajeHuella").text("Muestra " + conteoMuestras + "/6. LEVANTE EL DEDO y vuelva a colocarlo.");
                    $("#mensajeHuella").css("color", "#d31900");
                    
                    // Esperamos 1.5 segundos y volvemos a encender el lector
                    setTimeout(function() {
                        isProcessing = false;
                        comenzarCaptura();
                    }, 1500);
                } else {
                    // Si ya son 4, terminamos
                    actualizarProgreso(conteoMuestras);
                }
            }
        } else {
            isProcessing = false;
            comenzarCaptura(); // Reintentar si falló
        }
    });
}

function actualizarProgreso(n) {
    var porcentaje = (n / 6) * 100;
    $("#barraProgreso").css("width", porcentaje + "%");
    
    if (n < 6) {
        $("#mensajeHuella").text("Muestra " + n + "/6 capturada (RAW). Levante el dedo.");
        $("#mensajeHuella").css("color", "#333");
    } else {
        // FINALIZADO
        $("#mensajeHuella").text("✅ Captura completada.");
        $("#mensajeHuella").css("color", "green");
        
        $("#iconoEstado").removeClass("glyphicon-hand-up").addClass("glyphicon-ok");
        $("#circuloEstado").css("border-color", "#28a745").css("color", "#28a745").css("background", "#d4edda");
        
        $("#btnGuardar").prop("disabled", false).removeClass("btn-secondary-custom").addClass("btn-success");
        $("#btnReiniciarHuella").show();
        
        sdk.stopAcquisition();
        $("#inputFeatureSet").val("READY");
    }
}

function animarLecturaExito() {
    $("#circuloEstado").css("background-color", "#d4edda");
    setTimeout(function() {
        $("#circuloEstado").css("background-color", "#eee");
    }, 200);
}

function reiniciarProceso() {
    conteoMuestras = 0;
    for(let i=1; i<=6; i++) $("#huella_"+i).val("");
    
    $("#barraProgreso").css("width", "0%");
    $("#mensajeHuella").text("Coloque el dedo en el lector (0/6)").css("color", "#555");
    
    $("#circuloEstado").css("border-color", "#ddd").css("color", "#aaa").css("background", "#eee");
    $("#iconoEstado").removeClass("glyphicon-ok").addClass("glyphicon-hand-up");
    
    $("#btnGuardar").prop("disabled", true);
    $("#btnReiniciarHuella").hide();
    
    comenzarCaptura();
}

function ajaxGuardarUsuario(formulario, controlador, csrfName, csrfHash) {
    var dataString = formulario.serialize() + "&" + csrfName + "=" + csrfHash;
    
    $("#btnGuardar").prop("disabled", true).text("Procesando Biometría...");

    $.ajax({
        url: base_url + controlador, 
        type: "POST",
        data: dataString, 
        dataType: "json",
        success: function(resp) {
            if (resp.status === 'success') {
                
                // 1. Mostrar alerta nativa primero para asegurar que el código no se detenga si falla SweetAlert
                alert("✅ " + (resp.message || "El registro fue correcto"));

                // 2. Lógica de impresión silenciosa (Iframe)
                if (resp.valoresdata) {
                    console.log("Enviando a impresora:", resp.valoresdata.costo_membresia); 

                    var queryString = $.param(resp.valoresdata);
                    
                    // OJO: Asegúrate de que esta ruta sea la correcta en tu nuevo sistema
                    let url = "http://localhost/sistema/vendor/ticket14.php?" + queryString;

               var nuevaVentana = window.open(url, '_blank');
                            setTimeout(function() {
                                if (nuevaVentana) {
                                    nuevaVentana.close();
                                }
                                 }, 5000);
                  




                        location.reload();
                }

            } else {
                alert("❌ Error: " + (resp.message || "Error desconocido"));
                $("#btnGuardar").prop("disabled", false).text("Intentar de Nuevo");
                if(resp.token) $('.txt_csrfname').val(resp.token);
            }
        },
        error: function(xhr) {
            alert("Error de conexión. Revise la consola.");
            console.log(xhr.responseText);
            $("#btnGuardar").prop("disabled", false).text("Guardar");
        }
    });
}

function mostrarEstado(msg, tipo) {
    console.log("[" + tipo + "] " + msg);
}