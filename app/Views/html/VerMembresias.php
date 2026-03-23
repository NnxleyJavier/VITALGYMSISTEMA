<style>
    /* Estilos exclusivos para la vista de membresías */
    .content-vital { padding: 30px; }
    
    .vital-portlet-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .vital-portlet-title { font-size: 1.8rem; font-weight: 700; color: var(--text-main); margin: 0; letter-spacing: -0.5px; }
    
    .btn-back { color: #7e8299; background: white; border-radius: 8px; padding: 8px 16px; font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,0.04); transition: all 0.3s ease; text-decoration: none; }
    .btn-back:hover { color: var(--primary-color); box-shadow: 0 4px 12px rgba(0,0,0,0.08); text-decoration: none; }

    .card-elegant { background: var(--card-bg); border-radius: 16px; box-shadow: 0 10px 30px 0 rgba(82, 63, 105, 0.05); border: none; padding: 30px; margin-bottom: 30px; }
    .card-table-wrapper { padding: 0; overflow: hidden; }

    .filter-label { font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 1px; margin-bottom: 10px; display: block; }
    .form-control-custom { background-color: #f8f9fa; border: 1px solid transparent; color: #595d6e; border-radius: 10px; height: 50px; font-weight: 500; padding: 10px 20px; box-shadow: none !important; transition: all 0.3s ease; }
    .form-control-custom:focus { border-color: #e2e5ec; background-color: #ffffff; box-shadow: 0 0 15px rgba(0,0,0,0.03) !important; }

    .btn-custom-primary { background-color: var(--primary-color); color: #fff; font-weight: 600; padding: 0 25px; border-radius: 10px; height: 50px; border: none; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; }
    .btn-custom-primary:hover { background-color: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 8px 15px rgba(93, 120, 255, 0.4); color: white; }
    
    .btn-custom-outline { border: 1px solid var(--border-light); color: #74788d; background: white; font-weight: 600; border-radius: 10px; height: 50px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
    .btn-custom-outline:hover { background: #f8f9fa; color: #3f4254; border-color: #d1d4db; }

    .table-custom { width: 100%; margin: 0; }
    .table-custom thead th { background-color: #ffffff; color: var(--text-muted); font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid var(--border-light); padding: 20px 25px; }
    .table-custom tbody td { vertical-align: middle; padding: 20px 25px; font-size: 14px; color: #595d6e; border-top: 1px solid #f4f5f8; transition: background-color 0.2s; }
    .table-custom tr:hover td { background-color: #fcfdfe; }

    .user-title { font-weight: 600; color: var(--text-main); font-size: 15px; display: block; margin-bottom: 3px; }
    .user-subtitle { font-size: 13px; color: var(--text-muted); }
    
    .badge-pill { padding: 6px 16px; border-radius: 50px; font-weight: 600; font-size: 12px; display: inline-block;}
    .badge-membresia { background-color: #f1f6ff; color: var(--primary-color); border: 1px solid #e1ebff;}
    .badge-status-active { background-color: #e8fff3; color: #0bb783; border: 1px solid #c9f7f5; }
    .badge-status-inactive { background-color: #fff5f8; color: #f64e60; border: 1px solid #ffe2e5; }
    
    .btn-action { width: 38px; height: 38px; border-radius: 10px; background: #f1f6ff; color: var(--primary-color); display: inline-flex; justify-content: center; align-items: center; transition: all 0.3s; text-decoration: none; }
    .btn-action:hover { background: var(--primary-color); color: white; transform: scale(1.05); box-shadow: 0 4px 10px rgba(93, 120, 255, 0.3); }

    @media (max-width: 768px) { .btn-custom-outline { margin-top: 15px; } .content-vital { padding: 15px; } }
</style>

<div class="content-vital flex-grow-1">
    
    <div class="vital-portlet-head">
        <h3 class="vital-portlet-title">Gestión General de Membresías</h3>
        <a href="<?= base_url('/') ?>" class="btn-back">
            <span class="glyphicon glyphicon-arrow-left" style="margin-right: 8px;"></span> Inicio
        </a>
    </div>

    <form method="get" action="<?= base_url('/servicios') ?>" class="card-elegant">
        <div class="row">
            <div class="col-md-3 mb-3 mb-md-0">
                <label class="filter-label">Estado de membresía</label>
                <select name="estado" class="form-control form-control-custom" onchange="this.form.submit()">
                    <option value="todas" <?= $estado == 'todas' ? 'selected' : '' ?>>🌐 Ver Todas</option>
                    <option value="activas" <?= $estado == 'activas' ? 'selected' : '' ?>>✅ Solo Activas</option>
                    <option value="inactivas" <?= $estado == 'inactivas' ? 'selected' : '' ?>>⛔ Inactivas / Vencidas</option>
                </select>
            </div>
            
            <div class="col-md-7 mb-3 mb-md-0">
                <label class="filter-label">Buscar Cliente</label>
                <div class="input-group" style="border-radius: 10px; background: #f8f9fa; padding: 4px;">
                    <input type="text" name="busqueda" class="form-control form-control-custom" style="background: transparent; border: none; box-shadow: none;" placeholder="Escribe nombre, apellido o teléfono..." value="<?= esc($busqueda) ?>">
                    <div class="input-group-btn">
                        <button class="btn-custom-primary" type="submit">
                            <span class="glyphicon glyphicon-search" style="font-size: 2rem; background: transparent; color: #000000; margin-right: 8px;"></span> Buscar
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2" style="display: flex; align-items: flex-end;">
                 <a href="<?= base_url('/servicios') ?>" class="btn-custom-outline w-100">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="card-elegant card-table-wrapper">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th class="pl-4">Cliente</th>
                        <th>Membresía</th>
                        <th class="text-center">Inicio</th>
                        <th class="text-center">Fin / Vencimiento</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($membresias)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div style="opacity: 0.5;">
                                    <span class="glyphicon glyphicon-folder-open" style="font-size: 3rem; margin-bottom: 15px; display: block;"></span>
                                    <h5>No se encontraron membresías con estos criterios.</h5>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($membresias as $m): ?>
                            <?php 
                                $esActivo = ($m['Estatus_idEstatus'] == 1);
                                $claseBadge = $esActivo ? 'badge-status-active' : 'badge-status-inactive';
                                $textoFecha = date('d/m/Y', strtotime($m['Fecha_Fin']));
                            ?>
                            <tr>
                                <td class="pl-4">
                                    <span class="user-title"><?= esc($m['Nombre']) . ' ' . esc($m['ApellidoP']) ?></span>
                                    <span class="user-subtitle">
                                        <span class="glyphicon glyphicon-earphone" style="font-size: 10px; margin-right: 4px;"></span> 
                                        <?= esc($m['Telefono'] ?? 'Sin Teléfono') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-pill badge-membresia"><?= esc($m['NombreMembresia']) ?></span>
                                </td>
                                <td class="text-center" style="color: #7e8299; font-weight: 500;">
                                    <?= date('d/m/Y', strtotime($m['Fecha_Inicio'])) ?>
                                </td>
                                <td class="text-center" style="font-weight: 600; color: #3f4254;">
                                    <?= $textoFecha ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge-pill <?= $claseBadge ?>">
                                        <span class="glyphicon <?= $esActivo ? 'glyphicon-ok-sign' : 'glyphicon-remove-sign' ?>" style="margin-right: 4px;"></span>
                                        <?= esc($m['EstadodeMembresia']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('renovacionesRegistro/' . $m['Clientes_IDClientes']) ?>" class="btn-action" title="Renovar">
                                        <span class="glyphicon glyphicon-refresh"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        <?= $pager->links() ?>
    </div>
</div>