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
          <button type="button" id="btn-abrir-sistema">+ Nuevo sistema</button>
          <div id="modal-sistema" class="modal hidden" aria-hidden="true">
            <div class="modal__backdrop" data-close></div>
            <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="ms-title">
              <header class="modal__head">
                <h3 id="ms-title">Crear sistema</h3>
              </header>

              <form id="form-sistema" class="modal__body">
                <div class="field">
                  <label for="ms-nombre">Nombre *</label>
                  <input id="ms-nombre" name="nombre" type="text" required maxlength="120" />
                </div>

                <div class="field">
                  <label for="ms-desc">Descripción</label>
                  <textarea id="ms-desc" name="descripcion" rows="3" maxlength="500"></textarea>
                </div>

                <p id="ms-error" class="error" hidden></p>

                <footer class="modal__foot">
                  <button type="button" id="ms-cancelar" class="btn-sec">Cancelar</button>
                  <button type="submit" id="ms-guardar" class="btn-pri">Guardar</button>
                </footer>
              </form>
            </div>
          </div>
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
  
  (function(){
    const btnAbrir   = document.getElementById('btn-abrir-sistema');
    const modal      = document.getElementById('modal-sistema');
    const form       = document.getElementById('form-sistema');
    const inpNombre  = document.getElementById('ms-nombre');
    const txtDesc    = document.getElementById('ms-desc');
    const btnGuardar = document.getElementById('ms-guardar');
    const btnCancel  = document.getElementById('ms-cancelar');
    const errorBox   = document.getElementById('ms-error');
    const selSys     = document.getElementById('id_sistema'); // ← Tu combo de sistemas

    function abrirModal(){
      errorBox.hidden = true; errorBox.textContent = '';
      form.reset();
      modal.classList.remove('hidden');
      modal.setAttribute('aria-hidden', 'false');
      setTimeout(()=> inpNombre.focus(), 0);
    }
    function cerrarModal(){
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
    }

    // Abrir/cerrar
    btnAbrir?.addEventListener('click', abrirModal);
    btnCancel?.addEventListener('click', cerrarModal);
    modal?.addEventListener('click', (e)=>{
      if (e.target.hasAttribute('data-close')) cerrarModal();
    });
    // ESC para cerrar
    window.addEventListener('keydown', (e)=>{
      if(!modal.classList.contains('hidden') && e.key === 'Escape') cerrarModal();
    });

    // Envío
    form?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      errorBox.hidden = true; errorBox.textContent = '';

      const nombre = inpNombre.value.trim();
      const descripcion = txtDesc.value.trim();

      if(!nombre){
        errorBox.textContent = 'El nombre es obligatorio.';
        errorBox.hidden = false;
        inpNombre.focus();
        return;
      }

      btnGuardar.disabled = true;

      try{
        const resp = await fetch('../app/appTiket.php?accion=CrearSistema', {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body: new URLSearchParams({ nombre, descripcion })
        });

        // Esperamos JSON: { ok:true, id:123, nombre:"X" }
        const data = await resp.json().catch(()=> ({}));

        if(!resp.ok || !data.ok){
          throw new Error(data?.error || `Error HTTP ${resp.status}`);
        }

        // Agregar opción al <select> y seleccionarla
        if (selSys) {
          const opt = new Option(data.nombre, data.id, true, true);
          selSys.add(opt);
          selSys.dispatchEvent(new Event('change'));
        }

        cerrarModal();
        alert('Sistema creado exitosamente.');
      }catch(err){
        console.error(err);
        errorBox.textContent = String(err.message || err) || 'No se pudo crear el sistema.';
        errorBox.hidden = false;
      }finally{
        btnGuardar.disabled = false;
      }
    });
  })()
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
