<?php
if (session_status() === PHP_SESSION_NONE) {session_start();}
$rol = $_SESSION['rol'] ?? 'Invitado';
$usuario = $_SESSION['login_id'] ?? 'Sin sesión';
$nombreUsuario = $_SESSION['nombre'] ?? 'Sin sesión';


?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SISTEMA DE GESTION</title>
  <link rel="icon" href="../img/favicon.ico" type="image/x-icon">
  <style>
    /* ==========================
       Diseño base y variables
       ========================== */
    :root {
      --bg: #0b1220;
      --bg-soft: #101826cc; /* con transparencia */
      --card: #121c2f;
      --text: #e6edf3;
      --muted: #9fb0c7;
      --brand: #46c2ff;
      --brand-2: #8a5cff;
      --ok: #43d39e;
      --danger: #ff6b6b;
      --ring: #2b3a55;
      --blur: 14px;
      --radius: 16px;
      --shadow: 0 10px 30px rgba(0,0,0,.25);
    }

    @media (prefers-color-scheme: light) {
      :root {
        --bg: #f6f8fb;
        --bg-soft: #ffffffcc;
        --card: #ffffff;
        --text: #101418;
        --muted: #6b7280;
        --ring: #e5e7eb;
        --shadow: 0 10px 30px rgba(16,24,40,.10);
      }
    }

    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      margin: 0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji";
      color: var(--text);
      background: radial-gradient(1200px 600px at 10% -10%, rgba(70,194,255,.18), transparent 60%),
                  radial-gradient(1000px 500px at 90% -20%, rgba(138,92,255,.18), transparent 60%),
                  var(--bg);
    }

    /* ==========================
       Header compacto y elegante
       ========================== */
    header.site-header {
        overflow: visible;
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(var(--blur));
      -webkit-backdrop-filter: blur(var(--blur));
      background: linear-gradient(180deg, var(--bg-soft) 0%, rgba(0,0,0,0) 140%);
      border-bottom: 1px solid var(--ring);
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 10px 16px;
      display: grid;
      grid-template-columns: auto 1fr auto;
      align-items: center;
      gap: 14px;
      min-height: 64px; /* compacto */
    }

    /* Marca y animación conservada */
    .brand {
      display: grid;
      grid-auto-flow: column;
      align-items: center;
      gap: 12px;
      grid-template-columns: 84px 1fr; align-items:center; 
    }

    .logo-shell {
      position: relative;
      width: 80px; height: 80px;
      display: grid; place-items: center;
      margin-right:12px; position:relative; z-index:1; pointer-events:none; 
    }

    /* Anillo exterior (animación girarExterior conservada) */
    .ring {
      position: absolute;
      inset: -2px;
      border-radius: 999px;
      background: url("../img/Exterior.png") no-repeat center/contain;
      animation: girarExterior 3.5s linear infinite;
      filter: drop-shadow(0 0 8px rgba(255,255,255,.85));
    }

    /* Centro que se abre (animación abrirCentro conservada) */
    .core {
      position: relative;
      width: 44px; height: 44px;
      background: url("../img/Centro.png") no-repeat center/contain;
      border-radius: 999px;
      animation: abrirCentro 700ms ease-in-out both;
      box-shadow: inset 0 0 8px rgba(255,255,255,.12), 0 2px 6px rgba(0,0,0,.25);
    }

    .brand h1 {
      margin: 0;
      font-size: clamp(16px, 1.6vw, 20px);
      font-weight: 700;
      letter-spacing: .2px;
      line-height: 1.15;
      position:relative; z-index:2; 
    }

    .brand .sub {
      display: block;
      font-size: 12px;
      font-weight: 500;
      color: var(--muted);
      letter-spacing: .2px;
      margin-top: 2px;
      position:relative; z-index:2; 
    }

    /* Navegación minimal */
    nav ul {
      display: flex;
      gap: 14px;
      list-style: none;
      padding: 0; margin: 0;
      align-items: center;
      justify-content: center;
    }

    nav a {
      text-decoration: none;
      color: var(--text);
      font-size: 14px;
      padding: 8px 10px;
      border-radius: 10px;
      border: 1px solid transparent;
      transition: .2s ease;
    }

    nav a:hover {
      border-color: var(--ring);
      background: rgba(255,255,255,.04);
    }

    /* Usuario a la derecha */
    .user {
      display: grid;
      grid-auto-flow: column;
      align-items: center;
      gap: 10px;
      padding: 6px 10px;
      border: 1px solid var(--ring);
      border-radius: 12px;
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,0));
    }

    .user .avatar {
      width: 28px; height: 28px;
      border-radius: 999px;
      display: grid; place-items: center;
      background: linear-gradient(180deg, var(--brand), var(--brand-2));
      color: white;
      font-weight: 700;
      font-size: 14px;
    }

    .user small { display:block; color: var(--muted); font-size: 11px; margin-top: 1px; }

    /* ==========================
       Main (demo)
       ========================== */
    main {
      max-width: 1100px;
      margin: 24px auto;
      padding: 0 16px 40px;
    }

    .card {
      background: var(--card);
      border: 1px solid var(--ring);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 20px;
    }

    /* ==========================
       Animaciones (recicladas)
       ========================== */
    @keyframes girarExterior {
      0% {
        transform: rotate(0deg);
        -webkit-mask-image: linear-gradient(to top, white 0%, white 100%);
                mask-image: linear-gradient(to top, white 0%, white 100%);
      }
      100% {
        transform: rotate(360deg);
        -webkit-mask-image: none;
                mask-image: none;
      }
    }

    @keyframes abrirCentro {
      0% { transform: scaleX(0); opacity: 0; }
      50% { transform: scaleX(1.08); opacity: 1; }
      100% { transform: scaleX(1); opacity: 1; }
    }

    /* ==========================
       Responsivo
       ========================== */
    @media (max-width: 760px) {
      .container { grid-template-columns: 1fr auto; gap: 10px; }
      nav { display: none; }
    }
  
    /* ==========================
       Footer estilizado (ligero, sin <hr>)
       ========================== */
    .app-footer{
      background: rgba(255,255,255,.9);
      backdrop-filter: blur(8px) saturate(1.05);
      -webkit-backdrop-filter: blur(8px) saturate(1.05);
      border-top: 1px solid #e9e9e9;
      box-shadow: 0 -4px 18px rgba(0,0,0,.06);
    }
    .app-footer__row{
      max-width: 1200px; margin: 0 auto;
      display:flex; align-items:center; justify-content:space-between;
      gap: 12px; padding: 12px 16px; min-height: 60px;
      color:#0b2c4a;
    }
    .app-footer__brand{
      display:flex; align-items:center; gap:10px; flex-wrap:wrap;
      font-size: 13px; font-weight:600;
    }
    .app-footer__brand .muted{ color:#8aa2b4; font-weight:500; }
    .app-footer__links{ display:flex; gap:10px; flex-wrap:wrap; }
    .app-footer__link{
      display:inline-flex; align-items:center; gap:6px; height:32px; padding:0 10px;
      border-radius:8px; border:1px solid #d8e6f3; background:#fff; color:#0f6292; text-decoration:none;
      font-weight:700; font-size:13px; box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 1px 8px rgba(0,0,0,.04);
      transition: transform .04s ease, background .15s ease, box-shadow .15s ease;
    }
    .app-footer__link:hover{ background:#f6fbff; box-shadow: 0 6px 18px rgba(15,98,146,.18); }
    .app-footer__meta{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; font-size:12px; color:#46627a; }
    .badge{ display:inline-flex; align-items:center; gap:6px; height:24px; padding:0 8px; border-radius:999px; border:1px solid #d8e6f3; background:#eef6fc; color:#0f6292; font-weight:700; }
    .badge--danger{ background:#fdecec; color:#b8323b; border-color:#f4cdd0; }
    .divider{ width:1px; height:18px; background:#e8eef4; }

    @media (max-width: 760px){
      .app-footer__row{ flex-direction:column; align-items:flex-start; gap:8px; }
      .divider{ display:none; }
    }
  </style>
  <?php if ($rol === 'SOPORTE'  && $title === 'DASHBOARD'): ?>
    <!-- Auto-refresh cada 180 segundos solo para soporte -->
    <meta http-equiv="refresh" content="180">
  <?php endif; ?>
</head>
<body>
  <header class="site-header">
    <div class="container">
      <div class="brand">
        <div class="logo-shell" aria-hidden="true">
          <span class="ring"></span>
          <span class="core"></span>
        </div>
        <div>
          <h1>SISTEMA DE GESTION</h1>
          <span class="sub">Empacadora Rosarito</span>
        </div>
      </div>

      <nav aria-label="Principal">
        <ul>
          <?php if ($rol === 'ALMACEN' || $rol === 'SUPERVISOR'): ?>
            <li><a href="../views/dashboardPedidos.php">Inicio</a></li>
            <!--<li><a href="../views/registrar_entrada.php">Entradas</a></li>
            <li><a href="../views/historial_entradas.php">Historial de entradas</a></li>-->
          <?php endif; ?>
          <?php if ($rol === 'EMPLEADO' || $rol === 'ADMINISTRADOR' || $rol === 'SOPORTE'): ?>
          <li><a href="../views/dashboard.php">Inicio</a></li>
          <li><a href="../views/registrar_tiket.php">Tickets</a></li>
          <li><a href="../views/cerrado_tiket.php">Historial de tikects</a></li>
          <?php if ($rol === 'ADMINISTRADOR' || $rol === 'SOPORTE'): ?>
            <li><a href="../views/empleado.php">Empleados</a></li>
          <?php endif; ?>
          <?php endif; ?>
          <li><a href="../public/logout.php">Cerrar sesión</a></li>
        </ul>
      </nav>

      <div class="user" title="<?php echo htmlspecialchars($rol); ?>">
        <div class="avatar"><?php echo strtoupper(substr($nombreUsuario, 0, 1)); ?></div>
        <div>
          <strong style="font-size:13px; line-height:1.1; display:block; max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            <?php echo htmlspecialchars($nombreUsuario); ?>
          </strong>
          <small><?php echo htmlspecialchars($rol); ?></small>
        </div>
      </div>
    </div>
  </header>
  <!-- ===== Footer estilizado ===== -->
  