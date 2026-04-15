/**
 * Lógica: Captura IMAGEN RAW (6 veces) -> Envío a PHP -> Python extrae FMD -> BD (Tabla Users)
 */

// VARIABLES GLOBALES
var conteoMuestras = 0;
var readerID = ""; 
var sdk = null; 
var isProcessing = false;

$(document).ready(function() {
    // 1. Inicializar SDK al cargar la página
    inicializarLector();

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

// =========================================================
    // 1. ESCUCHADOR DE AMBOS FORMULARIOS
    // =========================================================
    $("#formRegistroStaff, #formRegistroCliente").submit(function(e) {
        e.preventDefault();
        
        // REGLA ESTRICTA: 6 Muestras
        if (conteoMuestras < 6) {
            alert("⚠️ Debe capturar la huella 6 veces antes de guardar.");
            return;
        }

        var formId = $(this).attr('id'); // Identificamos qué formulario se envió
        var urlAction = $(this).attr('action'); 
        var datosFormulario = $(this);
        
        // Tomamos el CSRF
        var csrfName = $('.txt_csrfname').attr('name'); 
        var csrfHash = $('.txt_csrfname').val();

   // CORRECCIÓN: Leemos las huellas directamente de los inputs ocultos 
        // tal y como lo hace tu función original onSamplesAcquired
        var dataString = datosFormulario.serialize() + 
                         "&" + csrfName + "=" + csrfHash +
                         "&huella_1=" + encodeURIComponent($("#huella_1").val()) + 
                         "&huella_2=" + encodeURIComponent($("#huella_2").val()) + 
                         "&huella_3=" + encodeURIComponent($("#huella_3").val()) + 
                         "&huella_4=" + encodeURIComponent($("#huella_4").val()) + 
                         "&huella_5=" + encodeURIComponent($("#huella_5").val()) + 
                         "&huella_6=" + encodeURIComponent($("#huella_6").val());

        // CONDICIONAL PARA SEPARAR LÓGICA
        if (formId === 'formRegistroStaff') {
            ajaxGuardarStaff(dataString, urlAction);
        } else if (formId === 'formRegistroCliente') {
            ajaxGuardarCliente(dataString, urlAction);
        }
    });
});


/* --- FUNCIONES DEL SDK --- */

function inicializarLector() {
    console.log("--> Iniciando SDK WebApi para Staff...");
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

function comenzarCaptura() {
    if (readerID === "") return;

    var formato = 5; // PngImage

    console.log("🔵 [JS] Solicitando formato PNG (ID: 5)...");

    sdk.startAcquisition(formato, readerID).then(function() {
        console.log("✅ [JS] Captura iniciada.");
        $("#circuloEstado").css("border-color", "#8e44ad"); // Color morado del staff
    }, function(error) {
        alert("Error al iniciar: " + error.message);
    });
}

function onSamplesAcquired(s) {
    if (isProcessing) return; 
    isProcessing = true; 

    console.log("📦 [JS] Procesando muestra...");
    
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
                actualizarProgreso(conteoMuestras);

                if (conteoMuestras < 6) {
                    // Texto Staff
                    $("#mensajeHuella").text("Muestra " + conteoMuestras + "/6. LEVANTE EL DEDO y vuelva a colocarlo.").css("color", "#d31900");
                    // Texto Cliente
                    $("#contadorMuestras").text(conteoMuestras + " de 6. LEVANTE EL DEDO y vuelva a colocarlo.").css("color", "#d31900");

                    $("#mensajeHuella").css("color", "#d31900");
                    $("#contadorMuestras").css("color", "#d31900");

                    
                    setTimeout(function() {
                        isProcessing = false;
                        comenzarCaptura();
                    }, 1500);
                } else {
                    actualizarProgreso(conteoMuestras);
                }
            }
        } else {
            isProcessing = false;
            comenzarCaptura(); 
        }
    });
}

