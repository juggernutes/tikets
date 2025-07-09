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
    <form class="modal-content p-4" id="formEncuesta" method="POST" action="../app/appTiket.php?accion=calificarEncuesta&id_tiket=<?= htmlspecialchars($idTiket) ?>">
      <div class="modal-body text-center">
        <h5 class="mb-3">Cual a sido tu experiencia?</h5>
        <input type="hidden" name="calificacion" id="calificacion">

        <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <button type="button" class="btn btn-outline-danger btn-score" onclick="seleccionarCalificacion(<?= $i ?>)">
              <img src="../img/pulgar-rosarito-fondo-transparente.png" alt="Pulgar" style="width:56px;height:56px;">
              <span class="d-none">Calificar con <?= $i ?></span>
            </button>
          <?php endfor; ?>
        </div>
        <?php $calificacion = $calificacion ?? ''; ?>

        <h4 class="mb-4"><?php echo htmlspecialchars($calificacion); ?></h4>
        <div class="mb-3 text-start">
          <label for="comentarios" class="form-label"><strong>Algun comentario? (Optional)</strong></label>
          <textarea class="form-control" id="comentarios" name="comentarios" placeholder="Escribe tus comentarios aquí..." rows="3"></textarea>
        </div>

        <button type="submit" id="btnEnviar" class="btn btn-danger w-100" disabled>Enviar Comentarios</button>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap (si no está ya cargado globalmente) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  function seleccionarCalificacion(valor) {
    // Obtiene todos los botones de puntuación
    const botones = document.querySelectorAll('.btn-score');

    // Remueve la clase 'seleccionado' de todos los botones
    botones.forEach((btn, i) => {
      if (i < valor) {
        btn.classList.add('seleccionado');
      } else {
        btn.classList.remove('seleccionado');
      }
    });

    // Guarda el valor en el campo oculto
    document.getElementById('calificacion').value = valor;

    // (Opcional) Habilita el botón de enviar
    document.getElementById('btnEnviar').disabled = false;
  }
</script>