<?php 
session_start();
require_once __DIR__ . '/../app/appTiket.php';

if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$IdUser = $_SESSION['login_id'];
$rol    = $_SESSION['rol'] ?? 'INVITADO';

// ================= Helpers =================
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function dash($v){ return ($v === null || $v === '') ? '—' : e($v); }
function fmt($s){
  if(!$s) return '—';
  $t = strtotime($s);
  return $t ? date('d/M/Y H:i', $t) : e($s);
}
function seconds_to_readable($secs){
  $secs = (int)$secs;
  $d = intdiv($secs, 86400); $secs %= 86400;
  $h = intdiv($secs, 3600);  $secs %= 3600;
  $m = intdiv($secs, 60);    $s = $secs % 60;
  $parts = [];
  if($d) $parts[] = $d.'d';
  if($h || $d) $parts[] = $h.'h';
  if($m || $h || $d) $parts[] = $m.'m';
  $parts[] = $s.'s';
  return implode(' ', $parts);
}

// ================= Horario hábil =================
// Cambia aquí la configuración laboral
const WORK_START_HOUR = 7;   // 09:00
const WORK_END_HOUR   = 18;  // 18:00 (exclusivo)
const WORK_DAYS       = [1,2,3,4,5]; // 1=Lunes ... 7=Domingo (sábado incluído)
$FERIADOS = [
  // '2025-01-01',
  // '2025-02-05',
];

/**
 * Segundos hábiles entre $ini y $fin.
 * Respeta WORK_DAYS, ventana WORK_START_HOUR..WORK_END_HOUR y $FERIADOS.
 * Devuelve 0 si fin <= ini. Devuelve null si fechas inválidas.
 */
function business_seconds_between($ini, $fin, array $feriados){
  if(!$ini || !$fin) return null;
  $start = strtotime($ini);
  $end   = strtotime($fin);
  if(!$start || !$end) return null;
  if($end <= $start) return 0;

  $tz = new DateTimeZone(date_default_timezone_get());
  $cur   = (new DateTime('@'.$start))->setTimezone($tz);
  $endDT = (new DateTime('@'.$end))->setTimezone($tz);

  $total = 0;
  while($cur < $endDT){
    $y = $cur->format('Y');
    $m = $cur->format('m');
    $d = $cur->format('d');

    $dayStart = (new DateTime("$y-$m-$d ".WORK_START_HOUR.":00:00", $tz));
    $dayEnd   = (new DateTime("$y-$m-$d ".WORK_END_HOUR.":00:00", $tz));

    $dow = (int)$cur->format('N'); // 1..7
    $isHoliday = in_array($cur->format('Y-m-d'), $feriados, true);
    $isWorkday = in_array($dow, WORK_DAYS, true) && !$isHoliday;

    $segStart = max($cur->getTimestamp(), $dayStart->getTimestamp());
    $segEnd   = min($endDT->getTimestamp(), $dayEnd->getTimestamp());

    if($isWorkday && $segEnd > $segStart){
      $total += ($segEnd - $segStart);
    }

    // Salta al siguiente día (00:00) y luego se recalcula ventana
    $cur = (clone $dayStart)->modify('+1 day');
  }

  return max(0, $total);
}

// ================= Filtros GET =================
$q      = trim($_GET['q']      ?? '');
$desde  = trim($_GET['desde']  ?? ''); // yyyy-mm-dd
$hasta  = trim($_GET['hasta']  ?? ''); // yyyy-mm-dd
$per    = max(5, min(100, (int)($_GET['per'] ?? 25)));
$page   = max(1, (int)($_GET['page'] ?? 1));
$mesSel = trim($_GET['mes']    ?? ''); // yyyy-mm

// Si no viene mes, usar el mes actual (local del servidor)
if ($mesSel === '') { $mesSel = date('Y-m'); }
// Límites del mes seleccionado
try {
  $monthStart = (new DateTime($mesSel.'-01 00:00:00'))->format('Y-m-d H:i:s');
  $monthEnd   = (new DateTime($mesSel.'-01 00:00:00'))->modify('last day of this month 23:59:59')->format('Y-m-d H:i:s');
} catch(Throwable $e){
  // fallback por si llega un mes inválido
  $mesSel     = date('Y-m');
  $monthStart = date('Y-m-01 00:00:00');
  $monthEnd   = date('Y-m-t 23:59:59');
}

