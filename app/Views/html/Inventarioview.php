<div class="container-fluid pt-4" style="min-height: calc(100vh - 160px); flex: 1 0 auto;
    padding: 50px 50px; border-radius: 16px; box-shadow: 0 10px 30px 0 rgba(82, 63, 105, 0.03); background-color: #ffffff;">
    
    <div class="row layout-tienda">
        <div class="col-12 mb-4">
            
            <div class="d-flex justify-content-between align-items-center mb-5 bg-white p-4 rounded-vital shadow-vital">
                <h3 class="fw-bold text-dark" style="margin: 10px;">
                    <span class="glyphicon glyphicon-list-alt" style="color: #c4b50d; margin-right: 10px;"></span> 
                    Gestión de Inventario
                </h3>
                <button style="margin: 10px;" class="btn btn-success fw-bold rounded-vital px-4 py-2 shadow-sm" onclick="abrirModalNuevo()" >
                    <span class="glyphicon glyphicon-plus" style="margin-right: 5px;"></span> Nuevo Producto
                </button>
            </div>

            <div class="card shadow-vital rounded-vital border-0">
                <div class="card-body p-4"> <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="text-muted" style="border-bottom: 2px solid #f4f6f9;">
                                <tr>
                                    <th class="pb-3 text-center">ID</th>
                                    <th class="pb-3">Producto</th>
                                    <th class="pb-3">Precio Venta</th>
                                    <th class="pb-3">Stock Actual</th>
                                    <th class="pb-3 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($productos as $p): ?>
                                <tr>
                                    <td class="text-center text-muted">#<?= $p['idProducto'] ?></td>
                                    <td class="fw-bold text-dark" style="font-size: 1.5rem;"><?= $p['Nombre'] ?></td>
                                    <td class="text-primary fw-bold" style="font-size: 1.5rem;">$<?= number_format($p['Precio'], 2) ?></td>
                                    <td>
                                        <span class="badge <?= $p['Stock'] < 5 ? 'bg-danger' : 'bg-success' ?> p-2 px-3" style="font-size: 1 rem; border-radius: 10px;">
                                            <?= $p['Stock'] ?> unidades
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-dark px-3 rounded-vital" 
                                                onclick="abrirModalEditar(<?= $p['idProducto'] ?>, '<?= addslashes($p['Nombre']) ?>', <?= $p['Precio'] ?>, <?= $p['Stock'] ?>)">
                                            <span class="glyphicon glyphicon-pencil"></span> Editar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div> <div class="modal fade" id="modalProducto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document"> <div class="modal-content rounded-vital border-0 shadow-vital">
            <div class="modal-header bg-white border-bottom-0 pb-0 pt-4 px-4">
                <h4 class="modal-title fw-bold text-dark pt-4" id="modalTitle">Nuevo Producto</h4>
            </div>
            <div class="modal-body p-4">
                <form id="formProducto">
                    <input type="hidden" id="idProducto" name="idProducto">
                    
                    <div class="form-group mb-4">
                        <label class="text-muted fw-bold mb-2">Nombre del Producto</label>
                        <input type="text" class="form-control" style="border-radius: 10px; padding: 10px 15px;" id="nombre" name="nombre" required placeholder="Ej. Proteína Whey 1kg">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-muted fw-bold mb-2">Precio de Venta ($)</label>
                            <input type="number" step="0.01" class="form-control" style="border-radius: 10px; padding: 10px 15px;" id="precio" name="precio" required placeholder="0.00">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-muted fw-bold mb-2">Cantidad en Stock</label>
                            <input type="number" class="form-control" style="border-radius: 10px; padding: 10px 15px;" id="stock" name="stock" required placeholder="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 bg-light rounded-vital px-4 py-3" style="border-radius: 0 0 16px 16px !important;">
                <button type="button" class="btn btn-secondary px-4 rounded-vital" onclick="$('#modalProducto').modal('hide')">Cancelar</button>
                <button type="button" class="btn btn-success fw-bold px-4 rounded-vital shadow-sm" id="btnGuardarProducto">
                    <span class="glyphicon glyphicon-floppy-disk"></span> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var base_url = "<?= base_url() ?>";

    function abrirModalNuevo() {
        $('#formProducto')[0].reset(); 
        $('#idProducto').val(''); 
        $('#modalTitle').text('Registrar Nuevo Producto');
        $('#modalProducto').modal('show');
    }

    function abrirModalEditar(id, nombre, precio, stock) {
        $('#idProducto').val(id);
        $('#nombre').val(nombre);
        $('#precio').val(precio);
        $('#stock').val(stock);
        $('#modalTitle').text('Editar Inventario');
        $('#modalProducto').modal('show');
    }

    $('#btnGuardarProducto').on('click', function() {
        if(!$('#nombre').val() || !$('#precio').val() || !$('#stock').val()) {
            alert("Por favor llena todos los campos.");
            return;
        }

        let datosFormulario = $('#formProducto').serialize();
        
        $.post(base_url + 'guardarProducto', datosFormulario, function(res) {
            if(res.status === 'success') {
                alert(res.mensaje);
                location.reload(); 
            } else {
                alert("Error: " + res.mensaje);
            }
        }, 'json').fail(function() {
            alert("Error de conexión al guardar el producto.");
        });
    });
</script>