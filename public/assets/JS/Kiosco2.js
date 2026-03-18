/**
 * LÓGICA DE KIOSKO PARA STAFF (ASISTENCIA)
 * Captura 1 Huella (PNG) -> Envía a PHP -> PHP compara con BD Staff
 */

var readerID = ""; 
var sdk = null; 
var isScanning = false; // Semáforo para no enviar doble

$(document).ready(function() {
    inicializarLector();

    // Botón visual por si se detiene el lector
    $("#btnActivarLector").click(function() {
        if (!isScanning) comenzarCaptura();
    });

    // Reiniciar lector al cerrar el modal
    $('#modalRespuesta').on('hidden.bs.modal', function () {
        console.log("Reiniciando lector para siguiente empleado...");
        comenzarCaptura();
    });
});

/* --- SDK DIGITALPERSONA --- */
function inicializarLector() {
    console.log("--> Iniciando SDK Kiosko Staff...");
    try {
        sdk = new Fingerprint.WebApi;
    } catch (e) {
        console.log("Error SDK. ¿Servicio detenido?");
        return;
    }

    sdk.onDeviceConnected = function(e) { buscarLectores(); };
    sdk.onDeviceDisconnected = function(e) { 
        $("#estadoTexto").text("⚠️ Lector desconectado");
        isScanning = false;
    };
    sdk.onSamplesAcquired = onSamplesAcquired; 
    
    buscarLectores();
}

function buscarLectores() {
    sdk.enumerateDevices().then(function(devices) {
        if (devices.length > 0) {
            readerID = devices[0];
            console.log("Lector conectado: " + readerID);
            $("#estadoTexto").text("Toca el lector para registrarte");
            comenzarCaptura();
        } else {
            $("#estadoTexto").text("Buscando lector...");
        }
    }, function(error) {
        console.error(error.message);
    });
}

function comenzarCaptura() {
    if (readerID === "" || isScanning) return;

    // USAMOS FORMATO 5 (PNG) IGUAL QUE EN EL REGISTRO
    var formato = 5; 

    sdk.startAcquisition(formato, readerID).then(function() {
        isScanning = true;
        $(".scanner-trigger").addClass("scanning"); // Activa animación CSS
        $("#estadoTexto").text("Esperando huella...");
    }, function(error) {
        console.error("Error inicio captura: " + error.message);
    });
}

function onSamplesAcquired(s) {
    // Pausamos captura para procesar
    sdk.stopAcquisition().then(function(){ 
        isScanning = false; 
        $(".scanner-trigger").removeClass("scanning");
    });

    var samples = JSON.parse(s.samples);
    var sampleData = samples[0];

    // --- CORRECCIÓN DE FORMATO ---
    if (typeof sampleData === 'string') {
        sampleData = { "Data": sampleData, "Format": "DirectString" };
    }

    if (sampleData && sampleData.Data) {
        $("#estadoTexto").text("🔍 Verificando identidad...");
        
        // Empaquetamos para Python
        var paqueteParaPython = JSON.stringify(sampleData);
        
        enviarValidacionAsistencia(paqueteParaPython);
    } else {
        console.error("Lectura vacía");
        comenzarCaptura(); 
    }
}

function enviarValidacionAsistencia(jsonHuella) {
    var dataToSend = {
        huella_feature_set: jsonHuella
    };
    
    // Agregamos CSRF dinámicamente si existe en AppConfig
    if (typeof AppConfig !== 'undefined' && AppConfig.csrfTokenName) {
        dataToSend[AppConfig.csrfTokenName] = AppConfig.csrfHash;
    }

    $.ajax({
        // IMPORTANTE: Asegúrate de que esta URL exista en tu controlador
        url: AppConfig.baseURL + "RegistroddeAsistencia", 
        type: "POST",
        dataType: "json",
        data: dataToSend,
        success: function(resp) {
            // Si hay token nuevo, actualizamos
            if (resp.token && typeof AppConfig !== 'undefined') {
                AppConfig.csrfHash = resp.token;
            }
            mostrarResultadoAsistencia(resp);
        },
        error: function(xhr) {
            console.log("Error Ajax Staff:", xhr.responseText);
            $("#estadoTexto").text("Error de conexión");
            setTimeout(comenzarCaptura, 2000); 
        }
    });
}

function mostrarResultadoAsistencia(data) {
   var modal = $("#modalRespuesta");
    
    if (data.status === 'success') {
        // ÉXITO (Entrada o Salida)
        var color = data.tipo === 'Entrada' ? '#2ecc71' : '#e67e22'; // Verde para entrada, naranja para salida
        var textoAccion = data.tipo === 'Entrada' ? '¡Entrada Registrada!' : '¡Salida Registrada!';

        $("#modalHeader").css("background-color", data.tipo === 'Entrada' ? "#e8f5e9" : "#fff3e0");
        $("#iconoResultado").attr("class", "glyphicon glyphicon-ok").css("color", color);
        $("#modalTitulo").text(textoAccion).css("color", color);
        
        $("#nombreUsuario").text(data.nombre);
        $("#rolUsuario").text(data.rolUsuario || "Personal");
        
        // Puedes cambiar dinámicamente el texto arriba de la hora
        $("#tipoRegistroTexto").text("Hora de " + data.tipo);
        $("#horaRegistro").text(data.hora).css("color", color);
        $("#infoAsistencia").show();
        $("#infoError").hide();

    } else if (data.status === 'info') {
        // ANTI-REBOTE (Escaneó dos veces rápido)
        $("#modalHeader").css("background-color", "#e3f2fd"); // Azul clarito
        $("#iconoResultado").attr("class", "glyphicon glyphicon-info-sign").css("color", "#1976d2");
        $("#modalTitulo").text("Espera un momento").css("color", "#1976d2");
        
        $("#nombreUsuario").text(data.nombre);
        $("#rolUsuario").text("-");
        
        $("#infoAsistencia").hide();
        $("#infoError").show();
        $("#mensajeError").text(data.mensaje).css("color", "#1976d2");

    } else {
        // ERROR (Huella no encontrada)
        $("#modalHeader").css("background-color", "#ffebee");
        $("#iconoResultado").attr("class", "glyphicon glyphicon-remove").css("color", "#c62828");
        $("#modalTitulo").text("No reconocido").css("color", "#c62828");
        
        $("#nombreUsuario").text("Usuario no encontrado");
        $("#rolUsuario").text("-");
        
        $("#infoAsistencia").hide();
        $("#infoError").show();
        $("#mensajeError").text(data.mensaje || "Huella no coincide.").css("color", "#c62828");
    }

    modal.modal('show');
    // Cerrar automático
    if (data.status === 'success' || data.status === 'info') {
        // 2.5 segundos de espera para que el empleado vea su hora de entrada
        setTimeout(function() { modal.modal('hide'); }, 4500);
    }
    else if (data.status === 'error') {
        // 1.5 segundos para mensajes de info (anti-rebote)
        setTimeout(function() { modal.modal('hide'); }, 2500);
    }
}