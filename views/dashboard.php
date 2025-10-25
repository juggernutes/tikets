<?php
// dashboard.php (vista)
include __DIR__ . '/../app/appTiket.php';

$tiketController = new TiketController(new Tiket($conn));
$title = 'DASHBOARD';
include __DIR__ . '/../views/layout/header.php';

// Inicialización de arrays
$tikets        = null;
$soloAbiertos  = [];
$enProceso     = [];
$cerrados      = [];

// Obtiene tickets según rol
if ($rol === 'EMPLEADO') {
    $tikets = $tiketController->getTicketsByUserId($usuarioId);
} elseif ($rol === 'SOPORTE' || $rol === 'ADMINISTRADOR') {
    $tikets = $tiketController->getAllTickets($usuarioId);
}

// Clasificación por estado
if ($tikets && $tikets->num_rows > 0) {
    while ($row = $tikets->fetch_assoc()) {
        $estado = strtoupper(trim($row['ESTADO'] ?? ''));
        if ($estado === 'ABIERTO') {
            $soloAbiertos[] = $row;
        } elseif ($estado === 'EN PROCESO') {
            $enProceso[] = $row;
        } else {
            $cerrados[] = $row; // RESUELTO / CERRADO / otros
        }
    }
}

$totalTickets   = ($tikets ? $tikets->num_rows : 0);
$totalAbiertos  = count($soloAbiertos) + count($enProceso);
$totalCerrados  = count($cerrados);
/*if ($rol === 'SOPORTE') {
  // Recargar la pagina
  echo '<meta http-equiv="refresh" content="180">';
  exit;
}*/
$rol = $_SESSION['rol'] ?? 'Invitado';
// REDIRECCIONAR SEGÚN ROL
if ($rol === 'SUPERVISOR' || $rol === 'ALMACEN') {
    header('Location: ./dashboardPedidos.php');
    exit;
} 
if ($rol === 'VENDEDOR') {
    header('Location: ./pedido.php');
    exit;
} 
?>

