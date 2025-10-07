<?php
// helpers/tickets_helpers.php

// =================== Formato & utilidades ===================
require_once __DIR__ . '/../app/appTiket.php';

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function dash($v){ return ($v === null || $v === '') ? '—' : e($v); }

function fmt($s){
  if(!$s) return '—';
  $t = strtotime($s);
  return $t ? date('d/M/Y H:i', $t) : e($s);
}

function diff_seconds($ini,$fin){
  $ti = strtotime($ini); $tf = strtotime($fin);
  if(!$ti || !$tf) return null;
  return max(0, $tf - $ti);
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

function format_duration($ini,$fin){
  $secs = diff_seconds($ini,$fin);
  if ($secs === null) return '—';
  return seconds_to_readable($secs);
}

function css_version($absPath){
  return is_file($absPath) ? filemtime($absPath) : time();
}

function safe_strftime_mon_year($ym){ // entrada "YYYY-mm" → "Sep/2025"
  $dt = DateTime::createFromFormat('Y-m', $ym);
  if(!$dt) return e($ym);
  // strftime depende de locale; usamos date() para ser agnóstico
  $map = ["Jan"=>"Ene","Apr"=>"Abr","Aug"=>"Ago","Dec"=>"Dic"];
  $txt = $dt->format('M/Y');
  return e(strtr($txt, $map));
}

// =================== Mes seleccionado ===================
function compute_month_range($mesSel){
  if(!$mesSel) $mesSel = date('Y-m');
  try {
    $start = (new DateTime($mesSel.'-01 00:00:00'));
    $end   = (new DateTime($mesSel.'-01 00:00:00'))->modify('last day of this month 23:59:59');
  } catch(Throwable $e){
    $mesSel = date('Y-m');
    $start  = new DateTime($mesSel.'-01 00:00:00');
    $end    = new DateTime($mesSel.'-01 00:00:00');
    $end->modify('last day of this month 23:59:59');
  }
  return [$mesSel, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
}

// =================== Filtrado ===================
function build_search_haystack($r){
  return mb_strtolower(implode(' ', [
    $r['Folio'] ?? '', $r['SISTEMA'] ?? '', $r['DESCRIPCION'] ?? '', $r['EMPLEADO'] ?? '',
    $r['PUESTO'] ?? '', $r['SUCURSAL'] ?? '', $r['NOMBRE_SOPORTE'] ?? '', $r['ERROR'] ?? '', $r['SOLUCION'] ?? ''
  ]), 'UTF-8');
}

/**
 * Filtra $rows por texto y rango (prioriza FECHA_SOLUCION; si no hay, usa FECHA)
 */
function filter_rows($rows, $q, $desde, $hasta){
  $needle = mb_strtolower(trim($q), 'UTF-8');
  $useText = ($needle !== '');
  $hasDesde = (bool)$desde; $hasHasta = (bool)$hasta;

  $out = [];
  foreach($rows as $r){
    if($useText){
      if(mb_strpos(build_search_haystack($r), $needle) === false) continue;
    }
    $fecha = $r['FECHA_SOLUCION'] ?? ($r['FECHA'] ?? '');
    if ($fecha && ($hasDesde || $hasHasta)) {
      $ts = strtotime($fecha);
      if ($hasDesde && $ts < strtotime($desde.' 00:00:00')) continue;
      if ($hasHasta && $ts > strtotime($hasta.' 23:59:59')) continue;
    }
    $out[] = $r;
  }
  return array_values($out);
}

// =================== Tablero (KPIs generales) ===================
function compute_board_metrics($rows){
  $totalSegsResol = 0; $totalSegsAsign = 0;
  $cntResol = 0; $cntAsign = 0;

  foreach($rows as $r){
    $crea   = $r['FECHA'] ?? null;
    $asig   = $r['FECHA_ASIGNACION'] ?? null;
    $cierra = $r['FECHA_SOLUCION'] ?? null;

    if($crea && $cierra){
      $secs = diff_seconds($crea, $cierra);
      if($secs !== null){ $totalSegsResol += $secs; $cntResol++; }
    }
    if($crea && $asig){
      $secsA = diff_seconds($crea, $asig);
      if($secsA !== null){ $totalSegsAsign += $secsA; $cntAsign++; }
    }
  }

  return [
    'total'          => count($rows),
    'total_resol'    => $totalSegsResol,
    'prom_resol'     => $cntResol ? $totalSegsResol/$cntResol : 0,
    'total_asig'     => $totalSegsAsign,
    'prom_asig'      => $cntAsign ? $totalSegsAsign/$cntAsign : 0,
    'cnt_resol'      => $cntResol,
    'cnt_asig'       => $cntAsign,
  ];
}

// =================== Métricas por mes ===================
/**
 * Devuelve:
 * - resumen mensual: abiertasMes, resueltasMes, sumResolMes, ratioMes
 * - idxDia (por día del mes): ['YYYY-mm-dd'=>['abiertas'=>..,'resueltas'=>..,'sum_resol_secs'=>..]]
 * - tablaMes: arreglo ordenado desc con campos: fecha, abiertas, resueltas, sum_resol, prom2, prom2a
 */
function compute_month_metrics($rows, $monthStart, $monthEnd){
  // Inicializa índice diario
  $idxDia = [];
  $d = new DateTime($monthStart);
  $end = new DateTime($monthEnd);
  for($cur = clone $d; $cur <= $end; $cur->modify('+1 day')){
    $idxDia[$cur->format('Y-m-d')] = ['abiertas'=>0,'resueltas'=>0,'sum_resol_secs'=>0];
  }

  $abiertasMes = 0; $resueltasMes = 0; $sumResolMes = 0;

  foreach($rows as $r){
    $crea   = $r['FECHA'] ?? null;
    $cierra = $r['FECHA_SOLUCION'] ?? null;

    // Abiertas dentro del mes (por FECHA)
    if($crea){
      $ts = strtotime($crea);
      if($ts >= strtotime($monthStart) && $ts <= strtotime($monthEnd)){
        $key = date('Y-m-d', $ts);
        if(isset($idxDia[$key])){ $idxDia[$key]['abiertas']++; $abiertasMes++; }
      }
    }

    // Resueltas dentro del mes (por FECHA_SOLUCION) y suma de resolución (cierre - creación)
    if($cierra){
      $tsc = strtotime($cierra);
      if($tsc >= strtotime($monthStart) && $tsc <= strtotime($monthEnd)){
        $key = date('Y-m-d', $tsc);
        if(isset($idxDia[$key])){
          $idxDia[$key]['resueltas']++; $resueltasMes++;
          $secs = diff_seconds($r['FECHA'] ?? null, $r['FECHA_SOLUCION'] ?? null);
          if($secs !== null){ $idxDia[$key]['sum_resol_secs'] += $secs; $sumResolMes += $secs; }
        }
      }
    }
  }

  // Construir tabla (desc)
  krsort($idxDia);
  $tablaMes = [];
  foreach($idxDia as $fecha => $vals){
    $ab  = (int)$vals['abiertas'];
    $re  = (int)$vals['resueltas'];
    $sum = (int)$vals['sum_resol_secs'];
    $tablaMes[] = [
      'fecha'     => $fecha,
      'abiertas'  => $ab,
      'resueltas' => $re,
      'sum_resol' => $sum,
      'prom2'     => ($ab > 0) ? (int)($sum / $ab) : null, // (2)
      'prom2a'    => ($re > 0) ? (int)($sum / $re) : null, // (2a)
    ];
  }

  $ratioMes = ($abiertasMes > 0) ? ($resueltasMes / $abiertasMes) : 0;

  return [
    'resumen' => [
      'abiertasMes' => $abiertasMes,
      'resueltasMes'=> $resueltasMes,
      'sumResolMes' => $sumResolMes,
      'ratioMes'    => $ratioMes,
    ],
    'idxDia'   => $idxDia,
    'tablaMes' => $tablaMes,
  ];
}

// =================== Orden & paginación ===================
function sort_rows_desc($rows){
  usort($rows, function($a,$b){
    $fa = strtotime($a['FECHA_SOLUCION'] ?? $a['FECHA'] ?? '1970-01-01');
    $fb = strtotime($b['FECHA_SOLUCION'] ?? $b['FECHA'] ?? '1970-01-01');
    return $fb <=> $fa;
  });
  return $rows;
}

function paginate($rows, $per, $page){
  $total_rows  = count($rows);
  $total_pages = max(1, (int)ceil($total_rows / $per));
  $page        = min(max(1,$page), $total_pages);
  $offset      = ($page - 1) * $per;
  $view        = array_slice($rows, $offset, $per);
  return [$view, $total_rows, $total_pages, $page, $offset];
}
