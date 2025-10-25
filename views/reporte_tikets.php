<?php 
// reporte_tickets.php (vista)
// Requisitos previos desde el controlador:
// - $tickets: mysqli_result con columnas: ID_Tiket, FechaReporte, Empleado, Soporte, Sistema, TipoError, ID_Solucion, Estatus
// - (Opcional) $page, $total_pages, $per_page, $total_rows para paginación
// - (Opcional) el controlador ya aplica filtros según $_GET (status, q, desde, hasta)

include __DIR__ . '/../app/App.php';
$title = 'Reporte de Tickets';
include __DIR__ . '/layout/header.php';

// Helpers locales
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function dash($v){ return ($v === null || $v === '' ) ? '—' : e($v); }
function fmtDate($s){ if(!$s) return '—'; $t = strtotime($s); return $t ? date('d/M/Y H:i', $t) : e($s); }
function badgeClass($status){
  $s = strtolower(trim((string)$status));
  return [
    'abierto'     => 'bg-red-50 text-red-700 ring-red-200',
    'en proceso'  => 'bg-amber-50 text-amber-800 ring-amber-200',
    'resuelto'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'cerrado'     => 'bg-slate-100 text-slate-700 ring-slate-200',
  ][$s] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
}

// Lee filtros actuales para mantenerlos en el formulario
$flt_status = $_GET['status'] ?? '';
$flt_q      = $_GET['q'] ?? '';
$flt_desde  = $_GET['desde'] ?? '';
$flt_hasta  = $_GET['hasta'] ?? '';

// Paginación opcional
$page        = isset($page) ? (int)$page : (int)($_GET['page'] ?? 1);
$total_pages = isset($total_pages) ? (int)$total_pages : 1;
$per_page    = isset($per_page) ? (int)$per_page : 25;
$total_rows  = isset($total_rows) ? (int)$total_rows : ($tickets? $tickets->num_rows : 0);
?>

