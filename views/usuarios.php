<?php 
session_start();
include __DIR__ . '/../app/appTiket.php';

if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$title = 'USUARIOS';

// --- Datos ---
$usuariosData = $loginController->obtenerTodosLosUsuarios();
$usuarios = [];

if (is_array($usuariosData)) {
    $usuarios = $usuariosData;
} elseif ($usuariosData instanceof mysqli_result) {
    while ($r = $usuariosData->fetch_assoc()) { 
        $usuarios[] = $r; 
    }
}

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// --- Filtros ---
$q    = trim($_GET['q'] ?? '');
$per  = max(10, min(100, (int)($_GET['per'] ?? 25)));
$page = max(1, (int)($_GET['page'] ?? 1));

// Buscar
if ($q !== '') {
    $needle = mb_strtolower($q, 'UTF-8');
    $usuarios = array_values(array_filter($usuarios, function($r) use ($needle){
        $hay = mb_strtolower(
            ($r['Nombre'] ?? '') . ' ' . ($r['Rol'] ?? ''), 
        'UTF-8');
        return mb_strpos($hay, $needle) !== false;
    }));
}

// Orden por Nombre
usort($usuarios, function($a, $b){
    return strcasecmp($a['Nombre'] ?? '', $b['Nombre'] ?? '');
});

// --- Paginación ---
$total_rows  = count($usuarios);
$total_pages = max(1, (int)ceil($total_rows / $per));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per;
$view        = array_slice($usuarios, $offset, $per);

include __DIR__ . '/../views/layout/header.php';


?>

<style>
  .container {max-width:1200px;margin:18px auto;padding:0 14px}
  .card {background:#fff;border:1px solid #e9e9e9;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,.05)}
  .card-header {display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #f1f5f9}
  .card-title {margin:0;font-size:18px;font-weight:800;color:#0f172a}
  .toolbar {display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin:10px 16px}
  .field {display:flex;flex-direction:column;gap:6px}
  .field input,.field select {padding:8px 10px;border:1px solid #e2e8f0;border-radius:10px;font-size:14px}
  .field button {padding:9px 14px;border:1px solid #0ea5e9;background:#0ea5e9;color:#fff;border-radius:12px;font-weight:600;cursor:pointer}
  .reset {padding:9px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:12px;text-decoration:none;color:#334155;font-weight:600}

  /* Estilo tabla */
  .table-wrap {overflow:auto;border-radius:0 0 16px 16px}
  table {border-collapse:separate;border-spacing:0;width:100%;font-size:14px}
  thead th {position:sticky;top:0;background:#f8fafc;color:#334155;text-align:left;font-weight:700;padding:12px;border-bottom:1px solid #e2e8f0}
  tbody td {padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#0f172a;vertical-align:top}
  tbody tr:hover {background:#f8fafc}

  /* Estilo botón RESET */
  .btn-reset {
      padding:7px 12px;
      background:#f59e0b;      /* NARANJA */
      color:#fff;
      border:none;
      border-radius:10px;
      cursor:pointer;
      font-weight:600;
      transition:0.2s;
  }
  .btn-reset:hover {
      background:#d97706;
  }

  .pager {display:flex;gap:8px;justify-content:flex-end;align-items:center;padding:12px 16px}
  .pager a,.pager span {padding:8px 12px;border:1px solid #e2e8f0;border-radius:10px;text-decoration:none;color:#334155}
  .pager .active {background:#0ea5e9;color:#fff;border-color:#0ea5e9}
</style>

<div class="container">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Lista de Usuarios</h2>
    </div>

    <div class="toolbar">
      <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
        <div class="field">
          <label for="q">Buscar</label>
          <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="Nombre, Rol">
        </div>

        <div class="field">
          <label for="per">Resultados por página</label>
          <select id="per" name="per">
            <?php foreach ([10,25,50,100] as $n): ?>
              <option value="<?= $n ?>" <?= $per == $n ? 'selected' : '' ?>><?= $n ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <button type="submit">Filtrar</button>
        </div>

        <a href="usuarios.php" class="reset">Restablecer</a>
      </form>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Rol</th>
            <th>Acción</th>
          </tr>
        </thead>

        <tbody>
          <?php if (count($view) === 0): ?>
            <tr><td colspan="3">No se encontraron usuarios.</td></tr>

          <?php else: ?>
            <?php foreach ($view as $usuario): ?>
              <tr>
                <td><?= e($usuario['Nombre'] ?? '') ?></td>
                <td><?= e($usuario['Rol'] ?? '') ?></td>
                <td><?= e($usuario['ID_Login'] ?? '') ?></td>
                <td>
                  <form action="../app/appTiket.php?accion=resetPasswordUsuario" method="post">
                    <input type="hidden" name="id" value="<?= e($usuario['ID_Login'] ?? '') ?>">
                    <button type="submit" class="btn-reset">Resetear</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>

      </table>
    </div>

    <div class="pager">
      <?php if ($total_pages > 1): ?>
        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
          <?php
            $params = $_GET;
            $params['page'] = $p;
            $url = 'usuarios.php?' . http_build_query($params);
          ?>
          <?php if ($p === $page): ?>
            <span class="active"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= e($url) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../views/layout/footer.php'; ?>
