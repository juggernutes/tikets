<?php
$title = "Registrar Ticket";
include_once __DIR__ . '/../app/appTiket.php';
include __DIR__ . '/layout/header.php';

$idUsuario = $_SESSION['login_id'] ?? 0;
?>
<main class="page" style="padding: 20px;">
  <header class="page-head" style="max-width:1100px;margin:0 auto 14px;">
    <link rel="stylesheet" href="../tools/newStyle.css">
    <h1 class="page-title" style="margin:10px 0 4px; font-weight:800; letter-spacing:.2px;">
      <?= htmlspecialchars($title) ?>
    </h1>
    <p class="page-sub" style="margin:0; color:#8aa2b4;">Crea un nuevo ticket de soporte</p>
  </header>

  <section class="card" style="max-width:1100px;margin:0 auto; background:#fff; border:1px solid #e9e9e9; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.06);">
    <form action="../app/appTiket.php?accion=crearTiket" method="POST" style="padding:16px 16px 20px;">
      <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">

        <!-- Sucursal 
        <div class="field">
          <label for="id_sucursal" class="label">Sucursal</label>
          <select name="id_sucursal" id="id_sucursal" class="input" required>
            <option value="">Seleccione la sucursal</option>
            <?php include __DIR__ . '/../partials/combo_sucursales.php'; ?>
          </select>
        </div>-->

        <!-- Empleado -->
        <div class="field">
          <label for="Numero_Empleado" class="label">Empleado</label>
          <select name="Numero_Empleado" id="Numero_Empleado" class="input empleados" required>
            <option value="">Seleccione un empleado</option>
            <?php include __DIR__ . '/../partials/combo_empleados.php'; ?>
          </select>
        </div>

        <!-- Sistema -->
        <div class="field">
          <label for="id_sistema" class="label">Sistema</label>
          <div style="display:flex; gap:8px; align-items:center;">
            <select name="id_sistema" id="id_sistema" class="input" required style="flex:1;">
              <option value="">Seleccione un sistema</option>
              <?php include __DIR__ . '/../partials/combo_sistemas.php'; /* Asegura data-descripcion en cada <option> */ ?>
            </select>
            <button id="btnNuevoSistema" type="button" class="app-btnCap">+ Nuevo</button>
            <button type="button" id="btnBajaSistema" class="app-btnCap app-btn--danger" title="Desactivar sistema">-</button>
          </div>
        </div>

        <!-- Descripción del sistema -->
        <div class="field" style="grid-column: 1 / -1;">
          <div class="meta" style="padding:10px 12px; border:1px dashed #d8e6f3; border-radius:8px; background:#f6fbff;">
            <strong style="margin-right:6px;">Descripción del sistema:</strong>
            <span id="descripcionSistema" style="color:#0f6292;">Seleccione un sistema</span>
          </div>
        </div>

        <!-- Descripción del ticket -->
        <div class="field" style="grid-column: 1 / -1;">
          <label for="descripcion" class="label">Descripción del ticket</label>
          <textarea name="descripcion" id="descripcion" class="input" rows="5" required
            placeholder="Describe el problema, pasos previos, capturas o mensajes de error..."></textarea>
        </div>
      </div>

      <!-- Acciones -->
      <div class="actions" style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
        <a href="./dashboard.php" class="app-btn app-btn--secondary" style="background:#fff;color:#0f6292;border-color:#d8e6f3;">Cancelar</a>
        <button type="submit" class="app-btnCap">Guardar ticket</button>
      </div>

      <input type="hidden" name="id_usuario_crea" value="<?= (int)$idUsuario ?>">
    </form>

    <!-- Modal: Nuevo Sistema -->
    <div id="modalSistema" class="modal hidden">
      <div class="modal-content">
        <form id="formSistema">
          <h2 style="color:#0f6292;margin:0 0 10px;">Nuevo sistema</h2>
          <label for="nombreSistema">Nombre <span style="color:#d00">*</span></label>
          <input type="text" id="nombreSistema" name="nombre" maxlength="100" required>

          <label for="descSistema">Descripción</label>
          <textarea id="descSistema" name="descripcion" maxlength="255"></textarea>

          <div class="modal-actions" style="display:flex; gap:8px; justify-content:flex-end; margin-top:12px;">
            <button type="button" id="cancelarSistema" class="app-btnCap app-btn--secondary">Cancelar</button>
            <button type="submit" class="app-btnCap">Guardar</button>
          </div>
        </form>
        <p id="msgSistema" class="msg" style="margin-top:8px;color:#b30000;"></p>
      </div>
    </div>
    <div id="modalBajaSistema" class="modal hidden">
      <div class="modal-content">
        <h3>Desactivar sistema</h3>
        <p>¿Seguro que quieres desactivar <strong id="bajaNombreSistema"></strong>?</p>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
          <button type="button" id="bajaCancelar" class="app-btnCap app-btn--secondary">Cancelar</button>
          <button type="button" id="bajaConfirmar" class="app-btnCap app-btn--danger">Desactivar</button>
        </div>
        <p id="bajaMsg" class="msg" style="margin-top:8px;color:#b30000;"></p>
      </div>
    </div>
  </section>
</main>

