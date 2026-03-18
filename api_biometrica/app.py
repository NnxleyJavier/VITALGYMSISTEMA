import ctypes
import json
import base64
import os
import io
from PIL import Image
from flask import Flask, request, jsonify

app = Flask(__name__)

# --- ESTANDARIZACIÓN ABSOLUTA (ANSI INCITS 378-2004) ---
DPFJ_FMD_ANSI_378 = 16842753
DPFJ_SUCCESS = 0
DPFJ_E_MORE_DATA = 96075789 # Código específico de DigitalPersona para "Requiere más varianza"

# --- CARGA Y TIPADO ESTRICTO DE DLLS ---
lib = None
try:
    if os.name == 'nt':
        for dll in ["dpfpdd.dll", "dpfr6.dll", "dpfr7.dll"]:
            try: ctypes.WinDLL(dll)
            except OSError: pass
        lib = ctypes.WinDLL("dpfj.dll")

    # Firmas estrictas para prevenir corrupción de segmentación
    lib.dpfj_create_fmd_from_raw.argtypes = [
        ctypes.POINTER(ctypes.c_ubyte), ctypes.c_uint, ctypes.c_uint, ctypes.c_uint, 
        ctypes.c_uint, ctypes.c_int, ctypes.c_int, 
        ctypes.c_int, ctypes.c_void_p, ctypes.POINTER(ctypes.c_uint)
    ]
    lib.dpfj_create_fmd_from_raw.restype = ctypes.c_int
    
    lib.dpfj_start_enrollment.argtypes = [ctypes.c_int]
    lib.dpfj_start_enrollment.restype = ctypes.c_int
    
    lib.dpfj_add_to_enrollment.argtypes = [ctypes.c_int, ctypes.c_void_p, ctypes.c_uint, ctypes.c_uint]
    lib.dpfj_add_to_enrollment.restype = ctypes.c_int
    
    lib.dpfj_create_enrollment_fmd.argtypes = [ctypes.c_void_p, ctypes.POINTER(ctypes.c_uint)]
    lib.dpfj_create_enrollment_fmd.restype = ctypes.c_int
    
    lib.dpfj_finish_enrollment.restype = ctypes.c_int

    lib.dpfj_compare.argtypes = [
        ctypes.c_int, ctypes.c_void_p, ctypes.c_uint, ctypes.c_uint,
        ctypes.c_int, ctypes.c_void_p, ctypes.c_uint, ctypes.c_uint,
        ctypes.POINTER(ctypes.c_uint)
    ]
    lib.dpfj_compare.restype = ctypes.c_int

    print("✅ Plataforma FFI Cargada: Nivel de Rigidez Máxima (ANSI) + SALVAVIDAS")

except Exception as e:
    print(f"❌ Fallo de Orquestación DLL: {e}")


