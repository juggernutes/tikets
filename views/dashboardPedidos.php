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

$pedidoController = new PedidoController($conn);
$title = "Dashboard de Pedidos";

// OJO: $unidad suele venir como array[0]
$unidad = $unidadOperacionalController->getIdUsuario($_SESSION['login_id'] ?? 0);
$uoRow  = $unidad[0] ?? [];

// Si vas a filtrar por almacén, usa el ID de almacén de la UO
$ID_ALMACEN = (int)($uoRow['ID_ALMACEN_UO'] ?? 0);

// Si tu método espera ALMACÉN, pásale $ID_ALMACEN
$pedidos = $pedidoController->getPedidosAbiertosByAlmacen($ID_ALMACEN);

// Debug opcional seguro:
//echo '<pre>';
//print_r($uoRow);
//print_r($pedidos);
//echo '</pre>';

$abiertos = [];
$cerrados = [];
?>
<style>
    html {
        scroll-behavior: smooth;
    }

    /* ==== OVERRIDES DESKTOP ==== */
    .container {
        /* centrado y ancho amplio para monitor */
        max-width: 1280px;
        /* ajusta a 1440px si quieres más */
        margin: 0 auto;
        padding: 0 24px;
    }

    /* Asegurar que esta sección no quede en una columna lateral */
    .dashboard-section {
        margin: 24px 0;
        grid-column: 1 / -1;
        /* por si el layout padre usa grid */
    }

    /* Grid de tarjetas: llenar ancho, centradas */
    .contenedor-tickets {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        justify-content: center;
        /* evita que se pegue a la derecha */
        align-items: start;
    }

    /* Opcional: limitar el ancho máximo de cada tarjeta para que no se estiren de más */
    .tiket {
        max-width: 520px;
        /* quítalo si prefieres que ocupen todo el 1fr */
    }

    /* Si hay estilos globales que empujan a la derecha, los neutralizamos */
    .contenedor-tickets,
    .contenedor-tickets-cerrados {
        justify-items: stretch;
        align-content: start;
    }

    /* Media queries por si quieres más columnas en ultrawide */
    @media (min-width: 1600px) {
        .contenedor-tickets {
            grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
        }
    }


    .tiket {
        display: block;
        background: #fff;
        border: 2px solid transparent;
        border-radius: 14px;
        padding: 16px;
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

    /* Borde destacado según estado */
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

    /* KPIs */
    .kpis {
        display: flex;
        gap: 16px;
        justify-content: center;
        margin: 20px 0;
        flex-wrap: wrap
    }

    .kpi-card {
        flex: 1 1 200px;
        max-width: 250px;
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

    /* Subtítulos dentro de la sección de abiertos */
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
        /* rojo elegante */
        color: white;
        /* texto blanco */
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
        /* rojo más oscuro al pasar mouse */
        transform: scale(1.05);
        /* pequeño zoom */
    }

    /* ==== Detalle del pedido en columnas ==== */
    .detalle-grid {
        display: grid;
        grid-template-columns: 1fr;
        /* una sola columna contenedora */
        border-top: 1px solid #e2e8f0;
        margin-top: 10px;
        padding-top: 8px;
        font-size: 13px;
        color: #374151;
    }

    /* encabezado */
    .detalle-header {
        display: grid;
        grid-template-columns: 0.6fr 1fr 1.4fr;
        /* ajusta según el largo de tus textos */
        font-weight: 700;
        margin-bottom: 4px;
        color: #111827;
    }

    /* cada fila de detalle */
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

    /* mejora visual dentro de la tarjeta */
    .tiket .detalle-grid {
        background-color: #f9fafb;
        border-radius: 8px;
        padding: 6px 8px;
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
                <div class="tiket estado-abierto">
                    <h4>Folio: <?php echo h($pedido['FOLIO'] ?? ''); ?></h4>
                    <p><strong>Unidad Venta:</strong> <?php echo h($pedido['UNIDAD_VENTA'] ?? 'N/A'); ?></p>
                    <p><strong>Fecha:</strong> <?php echo fmtFecha($pedido['FECHA'] ?? ''); ?></p>
                    <p><strong>Registros:</strong> <?php echo h($pedido['REGISTROS'] ?? '0'); ?></p>
                    <div class="detalle-grid"><?php
                                                // Obtener detalle del pedido
                                                $detalle = $pedidoController->getDetallePedido($pedido['FOLIO'] ?? '');
                                                ?>
                        <div class="detalle-header">
                            <span><strong>CANTIDAD</strong></span>
                            <span><strong>ID ARTÍCULO</strong></span>
                            <span><strong>NOMBRE CORTO</strong></span>
                        </div>

                        <?php foreach ($detalle as $item): ?>
                            <div class="detalle-row">
                                <span><?php echo h($item['CANTIDAD'] ?? '0'); ?></span>
                                <span><?php echo h($item['ID_ARTICULO'] ?? 'N/A'); ?></span>
                                <span><?php echo h($item['NOMBRE_CORTO'] ?? 'N/A'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>


                    <div class="acciones-botones">
                        <button onclick="surtirPedido('<?php echo h($pedido['FOLIO'] ?? ''); ?>')">
                            SURTIR PEDIDO
                        </button>
                        <a class="btn" href="../app/appPedidos.php?action=csv&folio=<?php echo urlencode($pedido['FOLIO'] ?? ''); ?>">
                            Descargar CSV
                        </a>
                    </div>

                </div>

            <?php endforeach; ?>
        </div>

    </div>
</div>
<script>
    async function surtirPedido(folio) {
        if (!folio) {
            alert('Folio inválido');
            return;
        }
        if (!confirm('¿Marcar como SURTIDO el pedido ' + folio + '?')) return;

        try {
            const r = await fetch('../app/appPedidos.php?action=surtir', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    folio
                })
            });
            const out = await r.json();
            if (!r.ok || !out?.ok) throw new Error(out?.error || 'No se pudo surtir');

            alert('✅ Pedido surtido: ' + folio);
            location.reload();
        } catch (err) {
            alert('❌ ' + err.message);
        }
    }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>