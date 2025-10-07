<?php
$title = "Registrar Ticket";
include_once __DIR__ . '/../app/appTiket.php';
include __DIR__ . '/layout/header.php';

$idUsuario = $_SESSION['login_id'] ?? 0;

?>

<main class="page" style="padding: 20px;">
  <!-- Título de página -->
  <header class="page-head" style="max-width:1100px;margin:0 auto 14px;">
    <link rel="stylesheet" href="../tools/newStyle.css">

    <h1 class="page-title" style="margin:10px 0 4px; font-weight:800; letter-spacing:.2px;">
      <?= htmlspecialchars($title) ?>
    </h1>
    <p class="page-sub" style="margin:0; color:#8aa2b4;">Crea un nuevo ticket de soporte</p>
  </header>

  <!-- Tarjeta del formulario -->
  <section class="card" style="max-width:1100px;margin:0 auto; background:#fff; border:1px solid #e9e9e9; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.06);">
    <form action="../app/appTiket.php?accion=crearTiket" method="POST" style="padding:16px 16px 20px;">
      <!-- Grid del formulario -->
      <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">

        <!-- Sucursal -->
        <div class="field">
          <label for="id_sucursal" class="label">Sucursal</label>
          <select name="id_sucursal" id="id_sucursal" class="input" required>
            <option value="">Seleccione la sucursal</option>
            <?php include __DIR__ . '/../partials/combo_sucursales.php'; ?>
          </select>
        </div>
        <script>
          document.getElementById('id_sucursal').addEventListener('change',function(){
            const idSucursal = this.value;

          });
        </script>
        

        <!-- Empleado -->
        <div class="field">
          <label for="Numero_Empleado" class="label">Empleado</label>
          <select name="Numero_Empleado" id="Numero_Empleado" class="input" required>
            <option value="">Seleccione un empleado</option>
            <?php
              include __DIR__ . '/../partials/combo_empleados.php';
              // $empleados = $empleadoController->obtenerEmpleados($idUsuario);
            ?>
          </select>
        </div>

        <!-- Sistema -->
        <div class="field">
          <label for="id_sistema" class="label">Sistema</label>
          <select name="id_sistema" id="id_sistema" class="input" required>
            <option value="">Seleccione un sistema</option>
            <?php include __DIR__ . '/../partials/combo_sistemas.php'; ?>
          </select>
        </div>

        <!-- Descripción del sistema -->
        <div class="field" style="grid-column: 1 / -1;">
          <div class="meta" style="padding:10px 12px; border:1px dashed #d8e6f3; border-radius:8px; background:#f6fbff;">
            <strong style="margin-right:6px;">Descripción del sistema:</strong>
            <span id="descripcionSistema" style="color:#0f6292;font-size:30px;">Seleccione un sistema</span>
          </div>
        </div>

        <!-- Descripción del ticket -->
        <div class="field" style="grid-column: 1 / -1;">
          <label for="descripcion" class="label">Descripción del ticket</label>
          <textarea name="descripcion" id="descripcion" class="input"
            rows="5" required
            placeholder="Describe el problema, pasos previos, capturas o mensajes de error..."></textarea>
        </div>
      </div>

      <!-- Acciones -->
      <div class="actions" style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
        <a href="./dashboard.php" class="app-btn app-btn--secondary" style="background:#fff;color:#0f6292;border-color:#d8e6f3;">Cancelar</a>
        <button type="submit" class="app-btnCap">Guardar ticket</button>
      </div>

      <!-- (Opcional) hidden para el usuario actual -->
      <input type="hidden" name="id_usuario_crea" value="<?= (int)$idUsuario ?>">
    </form>
  </section>
</main>

<script>
  // Mostrar descripción del sistema seleccionado
  document.getElementById('id_sistema')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const desc = opt?.getAttribute('data-descripcion') || 'Seleccione un sistema';
    document.getElementById('descripcionSistema').textContent = desc;
  });

  // Auto-grow del textarea (suave)
  const ta = document.getElementById('descripcion');
  if (ta) {
    const grow = () => { ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight + 4) + 'px'; };
    ta.addEventListener('input', grow);
    window.addEventListener('load', grow);
  }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