$title = 'TICKETS CERRADOS';
include __DIR__ . '/layout/header.php';

// ================= Datos base =================
$ticketsResult = $tiketController->getTicketsCerrados($IdUser, $rol);

// Pasar a array
$rows = [];
if ($ticketsResult && $ticketsResult->num_rows > 0) {
    while ($r = $ticketsResult->fetch_assoc()) { $rows[] = $r; }
}

// ================= Filtrado general en memoria =================
$rows = array_values(array_filter($rows, function($r) use ($q, $desde, $hasta){
    // Búsqueda libre
    if ($q !== '') {
        $needle = mb_strtolower($q, 'UTF-8');
        $hay = mb_strtolower(implode(' ', [
            $r['Folio'] ?? '', $r['SISTEMA'] ?? '', $r['DESCRIPCION'] ?? '', $r['EMPLEADO'] ?? '',
            $r['PUESTO'] ?? '', $r['SUCURSAL'] ?? '', $r['NOMBRE_SOPORTE'] ?? '', $r['ERROR'] ?? '', $r['SOLUCION'] ?? ''
        ]), 'UTF-8');
        if (mb_strpos($hay, $needle) === false) return false;
    }
    // Rango de fechas para la tabla: prioriza FECHA_SOLUCION; si no, FECHA
    $fecha = $r['FECHA_SOLUCION'] ?? ($r['FECHA'] ?? '');
    if ($fecha && ($desde || $hasta)) {
        $ts = strtotime($fecha);
        if ($desde && $ts < strtotime($desde.' 00:00:00')) return false;
        if ($hasta && $ts > strtotime($hasta.' 23:59:59')) return false;
    }
    return true;
}));

// ================= KPIs GLOBALES (no dependen de $mesSel) =================
$totalTicketsFiltrados = count($rows);

$totalSegsResolGlobal = 0; // FECHA -> FECHA_SOLUCION (HÁBILES)
$totalSegsAsignGlobal = 0; // FECHA -> FECHA_ASIGNACION (HÁBILES)
$cntResolGlobal = 0; 
$cntAsignGlobal = 0;

// Para "Promedio tickets/día" global en función de la ventana real de datos
$minCreacionTs = PHP_INT_MAX;
$maxCreacionTs = 0;

foreach($rows as $r){
  $crea   = $r['FECHA'] ?? null;
  $asig   = $r['FECHA_ASIGNACION'] ?? null;
  $cierra = $r['FECHA_SOLUCION'] ?? null;

  // ventana de fechas para promedio por día
  if($crea){
    $ts = strtotime($crea);
    if($ts){ 
      if($ts < $minCreacionTs) $minCreacionTs = $ts;
      if($ts > $maxCreacionTs) $maxCreacionTs = $ts;
    }
  }

  // resolución (hábiles)
  if($crea && $cierra){
    $secs = business_seconds_between($crea, $cierra, $FERIADOS);
    if($secs !== null){ $totalSegsResolGlobal += $secs; $cntResolGlobal++; }
  }

  // hasta asignación (hábiles)
  if($crea && $asig){
    $secsA = business_seconds_between($crea, $asig, $FERIADOS);
    if($secsA !== null){ $totalSegsAsignGlobal += $secsA; $cntAsignGlobal++; }
  }
}

// Promedios globales
$promResolSegsGlobal = $cntResolGlobal ? (int)($totalSegsResolGlobal / $cntResolGlobal) : 0;
$promAsignSegsGlobal = $cntAsignGlobal ? (int)($totalSegsAsignGlobal / $cntAsignGlobal) : 0;

// Promedio tickets/día global según ventana de creación
if($minCreacionTs === PHP_INT_MAX || $maxCreacionTs === 0){
  $promTicketsDiaGlobal = 0;
} else {
  $diasVentana = max(1, (int)floor(($maxCreacionTs - $minCreacionTs) / 86400) + 1);
  $promTicketsDiaGlobal = $totalTicketsFiltrados / $diasVentana;
}

// ================= Métricas por MES (hábiles) =================
// ================= Métricas por MES (hábiles, PRORRATEADAS por día) =================

