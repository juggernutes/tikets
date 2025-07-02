<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$rol = $_SESSION['rol'] ?? 'Invitado';
$usuario = $_SESSION['login_id'] ?? 'Sin sesión';
$nombreUsuario = $_SESSION['nombre'] ?? 'Sin sesión';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Sistema de Tickets' ?></title>
    <link rel="stylesheet" href="../tools/style.css">
</head>

<body>
    <div class="wrapper">
        <header>
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                <!-- Logo a la izquierda -->
                <div class="contenedor-animacion" id="animacionRosarito">
                    <img src="../img/Exterior.png" alt="Exterior" class="exterior">
                    <img src="../img/Centro.png" alt="Centro" class="centro">
                </div>
                <script>
                    const animacion = document.getElementById('animacionRosarito');

                    function reiniciarAnimacion() {
                        animacion.classList.add('reiniciar');
                        void animacion.offsetWidth;
                        animacion.classList.remove('reiniciar');
                    }

                    setInterval(reiniciarAnimacion, 30000);
                </script>


                <!-- Título centrado -->
                <div style="flex: 1; text-align: center;">
                    <h1 style="margin: 0;">SISTEMA DE GESTION TIKETS</h1>
                </div>

                <!-- Usuario a la derecha -->
                <div style="flex: 1; text-align: right; padding-right: 10px;">
                    <small>Usuario: </small> <strong><?= htmlspecialchars($nombreUsuario) ?> ||</strong>
                    <small> Rol: </small><strong><?= htmlspecialchars($rol) ?></strong>
                </div>
            </div>

            <nav style="text-align: center; margin: 10px 0;">
                <a href="../views/dashboard.php"><button>Inicio</button></a>
                <?php if ($rol === 'ADMINISTRADOR' || $rol === 'EMPLEADO' || $rol === 'SOPORTE'): ?>
                    <a href="../views/registrar_tiket.php"><button>Crear Ticket</button></a>
                    <a href="../views/cerrado_tiket.php"><button>Ticket cerrados</button></a>
                <?php endif; ?>
                <?php if ($rol === 'ADMINISTRADOR' || $rol === 'SOPORTE'): ?>
                    <a href="../views/empleado.php"><button>Empleados</button></a>
                    <!-- <a href="../views/resolver_tiket.php"><button>Resolver Tickets</button></a>
                    <a href="../views/reporte_tickets.php"><button>Reporte</button></a> -->
                <?php endif; ?>
                <a href="../public/logout.php"><button>Cerrar sesión</button></a>
            </nav>
            <hr>
        </header>
        <main style="background-image: url('../img/FONDO_2.png'); background-size: cover; background-position: center; background-repeat: no-repeat; padding: 20px;">
            <div class="content">
                <!-- Aquí va el contenido de cada página -->