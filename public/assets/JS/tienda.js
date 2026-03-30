
$(document).ready(function() {
    let carrito = [];

    // --- BUSCADOR EN TIEMPO REAL ---
    $("#searchProduct").on("keyup", function() {
        let value = $(this).val().toLowerCase();
        $(".item-producto").filter(function() {
            $(this).toggle($(this).data("nombre").indexOf(value) > -1)
        });
    });
    
// --- AGREGAR AL CARRITO ---
$('.btn-add').on('click', function() {
        const item = {
            id: $(this).data('id'),
            nombre: $(this).data('nombre'),
            precio: parseFloat($(this).data('precio')),
            stock: parseInt($(this).data('stock')) // Capturamos el stock real
        };

        let existe = carrito.find(p => p.id === item.id);
        
        if (existe) {
            // VERIFICACIÓN DE STOCK: Si la cantidad en el carrito ya es igual al stock disponible, bloqueamos
            if (existe.cantidad >= item.stock) {
                alert(`¡Stock insuficiente! Solo quedan ${item.stock} unidades de ${item.nombre}.`);
                return; // Detiene la ejecución y no suma
            }
            existe.cantidad++;
        } else {
            // Verificación por si acaso el stock es 0 (aunque desde el controlador ya los filtras)
            if (item.stock < 1) {
                alert("Este producto está agotado.");
                return;
            }
            item.cantidad = 1;
            carrito.push(item);
        }
        
        actualizarUI();
    });

    function actualizarUI() {
        if (carrito.length === 0) {
            $("#emptyCart").show();
            $("#cartList .cart-item").remove();
            $("#btnFinalizar").prop("disabled", true);
        } else {
            $("#emptyCart").hide();
            let html = '';
            let total = 0;

            carrito.forEach((p, index) => {
                html += `
                <div class="cart-item d-flex align-items-center p-3 border-bottom">
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold">${p.nombre}</h6>
                        <small class="text-muted">$${p.precio} x ${p.cantidad}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">$${(p.precio * p.cantidad).toFixed(2)}</div>
                        <button class="btn btn-sm text-danger p-0" onclick="eliminarItem(${index})">
                            <small>Eliminar</small>
                        </button>
                    </div>
                </div>`;
                total += (p.precio * p.cantidad);
            });

            $("#cartList .cart-item").remove();
            $("#cartList").append(html);
            $("#total, #subtotal").text('$' + total.toFixed(2));
            $("#btnFinalizar").prop("disabled", false);
        }
    }

    window.eliminarItem = function(index) {
        carrito.splice(index, 1);
        actualizarUI();
    };

    // --- FINALIZAR VENTA ---
    $('#btnFinalizar').on('click', function() {
        if(confirm("¿Confirmar venta de VitalGym?")) {
            
            // Construimos la URL limpiamente usando la variable JS
            // Aseguramos de agregar la barra "/" por si baseURL no la trae al final
            let urlVenta = AppConfig.baseURL + "registrarVenta";
            // Asegúrate de enviar también el Token CSRF si lo tienes activado en CI4
            let datosPost = { 
                productos: carrito,
                [AppConfig.csrfTokenName]: AppConfig.csrfHash // Usando las variables que declaraste en tu vista
            };

            $.post(urlVenta, datosPost, function(res) {
                if(res.status === 'success') {
                    alert(res.mensaje);
                    console.log("Venta registrada exitosamente:", carrito);
                    location.reload(); 
                } else {
                    alert("Error: " + res.mensaje);
                }
                }, 'json').fail(function() {
                    alert("Hubo un problema de conexión al procesar la venta.");
                });

        
        }
    });

});
