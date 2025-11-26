<?php
include __DIR__ . '/../app/appPedidos.php';
include __DIR__ . '/layout/header.php';

// Helpers
function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function fmtFecha($s)
{
    if (empty($s)) return 'N/A';
    try {
        $dt = new DateTime($s);
        return $dt->format('d/m/Y H:i');
    } catch (Throwable $e) {
        return h($s);
    }
}

// Rol desde sesión
$rol = $_SESSION['rol'] ?? '';

// Para saber qué folio está en modo edición
$folioEditable = $_GET['folio_edit'] ?? '';
$editable = false;

$pedidoController = new PedidoController($conn);
$title = "DASHBOARD PEDIDOS";

// OJO: $unidad suele venir como array[0]
$unidad = $unidadOperacionalController->getIdUsuario($_SESSION['login_id'] ?? 0);
$uoRow  = $unidad[0] ?? [];

$ID_ALMACEN    = (int)($uoRow['ID_ALMACEN_UO'] ?? 0);
$ID_SUPERVISOR = (int)($uoRow['ID_SUPERVISOR_UO'] ?? 0);
$IDUN          = (int)($uoRow['IDUO'] ?? 0);

$abiertos = [];
$cerrados = [];

$articulos = $articuloController->getAllArticulos();

if ($rol === 'SUPERVISOR') {
    $pedidos = $pedidoController->getPedidosAbiertosBySupervisor($IDUN);
} else {
    $pedidos = $pedidoController->getPedidosAbiertosByAlmacen($IDUN);
}
/*
echo "<pre>";
print_r($unidad);
echo "</pre>";
exit;
*/

