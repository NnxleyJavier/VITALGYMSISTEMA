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

    // ENVÍO DEL FORMULARIO DE STAFF
    $("#formRegistroStaff").submit(function(e) {
    e.preventDefault();
        
        if (conteoMuestras < 6) {
            alert("⚠️ Debe capturar la huella 6 veces antes de guardar.");
            return;
        }

        var datosFormulario = $(this);
        var urlAction = $(this).attr('action'); 
        
        // CORRECCIÓN: Tomamos el CSRF directamente del input oculto del formulario
        // en lugar de usar AppConfig
        var csrfName = $('.txt_csrfname').attr('name'); 
        var csrfHash = $('.txt_csrfname').val(); 

        // Agregamos el token CSRF a los datos serializados
        var dataString = datosFormulario.serialize() + "&" + csrfName + "=" + csrfHash;

        ajaxGuardarStaff(dataString, urlAction);
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
                    $("#mensajeHuella").text("Muestra " + conteoMuestras + "/6. LEVANTE EL DEDO y vuelva a colocarlo.");
                    $("#mensajeHuella").css("color", "#d31900");
                    
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
        $("#mensajeHuella").text("Muestra " + n + "/6 capturada. Levante el dedo.");
        $("#mensajeHuella").css("color", "#333");
    } else {
        $("#mensajeHuella").text("✅ Captura completada.");
        $("#mensajeHuella").css("color", "green");
        
        $("#iconoEstado").removeClass("glyphicon-hand-up").addClass("glyphicon-ok");
        $("#circuloEstado").css("border-color", "#28a745").css("color", "#28a745").css("background", "#d4edda");
        
        // Cambié la clase a tu botón morado (btn-secondary-custom)
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
    $("#mensajeHuella").text("Coloque el dedo en el lector (0/6)").css("color", "#555");
    
    $("#circuloEstado").css("border-color", "#ddd").css("color", "#aaa").css("background", "#eee");
    $("#iconoEstado").removeClass("glyphicon-ok").addClass("glyphicon-hand-up");
    
    $("#btnGuardar").prop("disabled", true);
    $("#btnReiniciarHuella").hide();
    
    comenzarCaptura();
}

function ajaxGuardarStaff(dataString, urlAction) {
    $("#btnGuardar").prop("disabled", true).html('<span class="glyphicon glyphicon-refresh"></span> Procesando Biometría...');

    $.ajax({
        url: urlAction, 
        type: "POST",
        data: dataString, 
        dataType: "json",
        success: function(resp) {
            
            // 🔥 CORRECCIÓN AQUÍ: Eliminamos AppConfig y actualizamos directo el input de HTML
            if (resp.token) {
                $('.txt_csrfname').val(resp.token);
            }

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

function mostrarEstado(msg, tipo) {
    console.log("[" + tipo + "] " + msg);
}