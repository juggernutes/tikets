<?php
// resolver_ticket.php (vista)
include __DIR__ . '/../app/appTiket.php';

$idTiket = $_GET['id'] ?? null;
if (!$idTiket) { echo 'No se proporcionó un ticket.'; exit; }

$ticket = $tiketController->getTicketById($idTiket);
if (!$ticket) { echo 'No se encontró el ticket.'; exit; }

// 🔧 FIX: variable mal escrita ($tiket -> $ticket)
$empleado = null;
if (isset($empleadoController)) {
  $empleado = $empleadoController->obtenerEmpleadoporNumeroC($ticket['Numero_Empleado'] ?? null);
}

$title = 'Resolver ticket';
$descripcionSolucion = $ticket['DESCRIPCION_SOLUCION'] ?? '';
$selectedErrorId     = $ticket['ID_Error']    ?? '';
$selectedSolucionId  = $ticket['ID_Solucion'] ?? '';

include __DIR__ . '/layout/header.php';
?>

<style>
  .contenedor-tiket{display:grid;gap:18px;grid-template-columns:1.1fr .9fr;align-items:start}
  @media (max-width: 900px){ .contenedor-tiket{grid-template-columns:1fr} }
  .tiket-info,.tiket-solucion{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 6px 18px rgba(0,0,0,.05)}
  .tiket-info{padding:18px}
  .tiket-solucion{padding:18px}
  h2{margin:0 0 10px;color:#0f172a}
  .meta p{margin:6px 0;color:#334155}
  .badge{display:inline-block;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:800}
  .b-abierto{background:#fef2f2;color:#b91c1c}
  .b-proceso{background:#fffbeb;color:#92400e}
  .b-cerrado{background:#ecfdf5;color:#047857}

  .form-row{display:grid;gap:12px}
  .form-row label{font-weight:700;color:#334155}
  select, textarea{width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:10px;font-size:14px}
  textarea{min-height:140px;resize:vertical}
  .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  .btn{padding:9px 14px;border:1px solid #e2e8f0;background:#fff;border-radius:12px;font-weight:700;cursor:pointer;color:#0f172a;text-decoration:none}
  .btn.primary{background:#0ea5e9;color:#fff;border-color:#0ea5e9}
  .btn.danger{background:#ef4444;color:#fff;border-color:#ef4444}
  .btn[disabled]{opacity:.6;cursor:not-allowed}

  .tarjeta-empleado{background:#f6f9ff;border:1.5px solid #b6cced;border-radius:10px;padding:14px 22px;margin:15px 0 10px 0;box-shadow:0 1px 7px #aac8e930}
  .tarjeta-empleado h3{color:#23478d;margin:0 0 10px}
  .muted{color:#64748b}
</style>

<div class="contenedor-tiket">
  <!-- INFO DEL TICKET -->
  <div class="tiket-info">
    <h2>Resolver Ticket <span class="muted">#<?= htmlspecialchars($ticket['Folio']) ?></span></h2>
    <div class="meta">
      <p><strong>Sistema:</strong> <?= htmlspecialchars($ticket['SISTEMA']) ?></p>
      <p><strong>Fecha:</strong> <?= htmlspecialchars($ticket['FECHA']) ?></p>
      <p><strong>Estado:</strong>
        <?php $s = strtolower($ticket['ESTADO'] ?? '');
          $cls = $s==='abierto'?'b-abierto':($s==='en proceso'?'b-proceso':'b-cerrado'); ?>
        <span class="badge <?= $cls ?>"><?= htmlspecialchars($ticket['ESTADO']) ?></span>
      </p>
      <?php $descripcion = $ticket['DESCRIPCION'] ?? ''; if (strpos((string)$descripcion, ' ') === false) { $descripcion = wordwrap($descripcion, 40, "\n", true);} ?>
      <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')) ?></p>

      <?php if ($empleado): ?>
        <div class="tarjeta-empleado">
          <h3>Datos del empleado</h3>
          <p><strong>Nombre:</strong> <?= htmlspecialchars($empleado['Nombre']   ?? $ticket['EMPLEADO']) ?></p>
          <p><strong>Puesto:</strong> <?= htmlspecialchars($empleado['Puesto']   ?? $ticket['PUESTO']) ?></p>
          <p><strong>Sucursal:</strong> <?= htmlspecialchars($empleado['Sucursal'] ?? $ticket['SUCURSAL']) ?></p>
          <?php if (!empty($empleado['Correo'])): ?><p><strong>Correo:</strong> <?= htmlspecialchars($empleado['Correo']) ?></p><?php endif; ?>
          <?php if (!empty($empleado['Telefono'])): ?><p><strong>Teléfono:</strong> <?= htmlspecialchars($empleado['Telefono']) ?></p><?php endif; ?>
        </div>
      <?php endif; ?>

      <!--<div class="actions">
        <a class="btn" href="ticket.php?id=<?= urlencode((string)($ticket['ID_Tiket'] ?? '')) ?>">Ver detalle</a>
      </div>-->
    </div>
  </div>

  <!-- FORMULARIO DE SOLUCIÓN -->
  <div class="tiket-solucion">
    <form id="form-solucion" action="../app/appTiket.php?accion=solucionar&id_tiket=<?= urlencode((string)$ticket['ID_Tiket']) ?>" method="POST">
      <input type="hidden" name="id_tiket" value="<?= htmlspecialchars($ticket['ID_Tiket']) ?>">

      <div class="form-row">
        <label for="id_error">SISTEMA</label>
        <select name="id_error" id="id_error" required>
          <option value="">Selecciona un error</option>
          <?php include __DIR__ . '/../partials/combo_errores.php'; ?>
        </select>
      </div>

      <div class="form-row">
        <label for="id_solucion">SOLUCION</label>
        <select name="id_solucion" id="id_solucion" required>
          <option value="">Selecciona una solución</option>
          <?php include __DIR__ . '/../partials/combo_soluciones.php'; ?>
        </select>
      </div>

      <div class="form-row">
        <label for="descripcion_solucion">Descripción de la solución</label>
        <textarea name="descripcion_solucion" id="descripcion_solucion" rows="7" maxlength="2000" placeholder="Qué hiciste, pasos, evidencia..." required><?= htmlspecialchars($descripcionSolucion) ?></textarea>
        <small class="muted"><span id="count">0</span>/2000</small>
      </div>

      <div class="actions">
        <button type="submit" class="btn primary" id="btn-submit">Solucionar</button>
        <a class="btn" href="javascript:history.back()">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function(){
    // Preseleccionar valores si el ticket ya tiene IDs guardados
    const selErr = document.getElementById('id_error');
    const selSol = document.getElementById('id_solucion');
    const preErr = <?= json_encode((string)$selectedErrorId) ?>;
    const preSol = <?= json_encode((string)$selectedSolucionId) ?>;
    if (preErr) selErr.value = preErr;
    if (preSol) selSol.value = preSol;

    // Contador de caracteres
    const ta = document.getElementById('descripcion_solucion');
    const count = document.getElementById('count');
    const updateCount = () => { count.textContent = ta.value.length; }
    ta.addEventListener('input', updateCount); updateCount();

    // Evitar doble envío
    const form = document.getElementById('form-solucion');
    const btn = document.getElementById('btn-submit');
    form.addEventListener('submit', function(){ btn.disabled = true; btn.textContent = 'Guardando…'; });
  });
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
