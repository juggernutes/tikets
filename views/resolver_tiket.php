<?php
// resolver_ticket.php (vista)
include __DIR__ . '/../app/appTiket.php';

$idTiket = $_GET['id'] ?? null;
if (!$idTiket) {
  echo 'No se proporcionó un ticket.';
  exit;
}

$ticket = $tiketController->getTicketById($idTiket);
if (!$ticket) {
  echo 'No se encontró el ticket.';
  exit;
}

// Empleado que reportó el ticket
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

<link rel="stylesheet" href="../tools/resStyle.css">

<div class="contenedor-tiket">
  <!-- INFO DEL TICKET -->
  <div class="tiket-info">
    <h2>Resolver Ticket <span class="muted">#<?= htmlspecialchars($ticket['Folio']) ?></span></h2>
    <div class="meta">
      <p><strong>Sistema:</strong> <?= htmlspecialchars($ticket['SISTEMA']) ?></p>
      <p><strong>Fecha:</strong> <?= htmlspecialchars($ticket['FECHA']) ?></p>
      <p><strong>Estado:</strong>
        <?php
        $s   = strtolower($ticket['ESTADO'] ?? '');
        $cls = $s === 'abierto' ? 'b-abierto' : ($s === 'en proceso' ? 'b-proceso' : 'b-cerrado');
        ?>
        <span class="badge <?= $cls ?>"><?= htmlspecialchars($ticket['ESTADO']) ?></span>
      </p>
      <?php
      $descripcion = $ticket['DESCRIPCION'] ?? '';
      if (strpos((string)$descripcion, ' ') === false) {
        $descripcion = wordwrap($descripcion, 40, "\n", true);
      }
      ?>
      <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')) ?></p>

      <?php if ($empleado): ?>
        <div class="tarjeta-empleado">
          <h3>Datos del empleado</h3>
          <p><strong>Nombre:</strong> <?= htmlspecialchars($empleado['Nombre']   ?? $ticket['EMPLEADO']) ?></p>
          <p><strong>Puesto:</strong> <?= htmlspecialchars($empleado['Puesto']   ?? $ticket['PUESTO']) ?></p>
          <p><strong>Sucursal:</strong> <?= htmlspecialchars($empleado['Sucursal'] ?? $ticket['SUCURSAL']) ?></p>
          <?php if (!empty($empleado['Correo'])): ?>
            <p><strong>Correo:</strong> <?= htmlspecialchars($empleado['Correo']) ?></p>
          <?php endif; ?>
          <?php if (!empty($empleado['Telefono'])): ?>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($empleado['Telefono']) ?></p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- FORMULARIO DE SOLUCIÓN -->
  <div class="tiket-solucion">
    <form id="form-solucion" action="../app/appTiket.php?accion=solucionar&id_tiket=<?= urlencode((string)$ticket['ID_Tiket']) ?>" method="POST">
      <input type="hidden" name="id_tiket" value="<?= htmlspecialchars($ticket['ID_Tiket']) ?>">

      <div class="form-grid-2cols">
        <!-- ERROR -->
        <div class="form-row-2col">
          <div class="col-input">
            <label class="titulo" for="id_error">ERROR</label>
            <select name="id_error" id="id_error" required>
              <option value="">Selecciona un error</option>
              <?php include __DIR__ . '/../partials/combo_errores.php'; ?>
            </select>
          </div>
          <button type="button" class="icon-btn" id="btn-nuevo-error">+</button>
        </div>

        <!-- SOLUCION -->
        <div class="form-row-2col">
          <div class="col-input">
            <label class="titulo" for="id_solucion">SOLUCIÓN</label>
            <select name="id_solucion" id="id_solucion" required>
              <option value="">Selecciona una solución</option>
              <?php include __DIR__ . '/../partials/combo_soluciones.php'; ?>
            </select>
          </div>
          <button type="button" class="icon-btn" id="btn-nueva-solucion">+</button>
        </div>

        <!-- PROVEEDOR -->
        <div class="form-row-2col">
          <div class="col-input">
            <label class="titulo" for="id_proveedor">PROVEEDOR</label>
            <select name="id_proveedor" id="id_proveedor">
              <option value="">Enviar a proveedor</option>
              <?php include __DIR__ . '/../partials/combo_proveedor.php'; ?>
            </select>
          </div>
          <!-- De momento el + de proveedor no hace nada, se puede usar después -->
          <button type="button" class="icon-btn" id="btn-nuevo-proveedor">+</button>
        </div>
      </div>

      <!-- DESCRIPCIÓN (a todo el ancho) -->
      <div class="form-text-full">
        <label class="titulo" for="descripcion_solucion">Descripción de la solución</label>
        <textarea name="descripcion_solucion" id="descripcion_solucion" rows="7" maxlength="2000" required><?= htmlspecialchars($descripcionSolucion) ?></textarea>
        <small class="muted"><span id="count">0</span>/2000</small>
      </div>

      <div class="actions">
        <?php $idTiketUrl = urlencode((string)$ticket['ID_Tiket']); ?>
        <button
          type="submit"
          class="btn primary"
          id="btn-dinamico"
          formaction="../app/appTiket.php?accion=tiket.avance&id_tiket=<?= $idTiketUrl ?>"
          formmethod="POST">
          Dar seguimiento
        </button>
        <button type="submit" class="btn primary" id="btn-submit">
          Solucionar
        </button>
        <a class="btn" href="javascript:history.back()">Cancelar</a>
      </div>
    </form>
  </div>

  <!-- FORMULARIO PARA NUEVO ERROR -->
  <div id="formularioError" class="card-form-error oculto">
    <div class="field">
      <label class="titulo" for="nombreError">Nombre del error</label>
      <input type="text" id="nombreError" name="nombreError" class="input"
        placeholder="Ej. Error de conexión..." required>
    </div>
    <div class="actions">
      <button type="button" id="guardarError" class="btn primary">
        Guardar nuevo error
      </button>
    </div>
  </div>

  <!-- FORMULARIO PARA NUEVA SOLUCIÓN -->
  <div id="formularioSolucion" class="card-form-solucion oculto">
    <div class="field">
      <label class="titulo" for="nombreSolucion">Nombre de la solución</label>
      <input type="text" id="nombreSolucion" name="nombreSolucion" class="input"
        placeholder="Ej. Reiniciar servicio..." required>
    </div>
    <div class="actions">
      <button type="button" id="guardarSolucion" class="btn primary">
        Guardar nueva solución
      </button>
    </div>
  </div>
  <div>
    
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // ==============================
    // Preseleccionar valores de error y solución
    // ==============================
    const selErr = document.getElementById('id_error');
    const selSol = document.getElementById('id_solucion');
    const preErr = <?= json_encode((string)$selectedErrorId) ?>;
    const preSol = <?= json_encode((string)$selectedSolucionId) ?>;

    if (selErr && preErr) {
      selErr.value = preErr;
    }
    if (selSol && preSol) {
      selSol.value = preSol;
    }

    // ==============================
    // Contador de caracteres en descripción
    // ==============================
    const ta = document.getElementById('descripcion_solucion');
    const count = document.getElementById('count');

    if (ta && count) {
      const updateCount = () => {
        count.textContent = ta.value.length;
      };
      ta.addEventListener('input', updateCount);
      updateCount();
    }

    // ==============================
    // Evitar doble envío en "Solucionar"
    // ==============================
    const form = document.getElementById('form-solucion');
    const btnSubmit = document.getElementById('btn-submit');

    if (form && btnSubmit) {
      form.addEventListener('submit', function() {
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Guardando…';
      });
    }

    // ==============================
    // Botón dinámico según proveedor
    // ==============================
    const selProv = document.getElementById('id_proveedor');
    const btnDinamico = document.getElementById('btn-dinamico');

    function actualizarBotonProveedor() {
      if (!selProv || !btnDinamico) return;

      // Si hay un proveedor seleccionado (value != '')
      if (selProv.value) {
        const idproveedor = selProv.value;

        btnDinamico.textContent = 'Enviar a proveedor';
        btnDinamico.classList.remove('primary');
        btnDinamico.classList.add('danger');

        btnDinamico.setAttribute(
          'formaction',
          `../app/appTiket.php?accion=enviarProveedor&id_tiket=<?= $idTiketUrl ?>&id_proveedor=${idproveedor}`
        );
      } else {
        // Si NO hay proveedor
        btnDinamico.textContent = 'Dar seguimiento';
        btnDinamico.classList.remove('danger');
        btnDinamico.classList.add('primary');

        btnDinamico.setAttribute(
          'formaction',
          '../app/appTiket.php?accion=tiket.avance&id_tiket=<?= $idTiketUrl ?>'
        );
      }
    }

    if (selProv) {
      selProv.addEventListener('change', actualizarBotonProveedor);
      actualizarBotonProveedor(); // al cargar
    }

    // ==============================
    // Mostrar / ocultar formularios de nuevo error / solución
    // ==============================
    const btnNuevoError = document.getElementById('btn-nuevo-error');
    const btnNuevaSolucion = document.getElementById('btn-nueva-solucion');
    const formErrorContainer = document.getElementById('formularioError');
    const formSolContainer = document.getElementById('formularioSolucion');

    if (btnNuevoError && formErrorContainer) {
      btnNuevoError.addEventListener('click', function() {
        formErrorContainer.classList.toggle('oculto');
      });
    }

    if (btnNuevaSolucion && formSolContainer) {
      btnNuevaSolucion.addEventListener('click', function() {
        formSolContainer.classList.toggle('oculto');
      });
    }

    // ==============================
    // GUARDAR NUEVO ERROR (AJAX)
    // ==============================
    const btnGuardarError = document.getElementById('guardarError');
    const inputNombreError = document.getElementById('nombreError');
    const selectErrores = document.getElementById('id_error');

    if (btnGuardarError && inputNombreError && selectErrores && formErrorContainer) {
      btnGuardarError.addEventListener('click', async function() {
        const nombre = inputNombreError.value.trim();

        if (!nombre) {
          alert("El nombre del error es obligatorio.");
          return;
        }

        const params = new URLSearchParams({
          accion: 'nuevoError', // appTiket.php debe manejar este case
          nombre: nombre
        });

        try {
          const response = await fetch('../app/appTiket.php?' + params.toString(), {
            method: 'GET'
          });

          const result = await response.json();
          console.log('Respuesta nuevoError:', result);

          if (result.ok) {
            const nuevaOpcion = document.createElement('option');
            nuevaOpcion.value = result.id;
            nuevaOpcion.textContent = nombre;
            selectErrores.appendChild(nuevaOpcion);
            selectErrores.value = result.id;

            formErrorContainer.classList.add('oculto');
            inputNombreError.value = '';
          } else {
            alert(result.error || "Error al guardar el error.");
          }
        } catch (err) {
          console.error('Error en fetch nuevoError:', err);
          alert('Error de comunicación con el servidor.');
        }
      });
    }

    // ==============================
    // GUARDAR NUEVA SOLUCIÓN (AJAX)
    // ==============================
    const btnGuardarSolucion = document.getElementById('guardarSolucion');
    const inputNombreSolucion = document.getElementById('nombreSolucion');
    const selectSoluciones = document.getElementById('id_solucion');

    if (btnGuardarSolucion && inputNombreSolucion && selectSoluciones && formSolContainer) {
      btnGuardarSolucion.addEventListener('click', async function() {
        const nombre = inputNombreSolucion.value.trim();

        if (!nombre) {
          alert("El nombre de la solución es obligatorio.");
          return;
        }

        const params = new URLSearchParams({
          accion: 'nuevoSolucion', // appTiket.php debe manejar este case
          nombre: nombre
        });

        try {
          const response = await fetch('../app/appTiket.php?' + params.toString(), {
            method: 'GET'
          });

          const result = await response.json();
          console.log('Respuesta nuevoSolucion:', result);

          if (result.ok) {
            const nuevaOpcion = document.createElement('option');
            nuevaOpcion.value = result.id;
            nuevaOpcion.textContent = nombre;
            selectSoluciones.appendChild(nuevaOpcion);
            selectSoluciones.value = result.id;

            formSolContainer.classList.add('oculto');
            inputNombreSolucion.value = '';
          } else {
            alert(result.error || "Error al guardar la solución.");
          }
        } catch (err) {
          console.error('Error en fetch nuevoSolucion:', err);
          alert('Error de comunicación con el servidor.');
        }
      });
    }
  });
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>