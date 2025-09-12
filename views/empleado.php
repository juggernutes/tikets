<?php
// empleados.php (vista)
session_start();

include __DIR__ . '/../app/appTiket.php';
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$title = 'EMPLEADOS';

// --- Datos (acepta array o mysqli_result) ---
$empleadosData = $empleadoController->obtenerTodosLosEmpleados();
$empleados = [];

// Normaliza a array
if (is_array($empleadosData)) {
    $empleados = $empleadosData;
} elseif ($empleadosData instanceof mysqli_result) {
    while ($r = $empleadosData->fetch_assoc()) { $empleados[] = $r; }
}

// --- Filtros y paginación (GET) ---
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$q    = trim($_GET['q'] ?? '');
$per  = max(10, min(100, (int)($_GET['per'] ?? 25)));
$page = max(1, (int)($_GET['page'] ?? 1));

// Filtra por texto libre en Número, Nombre, Puesto, Sucursal
if ($q !== '') {
    $needle = mb_strtolower($q, 'UTF-8');
    $empleados = array_values(array_filter($empleados, function($r) use ($needle){
        $hay = mb_strtolower(implode(' ', [
          $r['Numero_Empleado'] ?? '',
          $r['Nombre']          ?? '',
          $r['PUESTO']          ?? '',
          $r['SUCURSAL']        ?? '',
        ]), 'UTF-8');
        return mb_strpos($hay, $needle) !== false;
    }));
}

// Orden opcional por Nombre
usort($empleados, function($a,$b){
    return strcasecmp($a['Nombre'] ?? '', $b['Nombre'] ?? '');
});

// Paginación
$total_rows  = count($empleados);
$total_pages = max(1, (int)ceil($total_rows / $per));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per;
$view        = array_slice($empleados, $offset, $per);

include __DIR__ . '/../views/layout/header.php';
?>

<style>
  .container{max-width:1200px;margin:18px auto;padding:0 14px}
  .card{background:#fff;border:1px solid #e9e9e9;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,.05)}
  .card-header{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #f1f5f9}
  .card-title{margin:0;font-size:18px;font-weight:800;color:#0f172a}
  .toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin:10px 16px}
  .field{display:flex;flex-direction:column;gap:6px}
  .field input,.field select{padding:8px 10px;border:1px solid #e2e8f0;border-radius:10px;font-size:14px}
  .field button{padding:9px 14px;border:1px solid #0ea5e9;background:#0ea5e9;color:#fff;border-radius:12px;font-weight:600;cursor:pointer}
  .reset{padding:9px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:12px;text-decoration:none;color:#334155;font-weight:600}

  .table-wrap{overflow:auto;border-radius:0 0 16px 16px}
  table{border-collapse:separate;border-spacing:0;width:100%;font-size:14px}
  thead th{position:sticky;top:0;background:#f8fafc;color:#334155;text-align:left;font-weight:700;padding:12px;border-bottom:1px solid #e2e8f0}
  tbody td{padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#0f172a;vertical-align:top}
  tbody tr:hover{background:#f8fafc}
  .clickable{cursor:pointer}

  .pager{display:flex;gap:8px;justify-content:flex-end;align-items:center;padding:12px 16px}
  .pager a,.pager span{padding:8px 12px;border:1px solid #e2e8f0;border-radius:10px;text-decoration:none;color:#334155}
  .pager .active{background:#0ea5e9;color:#fff;border-color:#0ea5e9}
</style>

<div class="container">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Empleados</h2>
    </div>

    <form class="toolbar" method="get">
      <div class="field">
        <label for="q">Buscar</label>
        <input id="q" name="q" value="<?= e($q) ?>" placeholder="número, nombre, puesto, sucursal">
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
            <th>Número de Empleado</th>
            <th>Nombre</th>
            <th>Puesto</th>
            <th>Sucursal</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($view)): ?>
          <tr><td colspan="4" style="text-align:center; color:#64748b; padding:20px">Sin resultados.</td></tr>
        <?php else: ?>
          <?php foreach ($view as $empleado): 
            $num = $empleado['Numero_Empleado'] ?? '';
            $url = '../components/empleadosDetalles.php?id=' . urlencode((string)$num);
          ?>
            <tr class="clickable" onclick="location.href='<?= e($url) ?>'">
              <td><?= e($num) ?></td>
              <td><?= e($empleado['Nombre']   ?? '') ?></td>
              <td><?= e($empleado['PUESTO']   ?? '') ?></td>
              <td><?= e($empleado['SUCURSAL'] ?? '') ?></td>
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
        <span class="muted">Total: <?= $total_rows ?> empleados</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../views/layout/footer.php'; ?>