// Inicializa buckets por día del mes
$idxDia = []; // y-m-d => ['abiertas'=>0, 'resueltas'=>0, 'sum_resol_secs'=>0]
$dtCur  = new DateTime($monthStart);
$dtEnd  = new DateTime($monthEnd);
for($d = clone $dtCur; $d <= $dtEnd; $d->modify('+1 day')){
  $idxDia[$d->format('Y-m-d')] = ['abiertas'=>0,'resueltas'=>0,'sum_resol_secs'=>0];
}

$abiertasMes  = 0;
$resueltasMes = 0;
$sumResolMes  = 0;

foreach($rows as $r){
  $crea   = $r['FECHA'] ?? null;
  $cierra = $r['FECHA_SOLUCION'] ?? null;
  if(!$crea || !$cierra) {
    // aún así cuenta “abiertas del mes” si se creó dentro del mes
    if($crea){
      $tsCrea = strtotime($crea);
      if($tsCrea >= strtotime($monthStart) && $tsCrea <= strtotime($monthEnd)){
        $key = date('Y-m-d', $tsCrea);
        if(isset($idxDia[$key])){ $idxDia[$key]['abiertas']++; $abiertasMes++; }
      }
    }
    continue;
  }

  $tsCrea   = strtotime($crea);
  $tsCierra = strtotime($cierra);

  // 1) Contabiliza “abiertas” si la creación está dentro del mes
  if($tsCrea >= strtotime($monthStart) && $tsCrea <= strtotime($monthEnd)){
    $keyC = date('Y-m-d', $tsCrea);
    if(isset($idxDia[$keyC])){ $idxDia[$keyC]['abiertas']++; $abiertasMes++; }
  }

  // 2) Contabiliza “resueltas” si el cierre está dentro del mes
  $resueltaEnMes = false;
  if($tsCierra >= strtotime($monthStart) && $tsCierra <= strtotime($monthEnd)){
    $keyR = date('Y-m-d', $tsCierra);
    if(isset($idxDia[$keyR])){ $idxDia[$keyR]['resueltas']++; $resueltasMes++; $resueltaEnMes = true; }
  }

  // 3) PRORRATEO por día: recorta al mes y reparte por cada día
  $ini = max($tsCrea,   strtotime($monthStart));
  $fin = min($tsCierra, strtotime($monthEnd));
  if($fin <= $ini) continue; // nada que sumar en el mes

  $tz = new DateTimeZone(date_default_timezone_get());
  $cur = (new DateTime('@'.$ini))->setTimezone($tz);
  $end = (new DateTime('@'.$fin))->setTimezone($tz);

  while($cur <= $end){
    // Ventana del día (del calendario)
    $dayYmd   = $cur->format('Y-m-d');
    $dayStart = new DateTime($dayYmd.' 00:00:00', $tz);
    $dayEnd   = new DateTime($dayYmd.' 23:59:59', $tz);

    // Intersección del intervalo ticket con este día
    $segStart = max($cur->getTimestamp(), $dayStart->getTimestamp(), $ini);
    $segEnd   = min($end->getTimestamp(), $dayEnd->getTimestamp(), $fin);

    if($segEnd > $segStart && isset($idxDia[$dayYmd])){
      // Sumar SOLO horas hábiles dentro de [segStart, segEnd]
      $secsH_day = business_seconds_between(
        date('Y-m-d H:i:s', $segStart),
        date('Y-m-d H:i:s', $segEnd),
        $FERIADOS
      );
      if($secsH_day !== null){
        $idxDia[$dayYmd]['sum_resol_secs'] += $secsH_day;
        $sumResolMes += $secsH_day;
      }
    }

    // Avanza un día
    $cur = (clone $dayStart)->modify('+1 day');
  }
}

// Ratio mensual solicitado: (Incidencias Resueltas / Incidencias Abiertas)
$ratioMes = ($abiertasMes > 0) ? ($resueltasMes / $abiertasMes) : 0;

// Para KPI "Promedio hasta asignación (hábiles)" dentro del mes
$sumAsignMes = 0; 
$cntAsignMes = 0;
foreach($rows as $r){
  $crea = $r['FECHA'] ?? null;
  $asig = $r['FECHA_ASIGNACION'] ?? null;
  if($crea && $asig){
    $ts = strtotime($crea);
    if($ts >= strtotime($monthStart) && $ts <= strtotime($monthEnd)){
      $segH = business_seconds_between($crea, $asig, $FERIADOS);
      if($segH !== null){
        $sumAsignMes += $segH;
        $cntAsignMes++;
      }
    }
  }
}