<style>
  html{ scroll-behavior:smooth; }
  .dashboard-section{margin:24px 0}

  /* Grid flexible por sección */
  .contenedor-tickets, .contenedor-tickets-cerrados{
    margin-top: 12px;
    display:grid;
    gap:16px;
    grid-template-columns:repeat(auto-fit,minmax(340px,1fr));
    align-items:start;
  }

  .tiket{
    display:block;
    background:#fff;
    border:2px solid transparent;
    border-radius:14px;
    padding:16px;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
    transition:all .2s ease-in-out;
  }
  .tiket:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,.1)}
  .tiket h4{margin:0 0 8px;font-size:16px;font-weight:700;color:#0f172a}
  .tiket p{margin:4px 0;font-size:14px;color:#334155}
  .acciones-botones{margin-top:10px;display:flex;gap:8px;flex-wrap:wrap}
  .acciones-botones button{padding:6px 12px;border:1px solid #0ea5e9;background:#0ea5e9;color:#fff;border-radius:8px;cursor:pointer;font-size:13px}
  .acciones-botones button:hover{background:#0284c7}

  /* Borde destacado según estado */
  .estado-abierto{border:2px solid #dc2626}
  .estado-en-proceso{border:2px solid #ca8a04}
  .estado-cerrado,.estado-resuelto{border:2px solid #16a34a}

  /* KPIs */
  .kpis{display:flex;gap:16px;justify-content:center;margin:20px 0;flex-wrap:wrap}
  .kpi-card{flex:1 1 200px;max-width:250px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,.05)}
  .kpi-card h3{margin:0 0 8px;font-size:16px;font-weight:600;color:#334155}
  .kpi-card .num{font-size:28px;font-weight:900;color:#0f172a}
  .kpi-card.total{border-top:5px solid #3b82f6}
  .kpi-card.abiertos{border-top:5px solid #dc2626}
  .kpi-card.cerrados{border-top:5px solid #16a34a}

  /* Subtítulos dentro de la sección de abiertos */
  .subtitulo{display:flex;align-items:center;gap:8px;margin:16px 0 6px;color:#e2e8f0}
  .subtitulo b{color:#fff;font-size:18px}
  .chip-estado{height:8px;width:8px;border-radius:999px;display:inline-block}
  .chip-abierto{background:#dc2626}
  .chip-proceso{background:#ca8a04}

  .btn-cancelar {
  background-color: #e74c3c;   /* rojo elegante */
  color: white;                /* texto blanco */
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
  }

  .btn-cancelar:hover {
  background-color: #c0392b;   /* rojo más oscuro al pasar mouse */
  transform: scale(1.05);      /* pequeño zoom */
  }

</style>

<div class="container">
  <h1 style="margin:20px 0;color:#0f172a;">Dashboard</h1>

  <!-- KPIs -->
  <div class="kpis">
    <a href="#totales" class="kpi-card total" style="text-decoration:none;color:inherit;">
      <h3>Tickets totales</h3>
      <div class="num"><?= $totalTickets ?></div>
    </a>
    <a href="#abiertos" class="kpi-card abiertos" style="text-decoration:none;color:inherit;">
      <h3>Abiertos / En proceso</h3>
      <div class="num"><?= $totalAbiertos ?></div>
    </a>
    <a href="#cerrados" class="kpi-card cerrados" style="text-decoration:none;color:inherit;">
      <h3> Resueltos</h3>
      <div class="num"><?= $totalCerrados ?></div>
    </a>
  </div>
</div>
  <!-- Sección: Abiertos / En proceso -->
  <div id="abiertos" class="dashboard-section">
    <h2>Tickets abiertos o en proceso</h2>

    <!-- Subgrid: ABIERTO -->
    <div class="subtitulo"><span class="chip-estado chip-abierto"></span><b>Abiertos</b></div>
    <?php if(!empty($soloAbiertos)): ?>
      <div class="contenedor-tickets">
        <?php foreach ($soloAbiertos as $row): 
          $estadoClass = 'estado-' . strtolower(str_replace(' ', '-', $row['ESTADO']));
          $descripcion = wordwrap($row['DESCRIPCION'] ?? '', 90, "\n", true);
        ?>
          <div class="tiket <?= $estadoClass ?>">
            <h4><?= htmlspecialchars($row['Folio']) ?> - <?= htmlspecialchars($row['SISTEMA']) ?></h4>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($row['FECHA']) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($row['ESTADO']) ?></p>
            <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')) ?></p>
            <p><strong>Empleado:</strong> <?= htmlspecialchars($row['EMPLEADO']) ?></p>
            <p><strong>Puesto:</strong> <?= htmlspecialchars($row['PUESTO']) ?></p>
            <p><strong>Sucursal:</strong> <?= htmlspecialchars($row['SUCURSAL']) ?></p>
            <div class="acciones-botones">
              <a href="../app/appTiket.php?accion=cancelarTiket&id_tiket=<?= $row['ID_Tiket'] ?>"><button class="btn-cancelar" 
              style = "background-color: #e74c3c; color: white; border: none; padding: 8px 14px;border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: background-color 0.3s ease, transform 0.2s ease;">Cancelar</button></a>
              <?php if(isset($_SESSION['rol']) && ($_SESSION['rol'] === 'SOPORTE'|| $_SESSION['rol'] === 'ADMINISTRADOR')): ?>
                <a href="../app/appTiket.php?accion=tomarTiket&id_tiket=<?= $row['ID_Tiket'] ?>"><button>Tomar</button></a>
              <?php endif; ?>
            </div>
            
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color:#94a3b8">No hay tickets en estado ABIERTO.</p>
    <?php endif; ?>

    <!-- Subgrid: EN PROCESO -->
    <div class="subtitulo"><span class="chip-estado chip-proceso"></span><b>En proceso</b></div>
    <?php if(!empty($enProceso)): ?>
      <div class="contenedor-tickets">
        <?php foreach ($enProceso as $row): 
          $estadoClass = 'estado-' . strtolower(str_replace(' ', '-', $row['ESTADO']));
          $descripcion = wordwrap($row['DESCRIPCION'] ?? '', 90, "\n", true);
          $descripcionSolucion = wordwrap($row['DESCRIPCION_SOLUCION'] ?? '', 90, "\n", true);
        ?>
          <div class="tiket <?= $estadoClass ?>">
            <h4><?= htmlspecialchars($row['Folio']) ?> - <?= htmlspecialchars($row['SISTEMA']) ?></h4>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($row['FECHA']) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($row['ESTADO']) ?></p>
            <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')) ?></p>
            <p><strong>Empleado:</strong> <?= htmlspecialchars($row['EMPLEADO']) ?></p>
            <p><strong>Puesto:</strong> <?= htmlspecialchars($row['PUESTO']) ?></p>
            <p><strong>Sucursal:</strong> <?= htmlspecialchars($row['SUCURSAL']) ?></p>
            <?php if(!empty($descripcionSolucion)): ?>
              <p><strong>Descripción de la solución:</strong><br><?= nl2br(htmlspecialchars($descripcionSolucion, ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>
            <?php if(isset($_SESSION['rol']) && ($_SESSION['rol'] === 'SOPORTE'|| $_SESSION['rol'] === 'ADMINISTRADOR')): ?>
              <div class="acciones-botones">
                <a href="../app/appTiket.php?accion=tomarTiket&id_tiket=<?= $row['ID_Tiket'] ?>"><button>Tomar</button></a>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color:#94a3b8">No hay tickets EN PROCESO.</p>
    <?php endif; ?>
  </div>

  <!-- Sección: Cerrados / Resueltos -->
  <div id="cerrados" class="dashboard-section">
    <h2>Tickets resueltos</h2>
    <?php if(!empty($cerrados)): ?>
      <div class="contenedor-tickets-cerrados">
        <?php foreach ($cerrados as $row):
          $estadoClass = 'estado-' . strtolower(str_replace(' ', '-', $row['ESTADO']));
          $descripcion = wordwrap($row['DESCRIPCION'] ?? '', 90, "\n", true);
          $descripcionSolucion = wordwrap($row['DESCRIPCION_SOLUCION'] ?? '', 90, "\n", true);
        ?>
          <div class="tiket <?= $estadoClass ?>">
            <h4><?= htmlspecialchars($row['Folio']) ?> - <?= htmlspecialchars($row['SISTEMA']) ?></h4>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($row['FECHA']) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($row['ESTADO']) ?></p>
            <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')) ?></p>
            <p><strong>Empleado:</strong> <?= htmlspecialchars($row['EMPLEADO']) ?></p>
            <p><strong>Puesto:</strong> <?= htmlspecialchars($row['PUESTO']) ?></p>
            <p><strong>Sucursal:</strong> <?= htmlspecialchars($row['SUCURSAL']) ?></p>
            <p><strong>Soporte:</strong> <?= htmlspecialchars($row['NOMBRE_SOPORTE'] ?? '') ?></p>
            <p><strong>Fecha de resolución:</strong> <?= htmlspecialchars($row['FECHA_SOLUCION'] ?? '') ?></p>
            <p><strong>Error:</strong> <?= htmlspecialchars($row['ERROR'] ?? '') ?></p>
            <p><strong>Solución:</strong> <?= htmlspecialchars($row['SOLUCION'] ?? '') ?></p>
            <?php if(!empty($descripcionSolucion)): ?>
              <p><strong>Descripción de la solución:</strong><br><?= nl2br(htmlspecialchars($descripcionSolucion, ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>
            <div class="acciones-botones">
              <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'SOPORTE' || $_SESSION['rol'] === 'ADMINISTRADOR')): ?>
                <a href="../app/appTiket.php?accion=activarTiket&id_tiket=<?= $row['ID_Tiket'] ?>"><button>Activar</button></a>
              <?php endif; ?>
              <a href="../app/appTiket.php?accion=cerrarTiket&id_tiket=<?= $row['ID_Tiket'] ?>"><button>Cerrar</button></a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color:#94a3b8">No hay tickets cerrados.</p>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>