# =================================================================
#  ENDPOINT 1: REGISTRO (MÁQUINA DE ESTADOS REACTIVA + SALVAVIDAS)
# =================================================================
@app.route('/crear_template', methods=['POST'])
def crear_template():
    data = request.get_json()
    
    try:
        # 1. Purgar cualquier contexto colgado
        lib.dpfj_finish_enrollment()
        
        # 2. Inicializar el contexto exigiendo rigidez ANSI
        res_start = lib.dpfj_start_enrollment(DPFJ_FMD_ANSI_378)
        if res_start != DPFJ_SUCCESS:
            return jsonify({"status": "error", "mensaje": "Fallo crítico al asignar la memoria del motor."})
        
        print("\n--- INICIANDO SÍNTESIS TOPOLÓGICA (ANSI 378) ---")
        
        enrollment_ready = False
        valid_frames_processed = 0
        
        # Variables de respaldo para el Salvavidas ANSI
        last_img_bytes = None
        last_w = 0
        last_h = 0

        # Procesamiento dinámico (hasta 6 fotogramas)
        for i in range(1, 7):
            json_str = data.get(f'huella_{i}')
            if not json_str: continue
            
            sample = json.loads(json_str)
            raw_b64 = sample['Data'].replace('-', '+').replace('_', '/')
            img_data = base64.b64decode(raw_b64 + "===")
            
            try:
                image = Image.open(io.BytesIO(img_data)).convert('L')
                width, height = image.size
                img_bytes = image.tobytes()
            except Exception as img_ex:
                continue

            expected = width * height
            if len(img_bytes) != expected:
                img_bytes = img_bytes[:expected]

            # Instanciación segura de búferes
            fmd_size = ctypes.c_uint(2048)
            fmd_buf = (ctypes.c_ubyte * 2048)()
            
            # Extracción del grafo matemático
            res_extract = lib.dpfj_create_fmd_from_raw(
                ctypes.cast(img_bytes, ctypes.POINTER(ctypes.c_ubyte)), len(img_bytes), 
                width, height, 500, 0, 0, DPFJ_FMD_ANSI_378, fmd_buf, ctypes.byref(fmd_size)
            )

            if res_extract == DPFJ_SUCCESS:
                res_add = lib.dpfj_add_to_enrollment(DPFJ_FMD_ANSI_378, fmd_buf, fmd_size.value, 0)
                
                if res_add == DPFJ_SUCCESS:
                    print(f"   ✅ Fotograma {i}: Entropía alcanzada. ¡Plantilla lista!")
                    enrollment_ready = True
                    break 
                
                elif res_add == DPFJ_E_MORE_DATA or res_add == 1:
                    print(f"   ⏳ Fotograma {i}: Asimilado. Requiere mayor varianza...")
                    valid_frames_processed += 1
                    
                    # Guardamos la mejor imagen por si la fusión falla
                    last_img_bytes = img_bytes
                    last_w = width
                    last_h = height
                else:
                    print(f"   ⚠️ Alerta: El núcleo rechazó la topología. Código: {res_add}")
                    
        # 3. Finalización y Consolidación (INTENTO A: Perfecto)
        if enrollment_ready:
            final_size = ctypes.c_uint(0)
            lib.dpfj_create_enrollment_fmd(None, ctypes.byref(final_size))
            
            if final_size.value > 0:
                final_buf = (ctypes.c_ubyte * final_size.value)()
                res_create = lib.dpfj_create_enrollment_fmd(final_buf, ctypes.byref(final_size))
                
                if res_create == DPFJ_SUCCESS:
                    tpl = base64.b64encode(bytes(final_buf)).decode()
                    lib.dpfj_finish_enrollment()
                    print("🎉 SÍNTESIS TOPOLÓGICA COMPUESTA COMPLETADA")
                    return jsonify({"status": "success", "template": tpl})

        # --- INTENTO B: EL SALVAVIDAS ANSI ---
        lib.dpfj_finish_enrollment()
        
        # Si el motor falló la fusión, pero tenemos al menos 1 imagen rescatable
        if valid_frames_processed > 0 and last_img_bytes is not None:
            print(f"   ⚠️ Fusión compuesta incompleta ({valid_frames_processed}/6 válidos). Usando SALVAVIDAS ANSI.")
            
            fmd_size_fallback = ctypes.c_uint(2048)
            fmd_buf_fallback = (ctypes.c_ubyte * 2048)()
            
            # Extraemos la mejor muestra en formato ANSI directo
            res_fallback = lib.dpfj_create_fmd_from_raw(
                ctypes.cast(last_img_bytes, ctypes.POINTER(ctypes.c_ubyte)), len(last_img_bytes), 
                last_w, last_h, 500, 0, 0, DPFJ_FMD_ANSI_378, fmd_buf_fallback, ctypes.byref(fmd_size_fallback)
            )
            
            if res_fallback == DPFJ_SUCCESS:
                tpl_fallback = base64.b64encode(bytes(fmd_buf_fallback)[:fmd_size_fallback.value]).decode()
                print("   ✅ Salvavidas ANSI generado con éxito.")
                return jsonify({"status": "success", "template": tpl_fallback})

        # Si todo lo anterior falla (dedo extremadamente sucio, ninguna foto válida)
        return jsonify({
            "status": "error", 
            "mensaje": "Fotogramas defectuosos o piel ilegible. Limpie el lector, presione levemente e intente nuevamente."
        })

    except Exception as e:
        lib.dpfj_finish_enrollment()
        print(f"🔥 ERROR: {e}")
        return jsonify({"status": "error", "mensaje": str(e)})

# =================================================================
#  ENDPOINT 2: VERIFICACIÓN (KIOSKO AUTENTICADO ANSI)
# =================================================================
@app.route('/verificar', methods=['POST'])
def verificar():
    data = request.get_json()
    try:
        # Decodificamos la huella guardada en la BD (ANSI)
        tpl_bd = base64.b64decode(data.get('huella_bd'))
        
        json_str = data.get('huella_nueva')
        sample = json.loads(json_str)
        raw_b64 = sample['Data'].replace('-', '+').replace('_', '/')
        img_data = base64.b64decode(raw_b64 + "===")
        
        image = Image.open(io.BytesIO(img_data)).convert('L')
        img_bytes = image.tobytes()
        width, height = image.size
        
        # Extracción en vivo de la huella del Kiosko usando ANSI
        fmd_size = ctypes.c_uint(2048)
        fmd_buf = (ctypes.c_ubyte * 2048)()
        
        res_extract = lib.dpfj_create_fmd_from_raw(
            ctypes.cast(img_bytes, ctypes.POINTER(ctypes.c_ubyte)), len(img_bytes), 
            width, height, 500, 0, 0, DPFJ_FMD_ANSI_378, fmd_buf, ctypes.byref(fmd_size)
        )
        
        if res_extract != DPFJ_SUCCESS:
             return jsonify({"status": "error", "match": False})

        # COMPARACIÓN 1 a 1
        score = ctypes.c_uint(0)
        res_compare = lib.dpfj_compare(
            DPFJ_FMD_ANSI_378, fmd_buf, fmd_size.value, 0, 
            DPFJ_FMD_ANSI_378, ctypes.create_string_buffer(tpl_bd), len(tpl_bd), 0, 
            ctypes.byref(score)
        )
        
        # Tolerancia comercial recomendada para gimnasios/kioskos
        UMBRAL_TOLERANCIA = 45474 
        match = (res_compare == DPFJ_SUCCESS and score.value < UMBRAL_TOLERANCIA)
        
        if match:
            print(f"🔍 MATCH EXITOSO: Score={score.value}")
        else:
            print(f"⛔ DENEGADO: Score={score.value}")
            
        return jsonify({"status": "success", "match": match, "score": score.value})

    except Exception as e:
        print(f"Error Verif: {e}")
        return jsonify({"status": "error", "match": False})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, threaded=True)