// ================= Desglose diario del mes (para la tabla) =================
$tablaMes = []; // orden descendente por fecha
krsort($idxDia);
foreach($idxDia as $fecha => $vals){
  $ab  = (int)$vals['abiertas'];
  $re  = (int)$vals['resueltas'];
  $sum = (int)$vals['sum_resol_secs'];

  // 2) Suma de horas de resolución / Incidencias del día (abiertas)
  $prom2  = ($ab > 0) ? (int)($sum / $ab) : null;
  // 2a) Suma de horas de resolución / Incidencias resueltas del día
  $prom2a = ($re > 0) ? (int)($sum / $re) : null;

  $tablaMes[] = [
    'fecha' => $fecha,
    'abiertas' => $ab,
    'resueltas' => $re,
    'sum_resol' => $sum,
    'prom2'  => $prom2,
    'prom2a' => $prom2a,
  ];
}

// ================= Orden/paginación para la tabla general =================
usort($rows, function($a,$b){
    $fa = strtotime($a['FECHA_SOLUCION'] ?? $a['FECHA'] ?? '1970-01-01');
    $fb = strtotime($b['FECHA_SOLUCION'] ?? $b['FECHA'] ?? '1970-01-01');
    return $fb <=> $fa; // descendente
});
$total_rows  = count($rows);
$total_pages = max(1, (int)ceil($total_rows / $per));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per;
$view        = array_slice($rows, $offset, $per);

// ================= CSS (cache bust) =================
$cssPath = __DIR__ . '/../tools/ticketCerradosStyle.css';
$cssVer  = is_file($cssPath) ? filemtime($cssPath) : time();

?>
<link rel="stylesheet" href="../tools/ticketCerradosStyle.css?v=<?= $cssVer ?>">

<<!-- ======= KPIs GLOBALES (no cambian con el mes) ======= -->
<div class="container">
  <div class="card card--fluid">
    <div class="cardKpis">
      <div class="kpi">
        <div class="kpi-label">Tickets (con filtros)</div>
        <div class="kpi-value"><?= number_format($totalTicketsFiltrados) ?></div>
      </div>

      <div class="kpi">
        <div class="kpi-label">Tiempo total de resolución (hábiles)</div>
        <div class="kpi-value"><?= seconds_to_readable($totalSegsResolGlobal) ?></div>
      </div>

      <div class="kpi">
        <div class="kpi-label">Promedio por ticket (resolución hábil)</div>
        <div class="kpi-value"><?= $cntResolGlobal ? seconds_to_readable($promResolSegsGlobal) : '—' ?></div>
      </div>

      <div class="kpi">
        <div class="kpi-label">Promedio tickets/día (global)</div>
        <div class="kpi-value"><?= $promTicketsDiaGlobal ? number_format($promTicketsDiaGlobal, 2) : '—' ?></div>
      </div>

      <div class="kpi">
        <div class="kpi-label">Promedio hasta asignación (hábiles)</div>
        <div class="kpi-value"><?= $cntAsignGlobal ? seconds_to_readable($promAsignSegsGlobal) : '—' ?></div>
      </div>
    </div>
  </div>
</div>


