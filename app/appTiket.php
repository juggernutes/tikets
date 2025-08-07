<?php


ob_start(); // Iniciar el buffer de salida

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

// Cargar las dependencias necesarias
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/empleado.php';
require_once __DIR__ . '/../models/tiket.php';
require_once __DIR__ . '/../models/errorModel.php';
require_once __DIR__ . '/../models/sistema.php';
require_once __DIR__ . '/../models/solucion.php';
require_once __DIR__ . '/../models/encuesta.php';
require_once __DIR__ . '/../models/equipo.php';

// Cargar los controladores necesarios
require_once __DIR__ . '/../controllers/solucionController.php';
require_once __DIR__ . '/../controllers/empleadoController.php';
require_once __DIR__ . '/../controllers/SistemaController.php';
require_once __DIR__ . '/../controllers/tiketController.php';
require_once __DIR__ . '/../controllers/errorModelController.php';
require_once __DIR__ . '/../controllers/encuestaController.php';
require_once __DIR__ . '/../controllers/equipoController.php';

// Crear instancias de los controladores y modelos
$sistemaController = new SistemaController($conn);
$empleadoController = new EmpleadoController($conn);
$tiketController = new TiketController(new Tiket($conn));
$errorController = new ErrorModelController(new ErrorModel($conn));
$solucionController = new SolucionController(new Solucion($conn));
$encuestaController = new EncuestaController($conn);
$equipoController = new EpicoController($conn);
/*$loginController = new LoginController($conn);*/

$usuarioId = $_SESSION['login_id'] ?? null;

if (isset($_GET['accion'])) {
    switch ($_GET['accion']) {
        case 'crearTiket':
            $numEmpleado = intval($_POST['Numero_Empleado']);
            $idSistema = intval($_POST['id_sistema']);
            $descripcion = trim($_POST['descripcion']);

            if ($numEmpleado > 0 && $idSistema > 0 && !empty($descripcion)) {
                $tiketCreado = $tiketController->createTicket($numEmpleado, $idSistema, $descripcion);
                if ($tiketCreado) {
                    header("Location: ../views/dashboard.php");
                } else {
                    // Manejo de error: no se pudo crear el ticket
                    echo "No se pudo crear el ticket. Inténtalo de nuevo más tarde.";
                }
                exit;
            } else {
                // Manejo de error: parámetros inválidos
                echo "Parámetros inválidos.";
            }
            break;
        case 'tomarTiket':
            $idTiket = intval($_GET['id_tiket']);
            $idSoporte = intval($_SESSION['login_id']);

            if ($idTiket > 0 && $idSoporte > 0) {
                $tiketTomado = $tiketController->tomarControlDeTiket($idTiket, $idSoporte);
                if ($tiketTomado) {
                    header("Location: ../views/resolver_tiket.php?id=$idTiket");
                } else  {echo "No se pudo tomar el ticket. Inténtalo de nuevo más tarde.";}
                exit;
            } else {echo "Parámetros inválidos.";}
            break;
        case 'solucionar':
            $idTiket = intval($_GET['id_tiket']);
            $idSoporte = intval($_SESSION['login_id']);
            $idError = intval($_POST['id_error']);
            $idSolucion = intval($_POST['id_solucion']);
            $descripcionSolucion = $_POST['descripcion_solucion'];

            if ($idTiket > 0 && $idSoporte > 0) {
                $tiketResuelto = $tiketController->resolverTiket($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion);
                if ($tiketResuelto) {
                    header("Location: ../views/dashboard.php");
                } else {
                    // Manejo de error: no se pudo resolver el ticket
                    echo "No se pudo resolver el ticket. Inténtalo de nuevo más tarde.";
                }
                exit;
            } else {
                // Manejo de error: IDs inválidos
                echo "Parámetros inválidos.";
            }
            break;
        case 'cerrarTiket':
            $idTiket = intval($_GET['id_tiket']);
            $idUsuario = intval($_SESSION['login_id']);

            if ($idTiket > 0 && $idUsuario > 0) {
                $tiketResuelto = $tiketController->closeTicket($idTiket, $idUsuario);

                if ($tiketResuelto) {
                    header("Location: ../views/encuesta.php?id_tiket=$idTiket");
                } else {
                    // Manejo de error: no se pudo resolver el ticket
                    echo "No se pudo resolver el ticket. Inténtalo de nuevo más tarde.";
                }
                exit;
            } else {
                // Manejo de error: IDs inválidos
                echo "Parámetros inválidos.";
            }
            break;
        case 'calificarEncuesta':
            $idTiket = intval($_GET['id_tiket']);
            $calificacion = intval($_POST['calificacion']);
            $comentarios = substr(trim($_POST['comentarios']), 0, 500);

            if ($idTiket > 0 && $calificacion >= 1 && $calificacion <= 5) {
                $encuestaCalificada = $encuestaController->calificarEncuesta($idTiket, $calificacion, $comentarios);

                if ($encuestaCalificada) {
                    header("Location: ../views/dashboard.php");
                } else {
                    // Manejo de error: no se pudo calificar la encuesta
                    echo "No se pudo calificar la encuesta. Inténtalo de nuevo más tarde.";
                }
                exit;
            } else {
                // Manejo de error: parámetros inválidos
                echo "Parámetros inválidos.";
            }
            break;
        case 'activarTiket':
            $idTiket = intval($_GET['id_tiket']);

            if ($idTiket > 0) {
                $tiketActivado = $tiketController->activarTiket($idTiket);
                if ($tiketActivado) {
                    header("Location: ../views/dashboard.php");
                } else {
                    // Manejo de error: no se pudo activar el ticket
                    echo "No se pudo activar el ticket. Inténtalo de nuevo más tarde.";
                }
                exit;
            } else {
                // Manejo de error: ID inválido
                echo "Parámetros inválidos.";
            }
            break;
        case 'restablecerContrasena':
            if (isset($_POST['usuario'])) {
                $usuario = $_POST['usuario'];
                $loginController->restablecerContrasena($usuario);
            } else {
                echo "Usuario no especificado.";
            }
            break;
        case 'enviarCorreoRestablecerContrasena':
            if (isset($_POST['correo'])) {
                $correo = $_POST['correo'];
                $loginController->restablecerContrasena($correo);
                echo "Se ha enviado un enlace para restablecer la contraseña a su correo.";
            } else {
                echo "Correo no encontrado.";
            }
            break;
        default:
            // Manejo de error: acción no reconocida
            echo "Acción no reconocida.";
            break;
    }
} else {
    // Manejo de error: no se especificó una acción
}

