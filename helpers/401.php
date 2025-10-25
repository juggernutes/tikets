<?php
http_response_code(200);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Contraseña incorrecta</title>
<style>
    :root{
    --bg-1: #04060a;
    --bg-2: #071224;
    --vignette-in: rgba(0,0,0,0.36);
    --vignette-mid: rgba(0,0,0,0);
    --vignette-out: rgba(0,0,0,0.36);
    --brand-glow: rgba(9,65,140,0.32);
    --card-bg: rgba(255,255,255,0.98);
    --primary: #0b75d6;
    --primary-dark: #0958b5;
    }

    /* --- Fondo: degradados recuperados --- */
    html, body { height:100%; margin:0; }
    body{
    min-height:100vh;
    display:flex;
    align-items:flex-start;
    justify-content:center;
    padding:6vh 16px;
    font-family: Arial, Helvetica, sans-serif;
    color:#e9eef6;

    /* <-- Asegúrate de incluir las 3 capas de gradiente aquí (sin la imagen) */
    background-image:
        radial-gradient(ellipse 1100px 780px at 50% 50%,
        var(--vignette-in) 0%,
        rgba(255,255,255,0) 38%,
        var(--vignette-mid) 68%,
        var(--vignette-out) 100%),
        radial-gradient(ellipse 700px 480px at 92% 96%,
        var(--brand-glow) 0%,
        rgba(9,65,140,0) 60%),
        linear-gradient(180deg, var(--bg-1) 0%, var(--bg-2) 100%);
    background-repeat: no-repeat, no-repeat, no-repeat;
    background-position: center, 92% 96%, center;
    background-size: 1100px 780px, 700px 480px, cover;

    /* opcional si quieres mezclar capas: background-blend-mode: overlay; */
    }

    /* Logo como bloque independiente (con esquinas redondeadas) */
    .logo-box{
    position: relative;      /* para controlar z-index */
    z-index: 1;
    width: 225px;
    height: 150px;
    margin: 0 auto;
    background-image: url('../img/Tecnologias.png');
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    background-color: #fff6f2;
    margin-bottom: 20px;
    }

    /* Tarjeta, encima del logo */
    .contenido{
    position: relative;
    z-index: 2;
    width:100%;
    max-width:720px;
    margin: 0 auto;
    margin-top: clamp(90px, 15vh, 260px); /* ajusta para bajar/ subrir la tarjeta */
    background: var(--card-bg);
    border-radius:10px;
    padding:32px;
    box-shadow: 0 18px 40px rgba(0,0,0,0.6);
    color:#111;
    }

    /* responsive */
    @media (max-width:640px){
    .logo-box { width: 160px; height:160px; border-radius:12px; }
    .contenido{ margin-top: 46vh; padding:18px; }
    h1{ font-size:1.6rem; }
    .actions { flex-direction: column; gap: 10px; }
    .btn { width: 100%; max-width: 360px; }
    }

    h1{ font-size:2.6rem; margin:0 0 6px; font-weight:700; }
    p.lead{ margin:8px 0 18px; font-size:1rem; color:#333; display:flex; justify-content:center;}
    .actions{ display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:12px; align-items: center;}
    .btn{ padding:10px 16px; border-radius:8px; text-decoration:none; font-weight:600; display:inline-block; border:none; cursor:pointer; }
    .btn-primary{ background:var(--primary); color:#fff; box-shadow: 0 8px 22px rgba(11,117,214,0.18);}
    .btn-primary:hover{ background:var(--primary-dark); transform:translateY(-2px);}
    .btn-outline{ background:transparent; color:var(--primary); border:2px solid rgba(11,117,214,0.12);}
    .small{ margin-top:16px; color:#666; }
    .titulo{display:flex; justify-content:center;}
    
</style>
</head>
<body>

  <main class="contenido" role="main" aria-labelledby="titulo">
  <div class="logo-box" aria-hidden="true"></div>
    <h1 id="titulo" class="titulo">Contraseña incorrecta</h1>
    <p class="lead">La cuenta o la contraseña que ingresaste no son correctas. Verifica y vuelve a intentarlo.</p>

    <div class="actions">
      <a class="btn btn-primary" href="../public/index.php">Reintentar inicio de sesión</a>
      <a class="btn btn-outline" href="../views/usuario_contr.php">Restablecer contraseña</a>
    </div>

    <div class="small" style="display:flex; justify-content:center;">
      Si requieres ayuda, contacta a Soporte Técnico extenciones: <strong> 1120, 1140, 1153</strong>
    </div>
  </main>
</body>
</html>
