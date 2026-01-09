<?php
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
$semSel = isset($_GET['Semana']) ? (int)$_GET['Semana'] : (int)date('W');

$dt = new DateTime();
$dt->setISODate($anio, 53);
$totalSemanas = ($dt->format('W') === '53') ? 53 : 52;

for ($sem = 1; $sem <= $totalSemanas; $sem++) {
  $selected = ($sem === $semSel) ? 'selected' : '';
  echo "<option value=\"$sem\" $selected>Semana $sem</option>";
}
