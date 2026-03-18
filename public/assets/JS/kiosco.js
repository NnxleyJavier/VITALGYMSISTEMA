/**
 * LÓGICA DE KIOSKO (1:1)
 * Captura 1 Huella (PNG) -> Envía a PHP -> PHP compara con todos
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
        console.log("Reiniciando lector...");
        comenzarCaptura();
    });
});

/* --- SDK DIGITALPERSONA --- */
function inicializarLector() {
    console.log("--> Iniciando SDK Kiosko...");
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
            $("#estadoTexto").text("Toca el lector para acceder");
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
    // Esto es vital para que tu app.py "Salvavidas" funcione
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

    // --- CORRECCIÓN DE FORMATO (Igual que en Registro) ---
    // Si llega como string directo, lo envolvemos en objeto
    if (typeof sampleData === 'string') {
        sampleData = { "Data": sampleData, "Format": "DirectString" };
    }

    if (sampleData && sampleData.Data) {
        $("#estadoTexto").text("🔍 Verificando identidad...");
        
        // ¡OJO AQUÍ! Enviamos el OBJETO COMPLETO (JSON String)
        // Python lo necesita así para hacer json.loads()
        var paqueteParaPython = JSON.stringify(sampleData);
        
        enviarValidacion(paqueteParaPython);
    } else {
        console.error("Lectura vacía");
        comenzarCaptura(); 
    }
}

function enviarValidacion(jsonHuella) {
    // Ajusta aquí los nombres de tus tokens CSRF si es necesario
    var dataToSend = {
        huella_feature_set: jsonHuella
    };
    
    // Agregamos CSRF dinámicamente si existe en AppConfig
    if (typeof AppConfig !== 'undefined' && AppConfig.csrfTokenName) {
        dataToSend[AppConfig.csrfTokenName] = AppConfig.csrfHash;
    }

    $.ajax({
        url: AppConfig.baseURL + "verificarHuella", // Tu controlador PHP
        type: "POST",
        dataType: "json",
        data: dataToSend,
        success: function(resp) {
            // Si hay token nuevo, actualizamos
            if (resp.token && typeof AppConfig !== 'undefined') {
                AppConfig.csrfHash = resp.token;
            }
            mostrarResultado(resp);
        },
        error: function(xhr) {
            console.log("Error Ajax:", xhr.responseText);
            $("#estadoTexto").text("Error de conexión");
            setTimeout(comenzarCaptura, 2000); 
        }
    });
}

function mostrarResultado(data) {
    var modal = $("#modalRespuesta");
    
    if (data.status === 'success') {
        // ÉXITO
        $("#modalHeader").css("background-color", "#e8f5e9");
        $("#iconoResultado").attr("class", "glyphicon glyphicon-ok").css("color", "#2e7d32");
        $("#modalTitulo").text("¡Bienvenido!").css("color", "#2e7d32");
        
        $("#nombreCliente").text(data.nombre + " " + data.apellido_paterno);
        $("#nombreMembresia").text("Acceso Autorizado");
        
        // Mostrar lógica de días (si la envías desde PHP)
        if(data.dias_restantes !== undefined) {
             $("#numDias").text(data.dias_restantes);
             $("#infoDias").show();
        } else {
             $("#infoDias").hide();
        }
        
        $("#infoError").hide();

    } else {
        // ERROR / NO ENCONTRADO
        $("#modalHeader").css("background-color", "#ffebee");
        $("#iconoResultado").attr("class", "glyphicon glyphicon-remove").css("color", "#c62828");
        $("#modalTitulo").text("No reconocido").css("color", "#c62828");
        
        $("#nombreCliente").text("Intente de nuevo");
        $("#nombreMembresia").text("");
        
        $("#infoDias").hide();
        $("#infoError").show();
        $("#mensajeError").text(data.message || "Huella no coincide");
    }

    modal.modal('show');

    // Cerrar automático si es éxito
    if (data.status === 'success' || data.status === 'error') {
        setTimeout(function() { modal.modal('hide'); }, 3500);
    }
}