<script>
  // ---------- Utils ----------
  const $ = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

  // Auto-grow del textarea
  (function(){
    const ta = $('#descripcion');
    if (!ta) return;
    const grow = () => { ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight + 4) + 'px'; };
    ta.addEventListener('input', grow);
    window.addEventListener('load', grow);
  })();

  // ---------- Sucursal -> Empleados ----------
  (function(){
    const selSuc = $('#id_sucursal');
    const selEmp = $('#Numero_Empleado');

    async function cargarEmpleadosPorSucursal(idSucursal){
      selEmp.innerHTML = '<option value="">Cargando empleados…</option>';
      try {
        const resp = await fetch(`../app/appTiket.php?accion=EmpleadosPorSucursal&id_sucursal=${encodeURIComponent(idSucursal)}`);
        const data = await resp.json();
        selEmp.innerHTML = '<option value="">Seleccione un empleado</option>';
        (data || []).forEach(emp => {
          const opt = document.createElement('option');
          opt.value = String(emp.Numero_Empleado ?? emp.numero_empleado ?? '');
          opt.textContent = String(emp.Nombre ?? emp.nombre ?? '').trim() || '(Sin nombre)';
          selEmp.appendChild(opt);
        });
      } catch (e) {
        selEmp.innerHTML = '<option value="">Error al cargar</option>';
      }
    }

    selSuc?.addEventListener('change', e => {
      const id = e.target.value;
      if (id) cargarEmpleadosPorSucursal(id);
      else selEmp.innerHTML = '<option value="">Seleccione un empleado</option>';
    });
  })();

  // ---------- Descripción del sistema ----------
  (function(){
    const selSis = $('#id_sistema');
    const out = $('#descripcionSistema');
    const updateDesc = () => {
      const opt = selSis.options[selSis.selectedIndex];
      const desc = opt?.getAttribute('data-descripcion') || 'Seleccione un sistema';
      out.textContent = desc;
    };
    selSis?.addEventListener('change', updateDesc);
    window.addEventListener('load', updateDesc);
  })();

  // ---------- Modal Nuevo Sistema ----------
  (function(){
    const modal = $('#modalSistema');
    const openBtn = $('#btnNuevoSistema');
    const cancelBtn = $('#cancelarSistema');
    const form = $('#formSistema');
    const msg = $('#msgSistema');
    const selSis = $('#id_sistema');

    function openModal(){
      form.reset();
      msg.textContent = '';
      modal.classList.remove('hidden');
      $('#nombreSistema').focus();
    }
    function closeModal(){ modal.classList.add('hidden'); }

    openBtn?.addEventListener('click', openModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      msg.textContent = '';

      const nombre = $('#nombreSistema').value.trim();
      const descripcion = $('#descSistema').value.trim();
      if (!nombre) { msg.textContent = 'El nombre es obligatorio.'; return; }

      try {
        const resp = await fetch('../app/appTiket.php?accion=CrearSistema', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
          body: new URLSearchParams({ nombre, descripcion })
        });
        const data = await resp.json();

        if (!data || !data.ok) {
          msg.textContent = (data && data.error) ? data.error : 'No fue posible guardar.';
          return;
        }

        // Agregar opción al select y seleccionarla
        const opt = document.createElement('option');
        opt.value = String(data.id);
        opt.textContent = data.nombre;
        if (data.descripcion) opt.setAttribute('data-descripcion', data.descripcion);
        selSis.appendChild(opt);
        selSis.value = String(data.id);
        selSis.dispatchEvent(new Event('change')); // refresca la descripción

        closeModal();
      } catch (err) {
        msg.textContent = 'Error de red o servidor.';
      }
    });
  })();
  (function(){
  const sel = document.getElementById('id_sistema');
  const btnBaja = document.getElementById('btnBajaSistema');
  const modal = document.getElementById('modalBajaSistema');
  const lblNombre = document.getElementById('bajaNombreSistema');
  const btnCancelar = document.getElementById('bajaCancelar');
  const btnConfirmar = document.getElementById('bajaConfirmar');
  const msg = document.getElementById('bajaMsg');

  function openModal(){
    msg.textContent = '';
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) { msg.textContent = 'Selecciona un sistema primero.'; return; }
    lblNombre.textContent = opt.textContent.trim();
    modal.classList.remove('hidden');
  }
  function closeModal(){ modal.classList.add('hidden'); }

  btnBaja?.addEventListener('click', openModal);
  btnCancelar?.addEventListener('click', closeModal);
  modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });

  btnConfirmar?.addEventListener('click', async () => {
    msg.textContent = '';
    const id = sel.value;
    if (!id) { msg.textContent = 'No hay sistema seleccionado.'; return; }

    try {
      const resp = await fetch('../app/appTiket.php?accion=DesactivarSistema', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({ id })
      });
      const data = await resp.json();
      if (!data || !data.ok) {
        msg.textContent = (data && data.error) ? data.error : 'No se pudo desactivar.';
        return;
      }

      // Estrategia 1: quitar del combo
      // sel.querySelector(`option[value="${CSS.escape(String(id))}"]`)?.remove();

      // Estrategia 2 (recomendada): marcar como inactivo y mover selección
      const opt = sel.querySelector(`option[value="${CSS.escape(String(id))}"]`);
      if (opt) {
        if (!/\(inactivo\)$/i.test(opt.textContent)) opt.textContent += ' (inactivo)';
        opt.disabled = true; // evita selección futura
      }
      // Mueve la selección a la primera opción válida
      const firstEnabled = Array.from(sel.options).find(o => o.value && !o.disabled);
      sel.value = firstEnabled ? firstEnabled.value : '';
      sel.dispatchEvent(new Event('change'));

      closeModal();
    } catch (e) {
      msg.textContent = 'Error de red o servidor.';
    }
  });
})();

</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
