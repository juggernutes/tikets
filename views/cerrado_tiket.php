<?php
session_start();
require_once __DIR__ . '/../app/appTiket.php';

if (!isset($_SESSION['login_id'])) {
  header("Location: ../public/index.php");
  exit;
}

$IdUser = $_SESSION['login_id'];
$rol    = $_SESSION['rol'] ?? 'INVITADO';

/* ===== Helpers ===== */
function e($v)
{
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function dash($v)
{
  return ($v === null || $v === '') ? '—' : e($v);
}
function fmt($s)
{
  if (!$s) return '—';
  $t = strtotime($s);
  return $t ? date('d/M/Y H:i', $t) : e($s);
}
function seconds_to_readable($secs)
{
  $secs = (int)$secs;
  $d = intdiv($secs, 86400);
  $secs %= 86400;
  $h = intdiv($secs, 3600);
  $secs %= 3600;
  $m = intdiv($secs, 60);
  $s = $secs % 60;
  $parts = [];
  if ($d) $parts[] = $d . 'd';
  if ($h || $d) $parts[] = $h . 'h';
  if ($m || $h || $d) $parts[] = $m . 'm';
  $parts[] = $s . 's';
  return implode(' ', $parts);
}

/* ===== Horario hábil solo para los tiempos por ticket ===== */
const WORK_START_HOUR = 7;
const WORK_END_HOUR   = 18;
const WORK_DAYS       = [1, 2, 3, 4, 5];

$FERIADOS = [];

function business_seconds_between($ini, $fin, array $feriados)
{
  if (!$ini || !$fin) return null;
  $start = strtotime($ini);
  $end   = strtotime($fin);
  if (!$start || !$end) return null;
  if ($end <= $start) return 0;

  $tz = new DateTimeZone(date_default_timezone_get());
  $cur   = (new DateTime('@' . $start))->setTimezone($tz);
  $endDT = (new DateTime('@' . $end))->setTimezone($tz);

  $total = 0;
  while ($cur < $endDT) {
    $y = $cur->format('Y');
    $m = $cur->format('m');
    $d = $cur->format('d');

    $dayStart = (new DateTime("$y-$m-$d " . WORK_START_HOUR . ":00:00", $tz));
    $dayEnd   = (new DateTime("$y-$m-$d " . WORK_END_HOUR   . ":00:00", $tz));

    $dow       = (int)$cur->format('N');
    $isHoliday = in_array($cur->format('Y-m-d'), $feriados, true);
    $isWorkday = in_array($dow, WORK_DAYS, true) && !$isHoliday;

    $segStart = max($cur->getTimestamp(), $dayStart->getTimestamp());
    $segEnd   = min($endDT->getTimestamp(), $dayEnd->getTimestamp());

    if ($isWorkday && $segEnd > $segStart) {
      $total += ($segEnd - $segStart);
    }

    $cur = (clone $dayStart)->modify('+1 day');
  }
  return max(0, $total);
}

/* ===== Filtros GET ===== */
$q      = trim($_GET['q']      ?? '');
$desde  = trim($_GET['desde']  ?? '');
$hasta  = trim($_GET['hasta']  ?? '');
$per    = max(5, min(100, (int)($_GET['per'] ?? 25)));
$page   = max(1, (int)($_GET['page'] ?? 1));

$title = 'TICKETS CERRADOS';
include __DIR__ . '/layout/header.php';

/* ===== Datos base ===== */
$ticketsResult = $tiketController->getTicketsCerrados($IdUser, $rol);

$rows = [];
if ($ticketsResult && $ticketsResult->num_rows > 0) {
  while ($r = $ticketsResult->fetch_assoc()) {
    $rows[] = $r;
  }
}

/* ===== Filtrado para historial ===== */
$rows = array_values(array_filter($rows, function ($r) use ($q, $desde, $hasta) {
  if ($q !== '') {
    $needle = mb_strtolower($q, 'UTF-8');
    $hay = mb_strtolower(implode(' ', [
      $r['Folio']          ?? '',
      $r['SISTEMA']        ?? '',
      $r['DESCRIPCION']    ?? '',
      $r['EMPLEADO']       ?? '',
      $r['PUESTO']         ?? '',
      $r['SUCURSAL']       ?? '',
      $r['NOMBRE_SOPORTE'] ?? '',
      $r['ERROR']          ?? '',
      $r['SOLUCION']       ?? ''
    ]), 'UTF-8');
    if (mb_strpos($hay, $needle) === false) return false;
  }

  $fecha = $r['FECHA_SOLUCION'] ?? ($r['FECHA'] ?? '');
  if ($fecha && ($desde || $hasta)) {
    $ts = strtotime($fecha);
    if ($desde && $ts < strtotime($desde . ' 00:00:00')) return false;
    if ($hasta && $ts > strtotime($hasta . ' 23:59:59')) return false;
  }
  return true;
}));

/* ===== Orden y paginación ===== */
usort($rows, function ($a, $b) {
  $fa = strtotime($a['FECHA_SOLUCION'] ?? $a['FECHA'] ?? '1970-01-01');
  $fb = strtotime($b['FECHA_SOLUCION'] ?? $b['FECHA'] ?? '1970-01-01');
  return $fb <=> $fa;
});
$total_rows  = count($rows);
$total_pages = max(1, (int)ceil($total_rows / $per));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per;
$view        = array_slice($rows, $offset, $per);

/* ===== CSS ===== */
$cssPath = __DIR__ . '/../tools/ticketCerradosStyle.css';
$cssVer  = is_file($cssPath) ? filemtime($cssPath) : time();
?>
<link rel="stylesheet" href="../tools/ticketCerradosStyle.css?v=<?= $cssVer ?>">

<div class="container">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Historial de tickets cerrados</h2>
      <div class="tools-right">
        <button class="copy" type="button" onclick="copiarURL()">Copiar URL</button>
        <button class="export" type="button" onclick="exportCSV()">Exportar CSV</button>
      </div>
    </div>

    <form class="toolbar" method="get">
      <div class="field">
        <label for="q">Buscar</label>
        <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="folio, sistema, empleado, soporte…">
      </div>
      <div class="field">
        <label for="desde">Desde</label>
        <input type="date" id="desde" name="desde" value="<?= e($desde) ?>">
      </div>
      <div class="field">
        <label for="hasta">Hasta</label>
        <input type="date" id="hasta" name="hasta" value="<?= e($hasta) ?>">
      </div>
      <div class="field">
        <label for="per">Por página</label>
        <select id="per" name="per">
          <?php foreach ([10, 25, 50, 100, 500] as $n): ?>
            <option value="<?= $n ?>" <?= $per == $n ? 'selected' : '' ?>><?= $n ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <button type="submit">Aplicar</button>
      </div>
      <a class="reset" href="?">Limpiar</a>
    </form>

    <div class="table-wrap">
      <table class="tickets-table">
        <thead>
          <tr>
            <th>Folio</th>
            <th>Estado</th>
            <th>Creación</th>
            <th>Asignación</th>
            <th>Resolución</th>
            <th>Sistema</th>
            <th>Descripción</th>
            <th>Empleado</th>
            <th>Puesto</th>
            <th>Sucursal</th>
            <th>Soporte</th>
            <th>Error</th>
            <th>Solución</th>
            <th>Descripción solución</th>
            <th>Tiempo de asignación (hábil)</th>
            <th>Tiempo de solución (hábil)</th>
            <th>Calificación</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($view)): ?>
            <tr>
              <td class="empty" colspan="17">No hay tickets con los filtros actuales.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($view as $row): ?>
              <tr class="ticket-row" data-tiket="<?= e($row['ID_Tiket'] ?? '') ?>" style="cursor:pointer">
                <td class="mono" data-label="Folio"><?= dash($row['Folio'] ?? '') ?></td>
                <td data-label="Estado"><?= dash($row['ESTADO'] ?? '') ?></td>
                <td data-label="Creación"><?= fmt($row['FECHA'] ?? '') ?></td>
                <td data-label="Asignación"><?= fmt($row['FECHA_ASIGNACION'] ?? '') ?></td>
                <td data-label="Resolución"><?= fmt($row['FECHA_SOLUCION'] ?? '') ?></td>
                <td data-label="Sistema"><?= dash($row['SISTEMA'] ?? '') ?></td>
                <td data-label="Descripción"><?= dash($row['DESCRIPCION'] ?? '') ?></td>
                <td data-label="Empleado"><?= dash($row['EMPLEADO'] ?? '') ?></td>
                <td data-label="Puesto"><?= dash($row['PUESTO'] ?? '') ?></td>
                <td data-label="Sucursal"><?= dash($row['SUCURSAL'] ?? '') ?></td>
                <td data-label="Soporte"><?= dash($row['NOMBRE_SOPORTE'] ?? '') ?></td>
                <td data-label="Error"><?= dash($row['ERROR'] ?? '') ?></td>
                <td data-label="Solución"><?= dash($row['SOLUCION'] ?? '') ?></td>
                <td data-label="Descripción solución"><?= dash($row['DESCRIPCION_SOLUCION'] ?? '') ?></td>
                <td data-label="Tiempo de asignación">
                  <?php
                  $secsA = business_seconds_between($row['FECHA'] ?? '', $row['FECHA_ASIGNACION'] ?? '', $FERIADOS);
                  echo ($secsA === null) ? '—' : seconds_to_readable($secsA);
                  ?>
                </td>
                <td data-label="Tiempo de solución">
                  <?php
                  $secsR = business_seconds_between($row['FECHA'] ?? '', $row['FECHA_SOLUCION'] ?? '', $FERIADOS);
                  echo ($secsR === null) ? '—' : seconds_to_readable($secsR);
                  ?>
                </td>
                <td data-label="Calificación"><?= dash($row['CALIFICACION'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="card">
      <form class="toolbar" action="../app/appTiket.php" method="get">

        <!-- Acción -->
        <input type="hidden" name="accion" value="reporteProveedor">

        <div class="field">
          <label for="ID_PROVEEDOR">Proveedor</label>
          <select name="ID_PROVEEDOR" id="ID_PROVEEDOR" required>
            <option value="">Selecciona proveedor</option>
            <?php include __DIR__ . '/../partials/combo_proveedor.php'; ?>
          </select>
        </div>

        <div class="field">
          <label for="fecha">Mes</label>
          <input type="month" name="fecha" id="fecha" required>
        </div>

        <div class="field">
          <button type="submit">Reporte</button>
        </div>

      </form>
    </div>



    <div class="pager">
      <?php if ($total_pages > 1):
        $qs = $_GET;
        unset($qs['page']);
        $base = '?' . http_build_query($qs);
        $pmin = max(1, $page - 2);
        $pmax = min($total_pages, $page + 2);
      ?>
        <a href="<?= $base . ($base === '?' ? '' : '&') ?>page=1">«</a>
        <a href="<?= $base . ($base === '?' ? '' : '&') ?>page=<?= max(1, $page - 1) ?>">‹</a>
        <?php for ($p = $pmin; $p <= $pmax; $p++): ?>
          <?php if ($p == $page): ?>
            <span class="active"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= $base . ($base === '?' ? '' : '&') ?>page=<?= $p ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <a href="<?= $base . ($base === '?' ? '' : '&') ?>page=<?= min($total_pages, $page + 1) ?>">›</a>
        <a href="<?= $base . ($base === '?' ? '' : '&') ?>page=<?= $total_pages ?>">»</a>
      <?php else: ?>
        <span class="muted">Total: <?= $total_rows ?> registros</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ticket-row').forEach(function(row) {
      row.addEventListener('click', function() {
        const id = this.getAttribute('data-tiket');
        if (id) {
          window.location.href = 'detalles_tiket.php?ID_Tiket=' + encodeURIComponent(id);
        }
      });
    });
  });

  function copiarURL() {
    navigator.clipboard.writeText(window.location.href);
  }

  function exportCSV() {
    const table = document.querySelector('.tickets-table');
    if (!table) return;

    let csv = [];
    csv.push('\ufeff'); // BOM

    table.querySelectorAll('tr').forEach(tr => {
      let row = [];
      tr.querySelectorAll('th,td').forEach(cell => {
        let text = cell.innerText.replaceAll('\n', ' ').replaceAll('"', '""').trim();
        row.push('"' + text + '"');
      });
      csv.push(row.join(','));
    });

    const blob = new Blob([csv.join('\n')], {
      type: 'text/csv;charset=utf-8;'
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'tickets_cerrados.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>