ob_end_flush(); // Enviar el contenido del buffer de salida

/*
if (isset($_GET['accion'], $_GET['id_tiket']) && $_GET['accion'] === 'tomarTiket') {
    $idTiket = intval($_GET['id_tiket']);
    $idSoporte = intval($_SESSION['login_id']);

    if ($idTiket > 0 && $idSoporte > 0) {
        $tiketTomado = $tiketController->tomarControlDeTiket($idTiket, $idSoporte);
        if ($tiketTomado) {
            header("Location: ../views/resolver_tiket.php?id=$idTiket");
        } else {
            // Manejo de error: no se pudo tomar el ticket
            echo "No se pudo tomar el ticket. Inténtalo de nuevo más tarde.";
        }
        exit;
    } else {
        // Manejo de error: IDs inválidos
        echo "Parámetros inválidos.";
    }
} elseif (isset($_GET['accion'], $_GET['id_tiket']) && $_GET['accion'] === 'solucionar') {
    $idTiket = intval($_GET['id_tiket']);
    $idSoporte = intval($_SESSION['login_id']);
    $idError = intval($_POST['id_error']);
    $idSolucion = intval($_POST['id_solucion']);
    $descripcionSolucion = $_POST['descripcion_solucion'];

    if ($idTiket > 0 && $idSoporte > 0) {
        $tiketResuelto = $tiketController->resolverTiket($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion);
        if ($tiketResuelto) {
            header("Location: ../views/dashboard.php");
        } else {
            // Manejo de error: no se pudo resolver el ticket
            echo "No se pudo resolver el ticket. Inténtalo de nuevo más tarde.";
        }
        exit;
    } else {
        // Manejo de error: IDs inválidos
        echo "Parámetros inválidos.";
    }
} elseif (isset($_GET['accion'], $_GET['id_tiket']) && $_GET['accion'] === 'cerrarTiket') {
    $idTiket = intval($_GET['id_tiket']);
    $idUsuario = intval($_SESSION['login_id']);

    if ($idTiket > 0 && $idUsuario > 0) {
        $tiketResuelto = $tiketController->closeTicket($idTiket, $idUsuario);

        if ($tiketResuelto) {
            header("Location: ../views/dashboard.php");
        } else {
            // Manejo de error: no se pudo resolver el ticket
            echo "No se pudo resolver el ticket. Inténtalo de nuevo más tarde.";
        }
        exit;
    } else {
        // Manejo de error: IDs inválidos
        echo "Parámetros inválidos.";
    }
} elseif (isset($_GET['accion'], $_GET['id_tiket']) && $_GET['accion'] === 'calificarEncuesta') {
    $idTiket = intval($_GET['id_tiket']);
    $calificacion = intval($_POST['calificacion']);
    $comentarios = substr(trim($_POST['comentarios']), 0, 500);

    if ($idTiket > 0 && $calificacion >= 1 && $calificacion <= 5) {
        $encuestaCalificada = $encuestaController->calificarEncuesta($idTiket, $calificacion, $comentarios);

        if ($encuestaCalificada) {
            header("Location: ../views/dashboard.php");
        } else {
            // Manejo de error: no se pudo calificar la encuesta
            echo "No se pudo calificar la encuesta. Inténtalo de nuevo más tarde.";
        }
        exit;
    } else {
        // Manejo de error: parámetros inválidos
        echo "Parámetros inválidos.";
    }
} else {
    // Manejo de error: acción no reconocida
    echo "Acción no reconocida.";
}*/
