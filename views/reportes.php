<?php
session_start();
require_once __DIR__ . '/../app/appTiket.php';

if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$IdUser = $_SESSION['login_id'];
$rol    = $_SESSION['rol'] ?? 'INVITADO';

/* ================= Helpers ================= */
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function seconds_to_readable($secs) {
    $secs = (int)$secs;
    $d = intdiv($secs, 86400);
    $secs %= 86400;
    $h = intdiv($secs, 3600);
    $secs %= 3600;
    $m = intdiv($secs, 60);
    $s = $secs % 60;
    $parts = [];
    if ($d) $parts[] = $d . 'd';
    if ($h || $d) $parts[] = $h . 'h';
    if ($m || $h || $d) $parts[] = $m . 'm';
    $parts[] = $s . 's';
    return implode(' ', $parts);
}

/* ================= Horario hábil ================= */
const WORK_START_HOUR = 7;
const WORK_END_HOUR   = 18;
const WORK_DAYS       = [1, 2, 3, 4, 5]; // Lunes–Viernes

$FERIADOS = [
    // '2025-01-01',
    // '2025-02-05',
];

function business_seconds_between($ini, $fin, array $feriados) {
    if (!$ini || !$fin) return null;
    $start = strtotime($ini);
    $end   = strtotime($fin);
    if (!$start || !$end) return null;
    if ($end <= $start) return 0;

    $tz = new DateTimeZone(date_default_timezone_get());
    $cur   = (new DateTime('@' . $start))->setTimezone($tz);
    $endDT = (new DateTime('@' . $end))->setTimezone($tz);

    $total = 0;
    while ($cur < $endDT) {
        $y = $cur->format('Y');
        $m = $cur->format('m');
        $d = $cur->format('d');

        $dayStart = (new DateTime("$y-$m-$d " . WORK_START_HOUR . ":00:00", $tz));
        $dayEnd   = (new DateTime("$y-$m-$d " . WORK_END_HOUR   . ":00:00", $tz));

        $dow       = (int)$cur->format('N'); // 1..7
        $isHoliday = in_array($cur->format('Y-m-d'), $feriados, true);
        $isWorkday = in_array($dow, WORK_DAYS, true) && !$isHoliday;

        $segStart = max($cur->getTimestamp(), $dayStart->getTimestamp());
        $segEnd   = min($endDT->getTimestamp(), $dayEnd->getTimestamp());

        if ($isWorkday && $segEnd > $segStart) {
            $total += ($segEnd - $segStart);
        }

        $cur = (clone $dayStart)->modify('+1 day');
    }

    return max(0, $total);
}

/* ================= Filtros GET ================= */
$q      = trim($_GET['q']      ?? '');
$desde  = trim($_GET['desde']  ?? ''); // yyyy-mm-dd
$hasta  = trim($_GET['hasta']  ?? ''); // yyyy-mm-dd
$mesSel = trim($_GET['mes']    ?? ''); // yyyy-mm

if ($mesSel === '') {
    $mesSel = date('Y-m');
}
try {
    $monthStart = (new DateTime($mesSel . '-01 00:00:00'))->format('Y-m-d H:i:s');
    $monthEnd   = (new DateTime($mesSel . '-01 00:00:00'))
                    ->modify('last day of this month 23:59:59')
                    ->format('Y-m-d H:i:s');
} catch (Throwable $e) {
    $mesSel     = date('Y-m');
    $monthStart = date('Y-m-01 00:00:00');
    $monthEnd   = date('Y-m-t 23:59:59');
}

/* ================= Datos base ================= */
$ticketsResult = $tiketController->getTicketsCerrados($IdUser, $rol);

$rows = [];
if ($ticketsResult && $ticketsResult->num_rows > 0) {
    while ($r = $ticketsResult->fetch_assoc()) {
        $rows[] = $r;
    }
}

