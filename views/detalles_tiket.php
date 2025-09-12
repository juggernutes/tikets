<?php
// detalles_tiket.php (vista)
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../app/appTiket.php';
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$IdTiket = $_GET['ID_Tiket'] ?? null;
if (!$IdTiket) {
    http_response_code(400);
    echo "ID de ticket no válido.";
    exit;
}

// Datos
$ticket   = $tiketController->getTicketById($IdTiket);
if (!$ticket) {
    http_response_code(404);
    echo "No se encontró el ticket solicitado.";
    exit;
}
$encuesta = $encuestaController->getEncuestaByIdTiket($IdTiket);

// Helpers
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmtDate($s){
    if (!$s) return '—';
    $t = strtotime($s);
    return $t ? date('d/M/Y H:i', $t) : e($s);
}
function renderStars($calificacion){
    $calificacion = (int)$calificacion;
    $out = '<div role="img" aria-label="Calificación: ' . $calificacion . ' de 5" style="line-height:1;">';
    for ($i=1; $i<=5; $i++){
        $filled = $i <= $calificacion;
        $out .= $filled
            ? '<span style="color:#f59e0b; font-size:28px;">&#9733;</span>'
            : '<span style="color:#cbd5e1; font-size:28px;">&#9733;</span>';
    }
    return $out . '</div>';
}

$descripcion         = wordwrap($ticket['DESCRIPCION'] ?? '',           90, "\n", true);
$descripcionSolucion = wordwrap($ticket['DESCRIPCION_SOLUCION'] ?? '',  90, "\n", true);
$comentarios         = wordwrap($encuesta['COMENTARIOS'] ?? '',         90, "\n", true);

$title = "DETALLES DEL TICKET";
include __DIR__ . '/layout/header.php';
?>
<style>
  .ticket-card{max-width:1100px;margin:18px auto;padding:0 14px}
  .card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,.05)}
  .card-header{display:flex;justify-content:space-between;align-items:center;padding:16px;border-bottom:1px solid #f1f5f9}
  .card-title{margin:0;font-size:20px;font-weight:800;color:#0f172a}
  .card-body{padding:16px}
  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px}
  .item{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px}
  .label{display:block;font-size:12px;color:#64748b;margin-bottom:4px}
  .value{font-weight:700;color:#0f172a;white-space:pre-wrap;word-break:break-word}
  .desc{grid-column:1/-1}
  .actions{display:flex;gap:10px;flex-wrap:wrap;padding:12px 16px;border-top:1px solid #f1f5f9}
  .btnx{padding:9px 14px;border:1px solid #e2e8f0;background:#fff;border-radius:12px;font-weight:700;color:#0f172a;text-decoration:none}
  .btnx.primary{background:#0ea5e9;color:#fff;border-color:#0ea5e9}
</style>

<div class="ticket-card">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Ticket <?= e($ticket['Folio'] ?? '') ?></h2>
      <div>
        <?php
          if ($encuesta && isset($encuesta['CALIFICACION'])) {
            echo renderStars($encuesta['CALIFICACION']);
          } else {
            echo '<span style="color:#64748b;font-weight:700">Sin calificación</span>';
          }
        ?>
      </div>
    </div>

    <div class="card-body">
      <div class="grid">
        <div class="item desc">
          <span class="label">Descripción</span>
          <div class="value"><?= nl2br(e($descripcion)) ?></div>
        </div>

        <div class="item">
          <span class="label">Estado</span>
          <div class="value"><?= e($ticket['ESTADO'] ?? '—') ?></div>
        </div>

        <div class="item">
          <span class="label">Fecha de creación</span>
          <div class="value"><?= fmtDate($ticket['FECHA'] ?? '') ?></div>
        </div>

        <div class="item">
          <span class="label">Fecha de resolución</span>
          <div class="value">
            <?= $ticket['FECHA_SOLUCION'] ? fmtDate($ticket['FECHA_SOLUCION']) : 'No resuelto' ?>
          </div>
        </div>

        <div class="item">
          <span class="label">Empleado</span>
          <div class="value"><?= e($ticket['EMPLEADO'] ?? '—') ?></div>
        </div>

        <div class="item">
          <span class="label">Puesto</span>
          <div class="value"><?= e($ticket['PUESTO'] ?? '—') ?></div>
        </div>

        <div class="item">
          <span class="label">Sucursal</span>
          <div class="value"><?= e($ticket['SUCURSAL'] ?? '—') ?></div>
        </div>

        <div class="item">
          <span class="label">Soporte</span>
          <div class="value"><?= e($ticket['NOMBRE_SOPORTE'] ?? 'No asignado') ?></div>
        </div>

        <div class="item">
          <span class="label">Error</span>
          <div class="value"><?= e($ticket['ERROR'] ?? 'No especificado') ?></div>
        </div>

        <div class="item">
          <span class="label">Solución</span>
          <div class="value"><?= e($ticket['SOLUCION'] ?? 'No especificada') ?></div>
        </div>

        <div class="item desc">
          <span class="label">Descripción de la solución</span>
          <div class="value"><?= nl2br(e($descripcionSolucion)) ?></div>
        </div>

        <div class="item desc">
          <span class="label">Comentarios de la encuesta</span>
          <div class="value">
            <?php
              if ($encuesta && !empty($encuesta['COMENTARIOS'])) {
                echo nl2br(e($comentarios));
              } else {
                echo 'No hay comentarios de encuesta disponibles';
              }
            ?>
          </div>
        </div>
      </div>
    </div>

    <div class="actions">
      <a href="../views/dashboard.php" class="btnx">Volver al Dashboard</a>
      <a href="../views/cerrado_tiket.php" class="btnx">Volver a Tickets Cerrados</a>
      <?php if (($ticket['ESTADO'] ?? '') !== 'CERRADO'): ?>
        <a href="../views/resolver_ticket.php?id=<?= urlencode((string)($ticket['ID_Tiket'] ?? '')) ?>" class="btnx primary">Resolver</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
