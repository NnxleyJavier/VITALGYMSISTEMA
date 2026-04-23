<script>
    var AppConfig = {
        baseURL: "<?= base_url() ?>",
        csrfTokenName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>"
    };
</script>
<link rel="stylesheet" href="<?= base_url('assets/CSS/tienda.css') ?>">
<script src="<?= base_url('assets/JS/tienda.js') ?>" defer></script>

<div class="container-fluid pt-4" style="min-height: calc(100vh - 160px); flex: 1 0 auto;
    padding: 50px 50px; border-radius: 16px; box-shadow: 0 10px 30px 0 rgba(82, 63, 105, 0.03); background-color: #f1f1f1;">
    
    <div class="col-xl-8 col-lg-7 mb-4">
        
        <div class="margin d-flex justify-content-between align-items-center mb-4 bg-black p-3 rounded-vital shadow-vital">
            <h3 class="fw-bold text-dark" style="margin: 0;"><span class="glyphicon glyphicon-tags" style="color: #0dc425; margin-right: 10px;"></span> Punto de Venta</h3>
            <div class="w-50">
                <input type="text" id="searchProduct" class="form-control input-buscador margin" placeholder="Buscar producto...">
            </div>
        </div>

        <div class="row" id="productGrid">
            <?php foreach($productos as $p): ?>
            <div class="col-xl-3 col-md-4 col-sm-6 col-6 mb-4 item-producto" data-nombre="<?= strtolower($p->Nombre) ?>">
                <div class="card h-100 shadow-vital product-card bg-white">
                    <span class="badge <?= $p->Stock < 5 ? 'bg-danger' : 'bg-success' ?> badge-stock">
                        <?= $p->Stock ?> en stock
                    </span>
                    
                    <div class="img-container border-bottom">
                        <?php $nombreImagen = (!empty($p->Imagen)) ? $p->Imagen : 'agua.png'; ?> 
                        <!-- Se mandeja por Rutas depediendo la base de datos -->
                       
                        <img src="<?= $nombreImagen ?>" alt="<?= $p->Nombre ?>">
                    </div>
                    

                    <div class="card-body p-3 text-center d-flex flex-column justify-content-between" style="flex-grow: 1;">
                        <h6 class="card-title fw-bold mb-1 texto-recortado" title="<?= $p->Nombre ?>"><?= $p->Nombre ?></h6>
                        <h4 class="text-primary fw-bold mb-3">$<?= number_format($p->Precio, 2) ?></h4>
                        
                        <button class="btn btn-dark w-100 btn-add btn-sm mt-auto" 
                            data-id="<?= $p->idProducto ?>" 
                            data-nombre="<?= $p->Nombre ?>" 
                            data-precio="<?= $p->Precio ?>"
                            data-stock="<?= $p->Stock ?>"> <span class="glyphicon glyphicon-shopping-cart"></span> Añadir
                    </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card shadow-vital cart-container bg-white">
            <div class="card-header bg-white py-3 border-bottom">
                <h4 class="mb-0 fw-bold" style="margin: 0;"><span class="glyphicon glyphicon-list-alt"></span> Resumen de Venta</h4>
            </div>
            
            <div class="card-body p-0 cart-body">
                <div id="cartList" class="w-100">
                    <div class="text-center text-muted" id="emptyCart" style="padding: 60px 20px;">
                        <span class="glyphicon glyphicon-shopping-cart" style="font-size: 4rem; color: #e4e6ef;"></span>
                        <p class="mt-3">Añade productos para cobrar</p>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white p-4 shadow-top">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-bold" id="subtotal">$0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="h3 fw-bold mb-0" style="margin: 0;">TOTAL</span>
                    <span class="h3 fw-bold text-success mb-0" id="total" style="margin: 0;">$0.00</span>
                </div>
                <button id="btnFinalizar" class="btn btn-success btn-lg w-100 fw-bold shadow-sm rounded-vital" disabled>
                    <span class="glyphicon glyphicon-ok"></span> COMPLETAR VENTA
                </button>
            </div>
        </div>
    </div>
</div>