/* ===== Filtrado general (igual que en historial) ===== */
$rows = array_values(array_filter($rows, function ($r) use ($q, $desde, $hasta) {
    if ($q !== '') {
        $needle = mb_strtolower($q, 'UTF-8');
        $hay = mb_strtolower(implode(' ', [
            $r['Folio']          ?? '',
            $r['SISTEMA']        ?? '',
            $r['DESCRIPCION']    ?? '',
            $r['EMPLEADO']       ?? '',
            $r['PUESTO']         ?? '',
            $r['SUCURSAL']       ?? '',
            $r['NOMBRE_SOPORTE'] ?? '',
            $r['ERROR']          ?? '',
            $r['SOLUCION']       ?? ''
        ]), 'UTF-8');
        if (mb_strpos($hay, $needle) === false) return false;
    }

    $fecha = $r['FECHA_SOLUCION'] ?? ($r['FECHA'] ?? '');
    if ($fecha && ($desde || $hasta)) {
        $ts = strtotime($fecha);
        if ($desde && $ts < strtotime($desde . ' 00:00:00')) return false;
        if ($hasta && $ts > strtotime($hasta . ' 23:59:59')) return false;
    }
    return true;
}));

/* ================= KPIs globales ================= */
$totalTicketsFiltrados = count($rows);

$totalSegsResolGlobal = 0;
$totalSegsAsignGlobal = 0;
$cntResolGlobal       = 0;
$cntAsignGlobal       = 0;

$minCreacionTs = PHP_INT_MAX;
$maxCreacionTs = 0;

foreach ($rows as $r) {
    $crea   = $r['FECHA'] ?? null;
    $asig   = $r['FECHA_ASIGNACION'] ?? null;
    $cierra = $r['FECHA_SOLUCION'] ?? null;

    if ($crea) {
        $ts = strtotime($crea);
        if ($ts) {
            if ($ts < $minCreacionTs) $minCreacionTs = $ts;
            if ($ts > $maxCreacionTs) $maxCreacionTs = $ts;
        }
    }

    if ($crea && $cierra) {
        $secs = business_seconds_between($crea, $cierra, $FERIADOS);
        if ($secs !== null) {
            $totalSegsResolGlobal += $secs;
            $cntResolGlobal++;
        }
    }

    if ($crea && $asig) {
        $secsA = business_seconds_between($crea, $asig, $FERIADOS);
        if ($secsA !== null) {
            $totalSegsAsignGlobal += $secsA;
            $cntAsignGlobal++;
        }
    }
}

$promResolSegsGlobal = $cntResolGlobal ? (int)($totalSegsResolGlobal / $cntResolGlobal) : 0;
$promAsignSegsGlobal = $cntAsignGlobal ? (int)($totalSegsAsignGlobal / $cntAsignGlobal) : 0;

if ($minCreacionTs === PHP_INT_MAX || $maxCreacionTs === 0) {
    $promTicketsDiaGlobal = 0;
} else {
    $diasVentana          = max(1, (int)floor(($maxCreacionTs - $minCreacionTs) / 86400) + 1);
    $promTicketsDiaGlobal = $totalTicketsFiltrados / $diasVentana;
}

/* ================= Métricas por mes ================= */
$idxDia = [];
$dtCur  = new DateTime($monthStart);
$dtEnd  = new DateTime($monthEnd);
for ($d = clone $dtCur; $d <= $dtEnd; $d->modify('+1 day')) {
    $idxDia[$d->format('Y-m-d')] = [
        'abiertas'      => 0,
        'resueltas'     => 0,
        'sum_resol_secs'=> 0
    ];
}

$abiertasMes  = 0;
$resueltasMes = 0;
$sumResolMes  = 0;

