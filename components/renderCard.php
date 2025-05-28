<?php
function renderCard($titulo, $campos = [], $botonTexto = '', $botonLink = '') {
    echo '<div class="tarjeta-ticket">';
    echo "<h3>$titulo</h3>";
    foreach ($campos as $label => $valor) {
        echo "<p><strong>$label:</strong> " . nl2br(htmlspecialchars($valor)) . "</p>";
    }
    if ($botonTexto && $botonLink) {
        echo "<a href=\"$botonLink\" class=\"boton-ver\">$botonTexto</a>";
    }
    echo '</div>';
}
?>