<!-- ======= Resumen del Mes + Selector ======= -->
<div class="container">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Resumen del mes</h2>
      <form class="toolbar" method="get" style="margin:0;">
        <!-- preserva filtros actuales en el submit -->
        <input type="hidden" name="q"     value="<?= e($q) ?>">
        <input type="hidden" name="desde" value="<?= e($desde) ?>">
        <input type="hidden" name="hasta" value="<?= e($hasta) ?>">
        <div class="field">
          <label for="mes">Mes</label>
          <input type="month" id="mes" name="mes" value="<?= e($mesSel) ?>">
        </div>
        <div class="field">
          <button type="submit">Aplicar</button>
        </div>
      </form>
    </div>

    <div class="cardKpis">
      <div class="kpi">
        <div class="kpi-label">Mes seleccionado</div>
        <div class="kpi-value">
          <?php
            $dtShow = DateTime::createFromFormat('Y-m', $mesSel);
            echo $dtShow ? e(strftime('%b/%Y', $dtShow->getTimestamp())) : e($mesSel);
          ?>
        </div>
      </div>
      <div class="kpi">
        <div class="kpi-label">Incidencias abiertas en el mes</div>
        <div class="kpi-value"><?= number_format($abiertasMes) ?></div>
      </div>
      <div class="kpi">
        <div class="kpi-label">Incidencias resueltas en el mes</div>
        <div class="kpi-value"><?= number_format($resueltasMes) ?></div>
      </div>
      <div class="kpi">
        <div class="kpi-label">Suma horas de resolución (mes, hábiles)</div>
        <div class="kpi-value"><?= seconds_to_readable($sumResolMes) ?></div>
      </div>
      <div class="kpi">
        <div class="kpi-label">Resueltas / Abiertas (mes)</div>
        <div class="kpi-value"><?= ($abiertasMes>0) ? number_format($ratioMes, 2) : '—' ?></div>
      </div>
    </div>

    <!-- Desglose diario del mes con métricas 2 y 2a -->
    <div class="day-table-wrap">
      <table class="day-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Abiertas del día</th>
            <th>Resueltas del día</th>
            <th>Suma horas de resolución (hábiles)</th>
            <th>Prom. (horas/abiertas) [2]</th>
            <th>Prom. (horas/resueltas) [2a]</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($tablaMes)): ?>
            <tr><td class="empty" colspan="6">Sin datos para el mes seleccionado.</td></tr>
          <?php else: ?>
            <?php foreach($tablaMes as $r): ?>
              <tr>
                <td data-label="Fecha"><?= e(date('d/M/Y', strtotime($r['fecha']))) ?></td>
                <td data-label="Abiertas del día"><?= number_format($r['abiertas']) ?></td>
                <td data-label="Resueltas del día"><?= number_format($r['resueltas']) ?></td>
                <td data-label="Suma horas de resolución"><?= seconds_to_readable($r['sum_resol']) ?></td>
                <td data-label="Prom. (horas/abiertas)">
                  <?= ($r['prom2'] !== null) ? seconds_to_readable($r['prom2']) : '—' ?>
                </td>
                <td data-label="Prom. (horas/resueltas)">
                  <?= ($r['prom2a'] !== null) ? seconds_to_readable($r['prom2a']) : '—' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ======= Tabla general de tickets ======= -->