foreach ($rows as $r) {
    $crea   = $r['FECHA'] ?? null;
    $cierra = $r['FECHA_SOLUCION'] ?? null;

    if (!$crea || !$cierra) {
        if ($crea) {
            $tsCrea = strtotime($crea);
            if ($tsCrea >= strtotime($monthStart) && $tsCrea <= strtotime($monthEnd)) {
                $key = date('Y-m-d', $tsCrea);
                if (isset($idxDia[$key])) {
                    $idxDia[$key]['abiertas']++;
                    $abiertasMes++;
                }
            }
        }
        continue;
    }

    $tsCrea   = strtotime($crea);
    $tsCierra = strtotime($cierra);

    if ($tsCrea >= strtotime($monthStart) && $tsCrea <= strtotime($monthEnd)) {
        $keyC = date('Y-m-d', $tsCrea);
        if (isset($idxDia[$keyC])) {
            $idxDia[$keyC]['abiertas']++;
            $abiertasMes++;
        }
    }

    if ($tsCierra >= strtotime($monthStart) && $tsCierra <= strtotime($monthEnd)) {
        $keyR = date('Y-m-d', $tsCierra);
        if (isset($idxDia[$keyR])) {
            $idxDia[$keyR]['resueltas']++;
            $resueltasMes++;
        }
    }

    $ini = max($tsCrea,   strtotime($monthStart));
    $fin = min($tsCierra, strtotime($monthEnd));
    if ($fin <= $ini) continue;

    $tz  = new DateTimeZone(date_default_timezone_get());
    $cur = (new DateTime('@' . $ini))->setTimezone($tz);
    $end = (new DateTime('@' . $fin))->setTimezone($tz);

    while ($cur <= $end) {
        $dayYmd   = $cur->format('Y-m-d');
        $dayStart = new DateTime($dayYmd . ' 00:00:00', $tz);
        $dayEnd   = new DateTime($dayYmd . ' 23:59:59', $tz);

        $segStart = max($cur->getTimestamp(), $dayStart->getTimestamp(), $ini);
        $segEnd   = min($end->getTimestamp(), $dayEnd->getTimestamp(), $fin);

        if ($segEnd > $segStart && isset($idxDia[$dayYmd])) {
            $secsH_day = business_seconds_between(
                date('Y-m-d H:i:s', $segStart),
                date('Y-m-d H:i:s', $segEnd),
                $FERIADOS
            );
            if ($secsH_day !== null) {
                $idxDia[$dayYmd]['sum_resol_secs'] += $secsH_day;
                $sumResolMes += $secsH_day;
            }
        }

        $cur = (clone $dayStart)->modify('+1 day');
    }
}

$ratioMes = ($abiertasMes > 0) ? ($resueltasMes / $abiertasMes) : 0;

$tablaMes = [];
krsort($idxDia);
foreach ($idxDia as $fecha => $vals) {
    $ab  = (int)$vals['abiertas'];
    $re  = (int)$vals['resueltas'];
    $sum = (int)$vals['sum_resol_secs'];

    $prom2  = ($ab > 0) ? (int)($sum / $ab) : null;
    $prom2a = ($re > 0) ? (int)($sum / $re) : null;

    $tablaMes[] = [
        'fecha'     => $fecha,
        'abiertas'  => $ab,
        'resueltas' => $re,
        'sum_resol' => $sum,
        'prom2'     => $prom2,
        'prom2a'    => $prom2a,
    ];
}

/* ================= Vista ================= */
$title = 'REPORTES TICKETS';
include __DIR__ . '/layout/header.php';

$cssPath = __DIR__ . '/../tools/ticketCerradosStyle.css';
$cssVer  = is_file($cssPath) ? filemtime($cssPath) : time();
?>
<link rel="stylesheet" href="../tools/ticketCerradosStyle.css?v=<?= $cssVer ?>">

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

<div class="container">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Resumen del mes</h2>
      <form class="toolbar" method="get" style="margin:0;">
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
        <div class="kpi-value"><?= ($abiertasMes > 0) ? number_format($ratioMes, 2) : '—' ?></div>
      </div>
    </div>

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
          <?php if (empty($tablaMes)): ?>
            <tr>
              <td class="empty" colspan="6">Sin datos para el mes seleccionado.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($tablaMes as $r): ?>
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

<?php include __DIR__ . '/layout/footer.php'; ?>