<style>
  /* Mini utilidades estilo Tailwind-like sin dependencia */
  .container{max-width:1200px;margin:18px auto;padding:0 14px}
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
  .badge{display:inline-block;padding:4px 10px;border-radius:999px;font-weight:700;font-size:12px;line-height:1;border:1px solid transparent;box-shadow:inset 0 0 0 1px rgba(0,0,0,0.02)}
  .bg-red-50{background:#fef2f2}.text-red-700{color:#b91c1c}.ring-red-200{box-shadow:0 0 0 1px #fecaca inset}
  .bg-amber-50{background:#fffbeb}.text-amber-800{color:#92400e}.ring-amber-200{box-shadow:0 0 0 1px #fde68a inset}
  .bg-emerald-50{background:#ecfdf5}.text-emerald-700{color:#047857}.ring-emerald-200{box-shadow:0 0 0 1px #a7f3d0 inset}
  .bg-slate-100{background:#f1f5f9}.text-slate-700{color:#334155}.ring-slate-200{box-shadow:0 0 0 1px #e2e8f0 inset}
  .muted{color:#64748b}
  .rowlink{color:inherit;text-decoration:none;display:block}
  .empty{padding:30px;text-align:center;color:#64748b}
  /* Boton copiar */
  .copy{padding:6px 10px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;cursor:pointer;font-weight:600}

  /* Paginación */
  .pager{display:flex;gap:8px;justify-content:flex-end;align-items:center;padding:12px 16px}
  .pager a,.pager span{padding:8px 12px;border:1px solid #e2e8f0;border-radius:10px;text-decoration:none;color:#334155}
  .pager .active{background:#0ea5e9;color:#fff;border-color:#0ea5e9}

  /* Responsive micro-ajustes */
  @media (max-width:640px){ .toolbar{flex-direction:column;align-items:stretch} }

  /* Imprimir limpio */
  @media print{
    .toolbar,.pager,.card-header-actions, a.reset { display:none !important; }
    .card{box-shadow:none;border:0}
    thead th{position:static}
  }
</style>

<div class="container">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Reporte general de tickets</h2>
      <div class="card-header-actions" style="display:flex;gap:8px;align-items:center">
        <button class="copy" onclick="copiarURL()" title="Copiar URL con filtros">Copiar URL</button>
        <a class="reset" href="?">Limpiar</a>
      </div>
    </div>

    <!-- Filtros -->
    <form class="toolbar" method="get">
      <div class="field">
        <label for="q">Buscar</label>
        <input type="text" id="q" name="q" value="<?= e($flt_q) ?>" placeholder="ID, empleado, sistema, error…">
      </div>
      <div class="field">
        <label for="status">Estatus</label>
        <select id="status" name="status">
          <?php $opts = ['', 'Abierto','En proceso','Resuelto','Cerrado']; foreach($opts as $opt): ?>
            <option value="<?= e($opt) ?>" <?= $opt===$flt_status? 'selected':'' ?>><?= $opt?:'Todos' ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="desde">Desde</label>
        <input type="date" id="desde" name="desde" value="<?= e($flt_desde) ?>">
      </div>
      <div class="field">
        <label for="hasta">Hasta</label>
        <input type="date" id="hasta" name="hasta" value="<?= e($flt_hasta) ?>">
      </div>
      <div class="field">
        <button type="submit">Aplicar filtros</button>
      </div>
    </form>

    <!-- Tabla -->
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="min-width:92px">ID</th>
            <th style="min-width:160px">Fecha</th>
            <th style="min-width:220px">Empleado</th>
            <th style="min-width:200px">Soporte</th>
            <th style="min-width:160px">Sistema</th>
            <th style="min-width:240px">Error</th>
            <th style="min-width:130px">Solución</th>
            <th style="min-width:130px">Estatus</th>
          </tr>
        </thead>
        <tbody>
        <?php if(!$tickets || $tickets->num_rows === 0): ?>
          <tr><td colspan="8" class="empty">No hay tickets con los filtros actuales.</td></tr>
        <?php else: ?>
          <?php while ($row = $tickets->fetch_assoc()): 
            $id   = $row['ID_Tiket'] ?? '';
            $href = 'ticket.php?id=' . urlencode((string)$id);
          ?>
            <tr>
              <td class="mono"><a class="rowlink" href="<?= e($href) ?>">#<?= e($id) ?></a></td>
              <td><?= fmtDate($row['FechaReporte'] ?? '') ?></td>
              <td><?= dash($row['Empleado'] ?? '') ?></td>
              <td><?= dash($row['Soporte'] ?? '') ?></td>
              <td><?= dash($row['Sistema'] ?? '') ?></td>
              <td><?= dash($row['TipoError'] ?? '') ?></td>
              <td><?= dash($row['ID_Solucion'] ?? '') ?></td>
              <td><span class="badge <?= badgeClass($row['Estatus'] ?? '') ?>"><?= dash($row['Estatus'] ?? '') ?></span></td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginación (si el controlador la provee) -->
    <div class="pager">
      <?php if($total_pages > 1):
        $qs = $_GET; unset($qs['page']); $base = '?' . http_build_query($qs);
        $pmin = max(1, $page-2); $pmax = min($total_pages, $page+2);
      ?>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=1" aria-label="Primera">«</a>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= max(1,$page-1) ?>" aria-label="Anterior">‹</a>
        <?php for($p=$pmin;$p<=$pmax;$p++): ?>
          <?php if($p == $page): ?>
            <span class="active"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= $p ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= min($total_pages,$page+1) ?>" aria-label="Siguiente">›</a>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= $total_pages ?>" aria-label="Última">»</a>
        <span class="muted">Mostrando <?= (min($per_page, $total_rows - ($page-1)*$per_page)) ?> de <?= $total_rows ?> registros</span>
      <?php else: ?>
        <span class="muted">Total: <?= $total_rows ?> registros</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
 function copiarURL(){
   navigator.clipboard.writeText(window.location.href).then(()=>{
     const btn = document.querySelector('.copy');
     const label = btn.textContent; btn.textContent = '¡Copiado!';
     setTimeout(()=>btn.textContent = label, 1200);
   });
 }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
