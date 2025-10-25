<?php
session_start();
if (!isset($_SESSION['login_id'])) {
  header("Location: ../public/index.php");
  exit;
}

$idTiket = $_GET['id_tiket'] ?? null;
if (!$idTiket) {
  echo "ID de ticket no válido.";
  exit;
}
?>
<!-- Modal de Encuesta -->
<div class="modal fade show d-block" id="modalEncuesta" tabindex="-1" aria-labelledby="modalEncuestaLabel" aria-modal="true" role="dialog" style="background-color: rgba(0,0,0,0.5);">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form class="modal-content p-4 shadow-lg rounded-3 border-0" id="formEncuesta" method="POST" action="../app/appTiket.php?accion=calificarEncuesta&id_tiket=<?= htmlspecialchars($idTiket) ?>">
      <div class="modal-body text-center">
        <h5 id="modalEncuestaLabel" class="mb-3 fw-bold">¿Cuál ha sido tu experiencia?</h5>
        <input type="hidden" name="calificacion" id="calificacion">

        <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <button type="button" class="btn btn-outline-danger btn-score d-flex flex-column align-items-center" onclick="seleccionarCalificacion(<?= $i ?>)" aria-label="Calificar con <?= $i ?>">
              <img src="../img/pulgar-rosarito-fondo-transparente.png" alt="Pulgar" style="width:56px;height:56px;">
              <small class="mt-1 text-muted"><?= $i ?></small>
            </button>
          <?php endfor; ?>
        </div>

        <div class="mb-3 text-start">
          <label for="comentarios" class="form-label fw-semibold">Sugerencias para mejorar el servicio <span class="text-muted">(Necesario)</span></label>
          <textarea class="form-control" id="comentarios" name="comentarios" placeholder="Escribe tus comentarios aquí..." rows="3"></textarea>
        </div>

        <button type="submit" id="btnEnviar" class="btn btn-danger w-100" disabled>Enviar comentarios</button>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
  .btn-score.seleccionado {
    background-color: #dc3545 !important;
    color: #fff !important;
    border-color: #dc3545 !important;
    transform: scale(1.05);
    transition: transform 0.2s ease-in-out;
  }
</style>

<script>
  function seleccionarCalificacion(valor) {
    const botones = document.querySelectorAll('.btn-score');

    botones.forEach((btn, i) => {
      if (i < valor) {
        btn.classList.add('seleccionado');

      } else {
        btn.classList.remove('seleccionado');
      }

      document.getElementById('calificacion').value = valor;
    validarFormulario();
    });
  }

    function validarFormulario() {
    const calificacion = document.getElementById('calificacion').value;
    const comentarios = document.getElementById('comentarios').value.trim();
    const btnEnviar = document.getElementById('btnEnviar');

    if (calificacion && comentarios.length > 0) {
      btnEnviar.disabled = false;
    } else {
      btnEnviar.disabled = true;
    }
  }

  // Detectar cambios en el textarea
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('comentarios').addEventListener('input', validarFormulario);
  })
</script>
