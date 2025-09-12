<?php
session_start();
require_once __DIR__ . '/../app/appTiket.php';
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$IdUser = $_SESSION['login_id'];
$rol    = $_SESSION['rol'] ?? 'INVITADO';

// ====== Filtros simples por GET ======
$q      = trim($_GET['q']      ?? '');
$desde  = trim($_GET['desde']  ?? ''); // yyyy-mm-dd
$hasta  = trim($_GET['hasta']  ?? ''); // yyyy-mm-dd
$per    = max(5, min(100, (int)($_GET['per'] ?? 25))); // 25 por defecto
$page   = max(1, (int)($_GET['page'] ?? 1));

$title = 'TICKETS CERRADOS';
include __DIR__ . '/layout/header.php';

// ====== Datos base ======
$ticketsResult = $tiketController->getTicketsCerrados($IdUser, $rol);

// Pasar a array para filtrar en PHP si el controlador aún no soporta filtros
$rows = [];
if ($ticketsResult && $ticketsResult->num_rows > 0) {
    while ($r = $ticketsResult->fetch_assoc()) { $rows[] = $r; }
}

// ====== Filtrado en memoria ======
$rows = array_values(array_filter($rows, function($r) use ($q, $desde, $hasta){
    // Buscar texto libre en varios campos
    if ($q !== '') {
        $needle = mb_strtolower($q, 'UTF-8');
        $hay = mb_strtolower(implode(' ', [
            $r['Folio'] ?? '', $r['SISTEMA'] ?? '', $r['DESCRIPCION'] ?? '', $r['EMPLEADO'] ?? '',
            $r['PUESTO'] ?? '', $r['SUCURSAL'] ?? '', $r['NOMBRE_SOPORTE'] ?? '', $r['ERROR'] ?? '', $r['SOLUCION'] ?? ''
        ]), 'UTF-8');
        if (mb_strpos($hay, $needle) === false) return false;
    }
    // Rango de fechas por FECHA_SOLUCION si existe, si no por FECHA
    $fecha = $r['FECHA_SOLUCION'] ?? ($r['FECHA'] ?? '');
    if ($fecha && ($desde || $hasta)) {
        $ts = strtotime($fecha);
        if ($desde) { if ($ts < strtotime($desde.' 00:00:00')) return false; }
        if ($hasta) { if ($ts > strtotime($hasta.' 23:59:59')) return false; }
    }
    return true;
}));

// ====== Ordenar descendente por FECHA_SOLUCION (si existe) ======
usort($rows, function($a,$b){
    $fa = strtotime($a['FECHA_SOLUCION'] ?? $a['FECHA'] ?? '1970-01-01');
    $fb = strtotime($b['FECHA_SOLUCION'] ?? $b['FECHA'] ?? '1970-01-01');
    return $fb <=> $fa; // descendente
});

// ====== Paginación ======
$total_rows  = count($rows);
$total_pages = max(1, (int)ceil($total_rows / $per));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per;
$view        = array_slice($rows, $offset, $per);

// ====== Helpers ======
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function dash($v){ return ($v === null || $v === '') ? '—' : e($v); }
function fmt($s){ if(!$s) return '—'; $t=strtotime($s); return $t? date('d/M/Y H:i', $t) : e($s); }

?>

