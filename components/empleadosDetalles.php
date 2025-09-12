<?php
session_start();
require_once __DIR__ . '/../app/appTiket.php';
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$empleadoController = new EmpleadoController($conn);
$equipoController   = new EquipoController($conn);

$numeroEmpleado = $_GET['id'] ?? null;
if (!$numeroEmpleado) {
    http_response_code(400);
    echo "Falta id de empleado.";
    exit;
}

$empleado = $empleadoController->obtenerEmpleadoporNumeroC($numeroEmpleado);
$equipos  = $equipoController->obtenerEquiposporEmpleado($numeroEmpleado);
if (!$empleado) {
    http_response_code(404);
    include __DIR__ . '/../views/404.php';
    exit;
}

$title = "DETALLES DE EMPLEADO";
$rol   = $_SESSION['rol'] ?? 'EMPLEADO';

// Permitir edición solo a SOPORTE/ADMINISTRADOR
$puedeEditar = in_array($rol, ['SOPORTE','ADMINISTRADOR'], true);

include __DIR__ . '/../views/layout/header.php';

// helper seguro
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<style>
  .empleado-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 6px 18px rgba(0,0,0,.05);padding:18px;margin-bottom:16px}
  .empleado-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px}
  .field{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px}
  .label{display:block;font-size:12px;color:#64748b;margin-bottom:4px}
  .value{font-weight:700;color:#0f172a;word-break:break-word}
  .editable{cursor:pointer}
  .editing{background:#fffbe6;border-color:#facc15}
  .edit-actions{margin-top:6px;display:flex;gap:8px}
  .btnx{padding:6px 10px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;cursor:pointer;font-weight:700}
  .btnx.primary{background:#0ea5e9;color:#fff;border-color:#0ea5e9}
  .btnx.danger{background:#ef4444;color:#fff;border-color:#ef4444}
  .muted{color:#64748b}
  .badge{display:inline-block;border-radius:999px;padding:2px 8px;font-size:12px;background:#e2e8f0;color:#334155;font-weight:700}

  .table-wrap{overflow:auto;border:1px solid #e2e8f0;border-radius:12px}
  table{border-collapse:separate;border-spacing:0;width:100%}
  thead th{position:sticky;top:0;background:#f8fafc;padding:10px;text-align:left;border-bottom:1px solid #e2e8f0}
  tbody td{padding:10px;border-bottom:1px solid #f1f5f9}
</style>

<div class="empleado-card">
  <h2 style="margin:0 0 10px">DETALLES DE EMPLEADO
    <span class="badge"><?= $puedeEditar ? 'Edición habilitada' : 'Solo lectura' ?></span>
  </h2>
  <div class="empleado-grid">

    <!-- Campo genérico editable -->
    <?php
      // Mapa: etiqueta => [clave_bd, editable(bool), sensible(bool)]
      $campos = [
        'Usuario'              => ['USUARIO',           true,  false],
        'Número de empleado'   => ['Numero_Empleado',   false, false],
        'Nombre'               => ['Nombre',            true,  false],
        'Puesto'               => ['PUESTO',            true,  false],
        'Sucursal'             => ['SUCURSAL',          true,  false],
        'Área'                 => ['AREA',              true,  false],
        'Email'                => ['Correo',            true,  false],
        'Teléfono'             => ['Telefono',          true,  false],
        'Extensión'            => ['Extencion',         true,  false],
        // Sensibles (ocultos por defecto, con botón de mostrar)
        'AnyDesk (usuario)'    => ['UsuarioAnyDesk',    true,  false],
        'AnyDesk (password)'   => ['ClaveAnyDesk',      true,  true ],
        'Usuario SAP'          => ['UsuarioSAP',        true,  false],
        'Password SAP'         => ['ClaveSAP',          true,  true ],
        'Password Correo'      => ['ClaveCorreo',       true,  true ],
      ];
      foreach ($campos as $label => [$key, $editable, $sensible]):
        $valor = $empleado[$key] ?? '';
        $mask  = $sensible ? str_repeat('•', max(8, strlen((string)$valor))) : $valor;
    ?>
      <div class="field" data-key="<?= e($key) ?>">
        <span class="label"><?= e($label) ?></span>
        <div class="value <?= $editable && $puedeEditar ? 'editable' : '' ?>"
             data-editable="<?= $editable && $puedeEditar ? '1' : '0' ?>"
             data-sensible="<?= $sensible ? '1' : '0' ?>"
             data-real="<?= e($valor) ?>"
             tabindex="<?= $editable && $puedeEditar ? '0' : '-1' ?>">
          <?= e($mask) ?>
        </div>

        <?php if ($sensible): ?>
          <div class="edit-actions">
            <button class="btnx" type="button" data-action="toggle-visibility">Mostrar/Ocultar</button>
            <?php if ($editable && $puedeEditar): ?>
              <button class="btnx primary" type="button" data-action="edit">Editar</button>
            <?php endif; ?>
          </div>
        <?php elseif ($editable && $puedeEditar): ?>
          <div class="edit-actions" hidden>
            <button class="btnx primary" type="button" data-action="save">Guardar</button>
            <button class="btnx" type="button" data-action="cancel">Cancelar</button>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

  </div>

  <div style="margin-top:12px">
    <a href="../views/dashboard.php" class="btnx">Volver al Dashboard</a>
  </div>
</div>

<div class="empleado-card">
  <h3 style="margin:0 0 10px">Equipos asignados</h3>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>EQUIPO</th>
          <th>NÚMERO DE ACTIVO FIJO</th>
          <th>FECHA DE ASIGNACIÓN</th>
          <th>DIRECCIÓN IP</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($equipos && is_array($equipos) && count($equipos) > 0): ?>
        <?php foreach ($equipos as $row): ?>
          <tr>
            <td><?= e($row['TIPO_EQUIPO'] ?? '') ?></td>
            <td><?= e($row['NUMERO_ACTIVO_FIJO'] ?? '') ?></td>
            <td><?= e($row['FECHA_ASIGNACION'] ?? '') ?></td>
            <td><?= e($row['DIRECCION_IP'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" class="muted">Sin equipos asignados.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(function(){
  const puedeEditar = <?= $puedeEditar ? 'true':'false' ?>;
  const numero = <?= json_encode((string)$numeroEmpleado) ?>;

  // Doble clic o botón “Editar” para entrar a modo edición (no sensible)
  document.querySelectorAll('.field .value[data-editable="1"][data-sensible="0"]').forEach(v => {
    const field = v.closest('.field');
    const actions = field.querySelector('.edit-actions');

    const enterEdit = () => {
      if (!puedeEditar) return;
      v.contentEditable = 'true';
      v.classList.add('editing');
      actions.hidden = false;
      v.focus();
      // coloca cursor al final
      document.execCommand('selectAll', false, null);
      document.getSelection().collapseToEnd();
    };

    v.addEventListener('dblclick', enterEdit);
    v.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        actions.querySelector('[data-action="save"]').click();
      } else if (e.key === 'Escape') {
        e.preventDefault();
        actions.querySelector('[data-action="cancel"]').click();
      }
    });

    // Botones
    actions?.querySelector('[data-action="save"]')?.addEventListener('click', async () => {
      const key = field.dataset.key;
      const nuevo = v.innerText.trim();
      await guardarCampo(numero, key, nuevo, v, actions);
    });

    actions?.querySelector('[data-action="cancel"]')?.addEventListener('click', () => {
      v.textContent = v.getAttribute('data-real') || '';
      salirEdicion(v, actions);
    });
  });

  // Sensibles: Mostrar/Ocultar y botón Editar abre modal prompt (evitar exponer en DOM editable)
  document.querySelectorAll('.field .value[data-sensible="1"]').forEach(v => {
    const field = v.closest('.field');
    const actions = field.querySelector('.edit-actions');
    const btnToggle = actions?.querySelector('[data-action="toggle-visibility"]');
    const btnEdit   = actions?.querySelector('[data-action="edit"]');

    btnToggle?.addEventListener('click', () => {
      const real = v.getAttribute('data-real') || '';
      const visible = v.dataset.visible === '1';
      if (visible) {
        v.textContent = '•'.repeat(Math.max(8, real.length));
        v.dataset.visible = '0';
      } else {
        v.textContent = real || '';
        v.dataset.visible = '1';
      }
    });

    btnEdit?.addEventListener('click', async () => {
      if (!puedeEditar) return;
      const key = field.dataset.key;
      const actual = v.getAttribute('data-real') || '';
      const nuevo = prompt('Nuevo valor para ' + key + ':', actual);
      if (nuevo === null) return;
      await guardarCampo(numero, key, nuevo, v, actions, /*esSensible*/true);
    });
  });

  function salirEdicion(v, actions){
    v.contentEditable = 'false';
    v.classList.remove('editing');
    if (actions) actions.hidden = true;
  }

  async function guardarCampo(numero, key, value, valueNode, actions, esSensible=false){
    try{
      // POST a endpoint (crea este handler en appEmpleado.php)
      const res = await fetch('../app/appEmpleado.php?accion=actualizarCampoDeUsuario', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ numero_empleado: numero, campo: key, valor: value })
      });
      const data = await res.json();
      if (!res.ok || !data?.ok) throw new Error(data?.error || 'Error al guardar');

      // Actualiza UI
      valueNode.setAttribute('data-real', value);
      if (valueNode.dataset.sensible === '1' && valueNode.dataset.visible === '1') {
        valueNode.textContent = value;
      } else if (valueNode.dataset.sensible === '1') {
        valueNode.textContent = '•'.repeat(Math.max(8, value.length));
      } else {
        valueNode.textContent = value;
        salirEdicion(valueNode, actions);
      }
      alert('Guardado ✅');
    }catch(err){
      console.error(err);
      alert('No se pudo guardar: ' + err.message);
    }
  }
})();
</script>

<?php include __DIR__ . '/../views/layout/footer.php'; ?>