?>
<style>
    html {
        scroll-behavior: smooth;
    }

    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .dashboard-section {
        margin: 24px 0;
        grid-column: 1 / -1;
    }

    .contenedor-tickets {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(auto-fit, 480px);
        justify-content: center;
        align-items: start;
    }

    .tiket {
        width: 480px;
        display: block;
        background: #fff;
        border: 2px solid transparent;
        border-radius: 14px;
        padding: 10px;
        box-sizing: border-box;
        overflow-x: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
        transition: all .2s ease-in-out;
    }

    .tiket:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, .1)
    }

    .tiket h4 {
        margin: 0 0 8px;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a
    }

    .tiket p {
        margin: 4px 0;
        font-size: 14px;
        color: #334155
    }

    .acciones-botones {
        margin-top: 10px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap
    }

    .acciones-botones button {
        padding: 6px 12px;
        border: 1px solid #0ea5e9;
        background: #0ea5e9;
        color: #fff;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px
    }

    .acciones-botones button:hover {
        background: #0284c7
    }

    .estado-abierto {
        border: 2px solid #dc2626
    }

    .estado-en-proceso {
        border: 2px solid #ca8a04
    }

    .estado-cerrado,
    .estado-resuelto {
        border: 2px solid #16a34a
    }

    .kpis {
        display: flex;
        gap: 16px;
        justify-content: center;
        margin: 20px 0;
        flex-wrap: wrap
    }

    .kpi-card {
        flex: 1 1 200px;
        max-width: 280px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .05)
    }

    .kpi-card h3 {
        margin: 0 0 8px;
        font-size: 16px;
        font-weight: 600;
        color: #334155
    }

    .kpi-card .num {
        font-size: 28px;
        font-weight: 900;
        color: #0f172a
    }

    .kpi-card.total {
        border-top: 5px solid #3b82f6
    }

    .kpi-card.abiertos {
        border-top: 5px solid #dc2626
    }

    .kpi-card.cerrados {
        border-top: 5px solid #16a34a
    }

    .subtitulo {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 16px 0 6px;
        color: #e2e8f0
    }

    .subtitulo b {
        color: #fff;
        font-size: 18px
    }

    .chip-estado {
        height: 8px;
        width: 8px;
        border-radius: 999px;
        display: inline-block
    }

    .chip-abierto {
        background: #dc2626
    }

    .chip-proceso {
        background: #ca8a04
    }

    .btn-cancelar {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-cancelar:hover {
        background-color: #c0392b;
        transform: scale(1.05);
    }

    .detalle-grid {
        display: grid;
        grid-template-columns: 1fr;
        border-top: 1px solid #e2e8f0;
        margin-top: 10px;
        padding-top: 8px;
        font-size: 13px;
        color: #374151;
    }

    .detalle-header {
        display: grid;
        grid-template-columns: 0.6fr 1fr 1.4fr;
        font-weight: 700;
        margin-bottom: 4px;
        color: #111827;
    }

    .detalle-row {
        display: grid;
        grid-template-columns: 0.6fr 1fr 1.4fr;
        gap: 4px;
        padding: 2px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .detalle-row span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tiket .detalle-grid {
        background-color: #f9fafb;
        border-radius: 8px;
        padding: 6px 8px;
    }

    /* === Tabla de detalle editable === */
    .tickets-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .tickets-table thead th {
        padding: 4px 6px;
        text-align: left;
        font-weight: 600;
        color: #111827;
        border-bottom: 1px solid #e5e7eb;
    }

    .tickets-table tbody td {
        padding: 4px 6px;
        color: #111827;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    /* input de cantidad compacto y limpio */
    .tickets-table input[type="number"] {
        width: 40px;
        padding: 2px 4px;
        font-size: 0.8rem;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        text-align: right;
    }

    /* Botón cancelar item más pequeño en la tabla */
    .tickets-table .btn-cancelar {
        padding: 4px 8px;
        font-size: 0.75rem;
    }

    /* Columna NOMBRE: mismo ancho en encabezado y cuerpo */
    .tickets-table th:nth-child(2),
    .tickets-table td:nth-child(2) {
        width: 140px;
        max-width: 140px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .card-pedido {
        width: 100%;
        max-width: 480px;
    }

    .peso-total-pedido {
        font-size: 1.3rem;
        font-weight: 700;
        color: #0f172a;
        margin: 8px 0;
    }
</style>

<div class="container">
    <h1 class="mt-4 mb-4"><?php echo h($title); ?></h1>

    <div class="kpis">
        <div class="kpi-card total">
            <h3>Total de Pedidos Abiertos</h3>
            <div class="num"><?php echo is_array($pedidos) ? count($pedidos) : 0; ?></div>
        </div>
    </div>

    <div class="dashboard-section">
        <h2>Pedidos Abiertos</h2>
        <div class="subtitulo"><span class="chip-estado chip-abierto"></span><b>Abiertos</b></div>
        <div class="contenedor-tickets">

            <?php foreach ($pedidos as $pedido): ?>
                <?php
                // Este pedido está en modo edición si el folio coincide con el de GET
                $editable  = ($folioEditable === ($pedido['FOLIO'] ?? ''));
                $idFormAut = 'form_aut_' . ($pedido['IDPEDIDO'] ?? 0);
                $pesoPedidoBase = floatval($pedido['PESO_PEDIDO'] ?? 0);
                ?>
                <div class="card-pedido">
                    <!-- Form que recibirá TODAS las cantidades autorizadas de este pedido -->
                    <form id="<?php echo $idFormAut; ?>"
                        method="post"
                        <?php if ($rol === 'SUPERVISOR'): ?>
                        action="../app/appPedidos.php?action=autorizar_pedido"
                        <?php else: ?>
                        action="../app/appPedidos.php?action=surtir"
                        <?php endif; ?>>
                        <input type="hidden" name="idPedido" value="<?php echo h($pedido['IDPEDIDO'] ?? ''); ?>">
                        <input type="hidden" name="folio" value="<?php echo h($pedido['FOLIO'] ?? ''); ?>">
                        <input type="hidden" name="peso_pedido"
                            value="<?php echo number_format($pesoPedidoBase, 3, '.', ''); ?>"
                            class="input-peso-pedido">
                    </form>

                    <div class="tiket estado-abierto">
                        <h4>Folio: <?php echo h($pedido['FOLIO'] ?? ''); ?></h4>
                        <p><strong>Unidad Venta:</strong> <?php echo substr(h($pedido['UNIDAD_VENTA'] ?? 'N/A'), 0, 5); ?></p>
                        <p><strong>Fecha:</strong> <?php echo fmtFecha($pedido['FECHA'] ?? ''); ?></p>
                        <p><strong>Registros:</strong> <?php echo h($pedido['REGISTROS'] ?? '0'); ?></p>

                        <p class="peso-total-pedido">
                            <strong>
                                Tamaño de Pedido:
                                <span class="label-peso-pedido">
                                    <?php echo number_format($pesoPedidoBase, 3); ?>
                                </span>
                                KG
                            </strong>
                        </p>

                        <table class="tickets-table" style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="font-size:.85rem;">
                                    <th style="padding:4px;">Artículo</th>
                                    <th style="padding:4px;">Nombre</th>
                                    <th style="padding:4px;">Cantidad</th>
                                    <th style="padding:4px;">Peso Unitario</th>
                                    <th style="padding:4px;">Peso Total</th>
                                    <!--<th style="padding:4px;">Acción</th>-->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $detalle = $pedidoController->getDetallePedido($pedido['FOLIO'] ??'',$rol);
                                $folioAgregarCampo = $_GET['folio_agregar'] ?? '';
                                ?>
                                <?php foreach ($detalle as $item): ?>
                                    <?php
                                    $cantBase = $item['CANTIDAD_AUT'] ?? $item['CANTIDAD'] ?? 0;
                                    $pesoUnit = floatval($item['PESO_UNITARIO'] ?? 0);
                                    $pesoTotal = $pesoUnit * floatval($cantBase);
                                    ?>
                                    <tr style="font-size:.75rem;border-top:1px solid #e2e8f0;"
                                        data-peso-unit="<?php echo h($item['PESO_UNITARIO'] ?? '0'); ?>">
                                        <td style="padding:4px;"><?php echo h($item['ID_ARTICULO'] ?? 'N/A'); ?></td>
                                        <td style="padding:4px;"><?php echo h($item['NOMBRE_CORTO'] ?? 'N/A'); ?></td>

                                        <!-- ÚNICA columna de cantidad -->
                                        <td style="padding:4px;text-align:center;">
                                            <?php if ($editable): ?>
                                                <input
                                                    type="number"
                                                    name="cant_aut[<?php echo h($item['ID_ARTICULO'] ?? ''); ?>]"
                                                    form="<?php echo $idFormAut; ?>"
                                                    value="<?php echo h($cantBase); ?>"
                                                    min="0"
                                                    class="input-cant-aut"
                                                    style="width:60px; text-align:right; font-size:0.8rem;">
                                            <?php else: ?>
                                                <span><?php echo h($cantBase); ?></span>
                                                <input
                                                    type="hidden"
                                                    name="cant_aut[<?php echo h($item['ID_ARTICULO'] ?? ''); ?>]"
                                                    form="<?php echo $idFormAut; ?>"
                                                    value="<?php echo h($cantBase); ?>">
                                            <?php endif; ?>
                                        </td>

                                        <td style="padding:4px;text-align:right;">
                                            <?php echo h($item['PESO_UNITARIO'] ?? '0'); ?>
                                        </td>

                                        <!-- Peso total dinámico + hidden para mandarlo al app -->
                                        <td style="padding:4px;text-align:right;" class="cell-peso-total">
                                            <span><?php echo number_format($pesoTotal, 3); ?></span>
                                            <input
                                                type="hidden"
                                                name="peso_total[<?php echo h($item['ID_ARTICULO'] ?? ''); ?>]"
                                                form="<?php echo $idFormAut; ?>"
                                                value="<?php echo number_format($pesoTotal, 3, '.', ''); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>

                        <div class="acciones-botones">
                            <?php if ($rol === 'SUPERVISOR'): ?>

                                <?php if ($editable): ?>
                                    <button type="submit" form="<?php echo $idFormAut; ?>">
                                        AUTORIZAR PEDIDO
                                    </button>
                                    <button type="button"
                                        onclick="location.href='dashboardPedidos.php'">
                                        CANCELAR EDICIÓN
                                    </button>
                                <?php else: ?>
                                    <button type="submit" form="<?php echo $idFormAut; ?>">
                                        AUTORIZAR PEDIDO
                                    </button>
                                    <button type="button"
                                        onclick="location.href='dashboardPedidos.php?folio_edit=<?php echo urlencode($pedido['FOLIO'] ?? ''); ?>'">
                                        EDITAR PEDIDO
                                    </button>
                                <?php endif; ?>

                            <?php else: ?>
                                <!-- ALMACÉN (u otros roles que solo surten) -->
                                <button type="button"
                                    onclick="surtirPedidoForm('<?php echo $idFormAut; ?>')">
                                    SURTIR PEDIDO
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

<script>
    function surtirPedidoForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return;
        const oldTarget = form.target;
        form.target = '_blank';
        form.submit();

        // Restaurar el target original
        form.target = oldTarget;

        // Recargar el dashboard después de un momento
        setTimeout(() => {
            location.reload();
        }, 1500);
    }

    // Recalcular peso total de renglón + tamaño de pedido
    document.querySelectorAll('.tickets-table .input-cant-aut').forEach(function(input) {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const card = this.closest('.card-pedido');
            const pesoUnit = parseFloat(row.dataset.pesoUnit || '0');
            const cant = parseFloat(this.value || '0');

            // 1) Peso total del renglón
            const totalRow = pesoUnit * cant;
            const cell = row.querySelector('.cell-peso-total');
            const spanRow = cell.querySelector('span');
            const hiddenRow = cell.querySelector('input[type="hidden"]');

            if (!isNaN(totalRow)) {
                spanRow.textContent = totalRow.toFixed(3);
                hiddenRow.value = totalRow.toFixed(3);
            } else {
                spanRow.textContent = '0.000';
                hiddenRow.value = '0.000';
            }

            // 2) Recalcular tamaño de pedido (suma de todas las filas)
            let sum = 0;
            card.querySelectorAll('.cell-peso-total input[type="hidden"]').forEach(function(h) {
                const v = parseFloat(h.value || '0');
                if (!isNaN(v)) sum += v;
            });

            const labelTotal = card.querySelector('.label-peso-pedido');
            const hiddenTotal = card.querySelector('.input-peso-pedido');

            if (labelTotal) {
                labelTotal.textContent = sum.toFixed(3);
            }
            if (hiddenTotal) {
                hiddenTotal.value = sum.toFixed(3);
            }
        });
    });
</script>