function actualizarProgreso(n) {
    var porcentaje = (n / 6) * 100;
    $("#barraProgreso").css("width", porcentaje + "%");
    
    if (n < 6) {
        // Texto Staff
        $("#mensajeHuella").text("Muestra " + n + "/6 capturada. Levante el dedo.").css("color", "#333");
        // Texto Cliente
        $("#contadorMuestras").text(n + " de 6 muestras capturadas. Levante el dedo.").css("color", "#333");
    } else {
        // Texto Staff
        $("#mensajeHuella").text("✅ Captura completada.").css("color", "green");
        // Texto Cliente
        $("#contadorMuestras").text("✅ Captura completada.").css("color", "green");
        
        $("#iconoEstado").removeClass("glyphicon-hand-up").addClass("glyphicon-ok");
        $("#circuloEstado").css("border-color", "#28a745").css("color", "#28a745").css("background", "#d4edda");
        
        $("#btnGuardar").prop("disabled", false);
        $("#btnReiniciarHuella").show();
        
        sdk.stopAcquisition();
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
    isProcessing = false;
    for(let i=1; i<=6; i++) $("#huella_"+i).val("");
    
    $("#barraProgreso").css("width", "0%");
    
    // Resetear textos
    $("#mensajeHuella").text("Coloque el dedo en el lector (0/6)").css("color", "#555");
    $("#contadorMuestras").text("0 de 6 muestras capturadas").css("color", "#555");
    
    $("#circuloEstado").css("border-color", "#ddd").css("color", "#aaa").css("background", "#eee");
    $("#iconoEstado").removeClass("glyphicon-ok").addClass("glyphicon-hand-up");
    
    $("#btnGuardar").prop("disabled", true);
    $("#btnReiniciarHuella").hide();
    
    comenzarCaptura();
}

// =========================================================
    // 2. FUNCIÓN AJAX PARA EL STAFF (Tu código original)
    // =========================================================
    function ajaxGuardarStaff(dataString, urlAction) {
        $("#btnGuardar").prop("disabled", true).html('<span class="glyphicon glyphicon-refresh fast-spin"></span> Procesando Biometría...');

        $.ajax({
            url: urlAction, 
            type: "POST",
            data: dataString, 
            dataType: "json",
            success: function(resp) {
                // Actualizamos token CSRF
                if (resp.token) $('.txt_csrfname').val(resp.token);

                if (resp.status === 'success') {
                    alert("✅ " + resp.mensaje);
                    reiniciarProceso();
                    $("#zonaCaptura").hide();
                    $("#btnEscanear").show().text("Huella Guardada - Cambiar Huella");
                    $("#btnGuardar").html('<span class="glyphicon glyphicon-floppy-disk"></span> Guardar Huella en mi Perfil');
                } else {
                    alert("❌ Error: " + (resp.mensaje || "Error desconocido"));
                    $("#btnGuardar").prop("disabled", false).html('<span class="glyphicon glyphicon-floppy-disk"></span> Intentar de Nuevo');
                }
            },
            error: function(xhr) {
                alert("Error de conexión. Revise la consola.");
                console.log(xhr.responseText);
                $("#btnGuardar").prop("disabled", false).html('<span class="glyphicon glyphicon-floppy-disk"></span> Guardar Huella en mi Perfil');
            }
        });
    }


    // =========================================================
    // 3. FUNCIÓN AJAX PARA EL CLIENTE (Con SweetAlert y redirección)
    // =========================================================
function ajaxGuardarCliente(dataString, urlAction) {
        $("#btnGuardar").prop("disabled", true).html('<span class="glyphicon glyphicon-refresh fast-spin"></span> Procesando Biometría...');

        $.ajax({
            url: urlAction, 
            type: "POST",
            data: dataString, 
            dataType: "json",
            success: function(resp) {
                if (resp.token) $('.txt_csrfname').val(resp.token);

                if (resp.status === 'success') {
             Swal.fire({
                        icon: 'success',
                        title: '¡Enrolamiento Exitoso!',
                        text: resp.mensaje,
                        confirmButtonText: 'Terminar y volver',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Si el controlador nos manda la ruta exacta, la usamos
                            if (resp.redirect) {
                                window.location.href = resp.redirect;
                            } else {
                                // Plan B infalible: Fuerza la ruta base + /recepcion
                                window.location.href = window.location.origin + '/recepcion';
                            }
                        }
                    });
                } else {
                    Swal.fire('Error', resp.mensaje || "Error desconocido", 'error');
                    $("#btnGuardar").prop("disabled", false).html('<span class="glyphicon glyphicon-floppy-disk"></span> Intentar de Nuevo');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Fallo de conexión. Revise la consola.', 'error');
                console.log(xhr.responseText);
                $("#btnGuardar").prop("disabled", false).html('<span class="glyphicon glyphicon-floppy-disk"></span> Guardar Huella del Cliente');
            }
        });
    }   

    
function mostrarEstado(msg, tipo) {
    console.log("[" + tipo + "] " + msg);
}