<div class="container">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Historial de tickets cerrados</h2>
      <div class="tools-right">
        <button class="copy" type="button" onclick="copiarURL()">Copiar URL</button>
        <button class="export" type="button" onclick="exportCSV()">Exportar CSV</button>
      </div>
    </div>

    <form class="toolbar" method="get">
      <div class="field">
        <label for="q">Buscar</label>
        <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="folio, sistema, empleado, soporte…">
      </div>
      <div class="field">
        <label for="desde">Desde</label>
        <input type="date" id="desde" name="desde" value="<?= e($desde) ?>">
      </div>
      <div class="field">
        <label for="hasta">Hasta</label>
        <input type="date" id="hasta" name="hasta" value="<?= e($hasta) ?>">
      </div>
      <div class="field">
        <label for="per">Por página</label>
        <select id="per" name="per">
          <?php foreach([10,25,50,100,500] as $n): ?>
            <option value="<?= $n ?>" <?= $per==$n?'selected':'' ?>><?= $n ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="mes">Mes (métricas)</label>
        <input type="month" id="mes" name="mes" value="<?= e($mesSel) ?>">
      </div>
      <div class="field">
        <button type="submit">Aplicar</button>
      </div>
      <a class="reset" href="?">Limpiar</a>
    </form>

    <div class="table-wrap">
      <table class="tickets-table">
        <thead>
          <tr>
            <th>Folio</th>
            <th>Estado</th>
            <th>Creación</th>
            <th>Asignación</th>
            <th>Resolución</th>
            <th>Sistema</th>
            <th>Descripción</th>
            <th>Empleado</th>
            <th>Puesto</th>
            <th>Sucursal</th>
            <th>Soporte</th>
            <th>Error</th>
            <th>Solución</th>
            <th>Descripción solución</th>
            <th>Tiempo de asignación (hábil)</th>
            <th>Tiempo de solución (hábil)</th>
            <th>Calificación</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($view)): ?>
            <tr><td class="empty" colspan="17">No hay tickets con los filtros actuales.</td></tr>
          <?php else: ?>
            <?php foreach ($view as $row): ?>
              <tr class="ticket-row" data-tiket="<?= e($row['ID_Tiket'] ?? '') ?>" style="cursor:pointer">
                <td class="mono" data-label="Folio"><?= dash($row['Folio'] ?? '') ?></td>
                <td data-label="Estado"><?= dash($row['ESTADO'] ?? '') ?></td>
                <td data-label="Creación"><?= fmt($row['FECHA'] ?? '') ?></td>
                <td data-label="Asignación"><?= fmt($row['FECHA_ASIGNACION'] ?? '') ?></td>
                <td data-label="Resolución"><?= fmt($row['FECHA_SOLUCION'] ?? '') ?></td>
                <td data-label="Sistema"><?= dash($row['SISTEMA'] ?? '') ?></td>
                <td data-label="Descripción"><?= dash($row['DESCRIPCION'] ?? '') ?></td>
                <td data-label="Empleado"><?= dash($row['EMPLEADO'] ?? '') ?></td>
                <td data-label="Puesto"><?= dash($row['PUESTO'] ?? '') ?></td>
                <td data-label="Sucursal"><?= dash($row['SUCURSAL'] ?? '') ?></td>
                <td data-label="Soporte"><?= dash($row['NOMBRE_SOPORTE'] ?? '') ?></td>
                <td data-label="Error"><?= dash($row['ERROR'] ?? '') ?></td>
                <td data-label="Solución"><?= dash($row['SOLUCION'] ?? '') ?></td>
                <td data-label="Descripción solución"><?= dash($row['DESCRIPCION_SOLUCION'] ?? '') ?></td>
                <td data-label="Tiempo de asignación">
                  <?php
                    $secsA = business_seconds_between($row['FECHA'] ?? '', $row['FECHA_ASIGNACION'] ?? '', $FERIADOS);
                    echo ($secsA === null) ? '—' : seconds_to_readable($secsA);
                  ?>
                </td>
                <td data-label="Tiempo de solución">
                  <?php
                    $secsR = business_seconds_between($row['FECHA'] ?? '', $row['FECHA_SOLUCION'] ?? '', $FERIADOS);
                    echo ($secsR === null) ? '—' : seconds_to_readable($secsR);
                  ?>
                </td>
                <td data-label="Calificación"><?= dash($row['CALIFICACION'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="pager">
      <?php if($total_pages > 1):
        $qs = $_GET; unset($qs['page']); $base = '?' . http_build_query($qs);
        $pmin = max(1, $page-2); $pmax = min($total_pages, $page+2);
      ?>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=1">«</a>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= max(1,$page-1) ?>">‹</a>
        <?php for($p=$pmin;$p<= $pmax;$p++): ?>
          <?php if($p == $page): ?>
            <span class="active"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= $p ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= min($total_pages,$page+1) ?>">›</a>
        <a href="<?= $base . ($base==='?'?'':'&') ?>page=<?= $total_pages ?>">»</a>
      <?php else: ?>
        <span class="muted">Total: <?= $total_rows ?> registros</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.ticket-row').forEach(function(row){
      row.addEventListener('click', function(){
        const id = this.getAttribute('data-tiket');
        if(id){ window.location.href = 'detalles_tiket.php?ID_Tiket=' + encodeURIComponent(id); }
      });
    });
  });

  function copiarURL(){
    navigator.clipboard.writeText(window.location.href);
  }

  function exportCSV(){
    const table = document.querySelector('.tickets-table');
    if(!table) return;

    let csv = [];
    csv.push('\ufeff'); // BOM para Excel

    table.querySelectorAll('tr').forEach(tr=>{
      let row=[];
      tr.querySelectorAll('th,td').forEach(cell=>{
        let text = cell.innerText.replaceAll('\n',' ').replaceAll('"','""').trim();
        row.push('"' + text + '"');
      });
      csv.push(row.join(','));
    });

    const blob = new Blob([csv.join('\n')], {type:'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'tickets_cerrados.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