<style>
  .container{
    width: 100%;
    max-width:none;
    margin:0;
    padding:0 14px}
  h1,h2{color:#0f172a}
  .toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin:10px 0 16px}
  .toolbar .field{display:flex;flex-direction:column;gap:6px}
  .toolbar input,.toolbar select{padding:8px 10px;border:1px solid #e2e8f0;border-radius:10px;font-size:14px}
  .toolbar button{padding:9px 14px;border:1px solid #0ea5e9;background:#0ea5e9;color:#fff;border-radius:12px;font-weight:600;cursor:pointer}
  .toolbar a.reset{padding:9px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:12px;text-decoration:none;color:#334155;font-weight:600}

  .card{background:#fff;border:1px solid #e9e9e9;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,.05)}
  .card-header{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #f1f5f9}
  .card-title{margin:0;font-size:18px;font-weight:800;letter-spacing:.2px;color:#0f172a}
  .table-wrap{overflow:auto;border-radius:0 0 16px 16px}
  table{border-collapse:separate;border-spacing:0;width:100%;font-size:14px}
  thead th{position:sticky;top:0;background:#f8fafc;color:#334155;text-align:left;font-weight:700;padding:12px;border-bottom:1px solid #e2e8f0}
  tbody td{padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#0f172a;vertical-align:top}
  tbody tr:hover{background:#f8fafc}
  .mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
  .empty{padding:30px;text-align:center;color:#64748b}

  .pager{display:flex;gap:8px;justify-content:flex-end;align-items:center;padding:12px 16px}
  .pager a,.pager span{padding:8px 12px;border:1px solid #e2e8f0;border-radius:10px;text-decoration:none;color:#334155}
  .pager .active{background:#0ea5e9;color:#fff;border-color:#0ea5e9}

  .tools-right{display:flex;gap:8px;align-items:center}
  .copy,.export{padding:6px 10px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;cursor:pointer;font-weight:600}
  .tickets-table td:nth-child(5),
  .tickets-table th:nth-child(5) {
  min-width: 300px;   /* fuerza un ancho mínimo más grande */
  max-width: 450px;   /* evita que se extienda demasiado */
  white-space: normal; /* permite saltos de línea */
  word-wrap: break-word;
  }

</style>

<div class="container">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Historial de tickets cerrados</h2>
      <div class="tools-right">
        <button class="copy" onclick="copiarURL()">Copiar URL</button>
        <button class="export" onclick="exportCSV()">Exportar CSV</button>
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
          <?php foreach([10,25,50,100] as $n): ?>
            <option value="<?= $n ?>" <?= $per==$n?'selected':'' ?>><?= $n ?></option>
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
            <th>Creación</th>
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
          </tr>
        </thead>
        <tbody>
          <?php if(empty($view)): ?>
            <tr><td class="empty" colspan="12">No hay tickets con los filtros actuales.</td></tr>
          <?php else: ?>
            <?php foreach ($view as $row): ?>
              <tr class="ticket-row" data-tiket="<?= e($row['ID_Tiket'] ?? '') ?>" style="cursor:pointer">
                <td class="mono"><?= dash($row['Folio'] ?? '') ?></td>
                <td><?= fmt($row['FECHA'] ?? '') ?></td>
                <td><?= fmt($row['FECHA_SOLUCION'] ?? '') ?></td>
                <td><?= dash($row['SISTEMA'] ?? '') ?></td>
                <td><?= dash($row['DESCRIPCION'] ?? '') ?></td>
                <td><?= dash($row['EMPLEADO'] ?? '') ?></td>
                <td><?= dash($row['PUESTO'] ?? '') ?></td>
                <td><?= dash($row['SUCURSAL'] ?? '') ?></td>
                <td><?= dash($row['NOMBRE_SOPORTE'] ?? '') ?></td>
                <td><?= dash($row['ERROR'] ?? '') ?></td>
                <td><?= dash($row['SOLUCION'] ?? '') ?></td>
                <td><?= dash($row['DESCRIPCION_SOLUCION'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="pager">
      <?php if($total_pages > 1):
        $qs = $_GET; unset($qs['page']); $base = '?' . http_build_query($qs);
        $pmin = max(1, $page-2); $pmax = min($total_pages, $page+2);
      ?>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=1">«</a>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= max(1,$page-1) ?>">‹</a>
        <?php for($p=$pmin;$p<=$pmax;$p++): ?>
          <?php if($p == $page): ?>
            <span class="active"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= $p ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= min($total_pages,$page+1) ?>">›</a>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= $total_pages ?>">»</a>
      <?php else: ?>
        <span class="muted">Total: <?= $total_rows ?> registros</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.ticket-row').forEach(function(row){
      row.addEventListener('click', function(){
        const id = this.getAttribute('data-tiket');
        if(id){ window.location.href = 'detalles_tiket.php?ID_Tiket=' + encodeURIComponent(id); }
      });
    });
  });
  function copiarURL(){ navigator.clipboard.writeText(window.location.href); }
  function exportCSV(){
    const table = document.querySelector('.tickets-table');
    if(!table) return;
    let csv = [];
    table.querySelectorAll('tr').forEach(tr=>{
      let row=[];
      tr.querySelectorAll('th,td').forEach(cell=>{
        let text = cell.innerText.replaceAll('\n',' ').replaceAll('"','""');
        row.push('"'+text+'"');
      });
      csv.push(row.join(','));
    });
    const blob = new Blob([csv.join('\n')], {type:'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'tickets_cerrados.csv'; a.click();
    URL.revokeObjectURL(url);
  }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>