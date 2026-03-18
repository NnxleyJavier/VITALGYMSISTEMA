<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .card-renovacion { 
        font-family: 'Poppins', sans-serif;
        margin: 20px auto 40px; 
        background-color: #ffffff; 
        color: #333333; 
        padding: 30px 50px; 
        border-radius: 12px; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #ff9800; /* Naranja para identificar renovaciones */
        border-bottom: 5px solid #ff9800;
        width: 100%; 
        max-width: 900px; 
    }
    .form-section-title { font-size: 15px; color: #ff9800; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 15px; }
    .cliente-header { background-color: #f8f9fa; padding: 15px 20px; border-radius: 8px; border-left: 4px solid #ff9800; margin-bottom: 25px; }
    .cliente-header h4 { margin: 0; font-weight: 600; color: #333; }
    
    .form-control { border-radius: 6px; box-shadow: none; border: 1px solid #e0e0e0; }
    .form-control:focus { border-color: #ff9800; box-shadow: 0 0 5px rgba(255, 152, 0, 0.3); }
    
    .checkbox-extra { display: block; margin-bottom: 10px; font-weight: 500; cursor: pointer; }
    
    .total-box { background-color: #333; color: white; padding: 15px; border-radius: 8px; text-align: right; font-size: 20px; font-weight: bold; margin-top: 20px; }
    .total-box span { color: #ff9800; font-size: 28px; }
    
    .btn-renovar { background-color: #ff9800; color: white; font-weight: 600; border-radius: 6px; padding: 12px; transition: all 0.3s; border: none; }
    .btn-renovar:hover { background-color: #e68a00; color: white; box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3); }
</style>

<div class="container">
    <div class="card-renovacion">
        <h3 style="font-weight: 800; margin-top: 0; text-align: center;"><span class="glyphicon glyphicon-refresh" style="color: #ff9800;"></span> Renovar Membresía</h3>
        <p class="text-center text-muted" style="margin-bottom: 30px;">Selecciona el nuevo paquete y servicios adicionales.</p>

        <div class="cliente-header">
            <h4><?= esc($cliente['Nombre'] . ' ' . $cliente['ApellidoP'] . ' ' . $cliente['ApellidoM']) ?></h4>
            <small class="text-muted"><span class="glyphicon glyphicon-phone"></span> Tel: <?= esc($cliente['Telefono']) ?></small>
        </div>

        <form id="formRenovacion"
                data-url-guardar="<?= base_url('/renovacionesguardar') ?>" 
                 data-url-panel="<?= base_url('/renovaciones') ?>">
                 
            <?= csrf_field() ?>
            <input type="hidden" name="cliente_id" value="<?= esc($cliente['IDClientes']) ?>">

            <div class="row">
                <div class="col-md-6">
                    <h5 class="form-section-title"><span class="glyphicon glyphicon-star"></span> 1. Paquete Principal</h5>
                    <div class="form-group">
                        <label>Tipo de Membresía</label>
                        <select name="servicio_id" id="selectServicio" class="form-control" required>
                            <option value="">-- Selecciona una membresía --</option>
                            <?php foreach($membresias as $mem): ?>
                                <option value="<?= $mem['IDServicios'] ?>" data-costo="<?= $mem['Costo'] ?>">
                                    <?= esc($mem['NombreMembresia']) ?> (<?= $mem['LapsoDias'] ?> días) - $<?= number_format($mem['Costo'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label>Método de Pago</label>
                        <select name="tipo_pago" class="form-control" required>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta / Terminal</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5 class="form-section-title"><span class="glyphicon glyphicon-plus-sign"></span> 2. Servicios Adicionales</h5>
                    <div class="form-group" style="background-color: #fafafa; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
                        <?php if(!empty($extras)): ?>
                            <?php foreach($extras as $extra): ?>
                                <label class="checkbox-extra">
                                    <input type="checkbox" name="extras[]" class="extra-checkbox" value="<?= $extra['IDServicios'] ?>" data-costo="<?= $extra['Costo'] ?>">
                                    <?= esc($extra['NombreMembresia']) ?> <span style="color: #28a745;">(+$<?= number_format($extra['Costo'], 2) ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No hay servicios extra registrados.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="total-box">
                        Total a Cobrar: $<span id="lblTotal">0.00</span>
                        <input type="hidden" name="monto_total" id="inputMontoTotal" value="0">
                    </div>
                    <button type="submit" class="btn btn-renovar btn-block btn-lg" style="margin-top: 20px;">
                        <span class="glyphicon glyphicon-piggy-bank"></span> Procesar Pago y Renovar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="<?= base_url('assets/JS/Renovaciones.js') ?>"></script>