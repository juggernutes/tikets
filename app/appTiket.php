<?php
ob_start(); // Iniciar el buffer de salida

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($ALLOW_PUBLIC) && !isset($_SESSION['login_id'])) {
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
require_once __DIR__ . '/../models/login.php';
require_once __DIR__ . '/../models/proveedor.php';

// Cargar los controladores necesarios
require_once __DIR__ . '/../controllers/solucionController.php';
require_once __DIR__ . '/../controllers/empleadoController.php';
require_once __DIR__ . '/../controllers/SistemaController.php';
require_once __DIR__ . '/../controllers/tiketController.php';
require_once __DIR__ . '/../controllers/errorModelController.php';
require_once __DIR__ . '/../controllers/encuestaController.php';
require_once __DIR__ . '/../controllers/equipoController.php';
require_once __DIR__ . '/../controllers/loginController.php';
require_once __DIR__ . '/../controllers/soporteController.php';
require_once __DIR__ . '/../controllers/SoporteController.php';
require_once __DIR__ . '/../components/ReportePdfProveedor.php';

// Crear instancias de los controladores y modelos
$sistemaController = new SistemaController($conn);
$empleadoController = new EmpleadoController($conn);
$tiketController = new TiketController(new Tiket($conn));
$errorController = new ErrorModelController(new ErrorModel($conn));
$solucionController = new SolucionController(new Solucion($conn));
$encuestaController = new EncuestaController($conn);
$equipoController = new EpicoController($conn);
$loginController = new LoginController($conn);
$soporteController = new soporteController($conn);


$usuarioId = $_SESSION['login_id'] ?? null;

if (isset($_GET['accion'])) {
    switch ($_GET['accion']) {
        case 'crearTiket': {
            $numEmpleado = (int) ($_POST['Numero_Empleado'] ?? 0);
            $idSistema = (int) ($_POST['id_sistema'] ?? 0);
            $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
            $proveedor = (int) ($_POST['proveedor'] ?? 0);

            // Evita que te rompan el log con saltos de línea
            $descLog = str_replace(["\r", "\n"], [' ', ' '], $descripcion);

            /*file_put_contents(
                __DIR__ . '/../crearTiket.log',
                date('Y-m-d H:i:s') . " | EMPLEADO: $numEmpleado | SISTEMA: $idSistema | DESCRIPCION: $descLog | PROVEEDOR: $proveedor\n",
                FILE_APPEND
            );*/

            if ($numEmpleado <= 0 || $idSistema <= 0 || $descripcion === '') {
                http_response_code(400);
                echo "Parámetros inválidos.";
                break;
            }

            // ======= DECISIÓN POR PROVEEDOR =======
            $tiketCreado = $tiketController->createTicket($numEmpleado, $idSistema, $descripcion);

            /*file_put_contents(
                __DIR__ . '/../crearTiket.log',
                date('Y-m-d H:i:s') . " | TICKET CREADO ID: " . ($tiketCreado ? $tiketCreado : 'FALLIDO') . "\n",
                FILE_APPEND
            );*/

            if ($tiketCreado && $proveedor > 0) {
                $tiketController->enviarTiketProveedor($tiketCreado, $proveedor);
            }


            if ($tiketCreado) {
                header("Location: ../views/dashboard.php");
                exit;
            }

            http_response_code(500);
            echo "No se pudo crear el ticket. Inténtalo de nuevo más tarde.";
            break;
        }

        case 'tomarTiket':
            $idTiket = intval($_GET['id_tiket']);
            $idSoporte = intval($_SESSION['login_id']);

            if ($idTiket > 0 && $idSoporte > 0) {
                $tiketTomado = $tiketController->tomarControlDeTiket($idTiket, $idSoporte);

                header("Location: ../views/resolver_tiket.php?id=$idTiket");

                exit;
            } else {
                echo "Parámetros inválidos.";
            }
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
        case 'buscarUsuario':
            //manejo de busquda de correo
            if (isset($_POST['correo'])) {
                $correo = $_POST['correo'];
                //$loginController->buscarUsuario($correo);
            } else {
                echo "Correo no encontrado.";
            }
            break;
        case 'cancelarTiket':
            //Manejo de cancelacion
            $idTiket = intval($_GET['id_tiket']);
            $idUsuario = intval($_SESSION['login_id']);

            if ($idTiket > 0) {
                $ok = $tiketController->cancelarTiket($idTiket, $idUsuario);
            } else {
                echo "No hay accion";
            }
            break;
        case 'tiket.avance':
            $idTiket = intval($_GET['id_tiket']);
            $idSoporte = intval($_SESSION['login_id']);
            $idError = intval($_POST['id_error']) || 42;
            $idSolucion = intval($_POST['id_solucion']) || 12;
            $descripcionSolucion = $_POST['descripcion_solucion'];

            if ($idTiket > 0 && $idSoporte > 0) {
                $tiketResuelto = $tiketController->avanzarTiket($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion);
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

        case 'enviarProveedor':
            $idTiket = intval($_GET['id_tiket']);
            $idSoporte = intval($_GET['id_proveedor']);

            file_put_contents(
                __DIR__ . '/../debug_enviarProveedor.log',
                date('Y-m-d H:i:s') . " | idTiket: $idTiket | idSoporte: $idSoporte\n",
                FILE_APPEND
            );

            if ($idTiket > 0 && $idSoporte > 0) {
                $tiketResuelto = $tiketController->enviarTiketProveedor($idTiket, $idSoporte);
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

        case 'resetPasswordUsuario':
            $idUsuario = intval($_POST['id'] ?? 0);
            $nuevaPassword = '12345';  // o genera una aleatoria

            if ($idUsuario > 0 && $loginController->resetearPassword($idUsuario, $nuevaPassword)) {
                header("Location: ../views/usuarios.php");
                exit;
            } else {
                echo "No se pudo cambiar la contraseña. Inténtalo de nuevo más tarde.";
            }
            break;

        case 'nuevoSistema':
            $nombre = trim($_GET['nombre'] ?? '');
            $descripcion = trim($_GET['descripcion'] ?? '');

            header('Content-Type: application/json');
            // DEBUG: registrar datos que llegan al app
            file_put_contents(
                __DIR__ . '/../debug_nuevoSistema.log',
                date('Y-m-d H:i:s') . " | REQUEST: " . print_r($_REQUEST, true) . "\n",
                FILE_APPEND
            );


            if ($nombre === '') {
                echo json_encode(['ok' => false, 'error' => 'El nombre del sistema es obligatorio.']);
                exit;
            }

            $nuevoSistemaId = $sistemaController->crearNuevoSistema($nombre, $descripcion);
            if ($nuevoSistemaId) {
                echo json_encode(['ok' => true, 'id' => $nuevoSistemaId]);
            } else {
                echo json_encode(['ok' => false, 'error' => 'No se pudo crear el sistema.']);
            }
            exit;

        case 'nuevoError':
            header('Content-Type: application/json; charset=utf-8');

            $nombreError = trim($_GET['nombre'] ?? '');

            if ($nombreError === '') {
                echo json_encode([
                    'ok' => false,
                    'error' => 'El nombre del error es obligatorio.'
                ]);
                exit;
            }

            $nuevoErrorId = $errorController->crearNuevoError($nombreError);

            if ($nuevoErrorId) {
                echo json_encode([
                    'ok' => true,
                    'id' => $nuevoErrorId
                ]);
            } else {
                echo json_encode([
                    'ok' => false,
                    'error' => 'No se pudo crear el error.'
                ]);
            }
            exit;


        case 'nuevoSolucion':
            header('Content-Type: application/json; charset=utf-8');

            $nombreSolucion = trim($_GET['nombre'] ?? '');

            if ($nombreSolucion === '') {
                echo json_encode([
                    'ok' => false,
                    'error' => 'El nombre de la solución es obligatorio.'
                ]);
                exit;
            }

            $nuevaSolucionId = $solucionController->crearNuevaSolucion($nombreSolucion);

            if ($nuevaSolucionId) {
                echo json_encode([
                    'ok' => true,
                    'id' => $nuevaSolucionId
                ]);
            } else {
                echo json_encode([
                    'ok' => false,
                    'error' => 'No se pudo crear la solución.'
                ]);
            }
            exit;

        case 'reporteProveedor':
            $idProveedor = trim($_GET['ID_PROVEEDOR'] ?? 0);
            $fecha = trim($_GET['fecha'] ?? '');

            //('Content-Type: application/json; charset=utf-8');

            $logData = [
                'fecha_log' => date('Y-m-d H:i:s'),
                'GET' => $_GET,
                'idProveedor' => $idProveedor,
                'fecha' => $fecha
            ];

            file_put_contents(
                __DIR__ . '/../reporteProveedor.log',
                json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL,
                FILE_APPEND
            );

            if ($idProveedor <= 0) {
                echo json_encode([
                    'ok' => false,
                    'error' => 'ID de proveedor inválido.'
                ]);
                exit;
            }

            $reporte = $tiketController->datosReportePorProveedor($idProveedor, $fecha);

            /*file_put_contents(
                __DIR__ . '/../reporteProveedor.log',
                date('Y-m-d H:i:s') . " | REPORTE: " . json_encode($reporte, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL,
                FILE_APPEND
            );

            echo json_encode([
                'ok'     => true,
                'reporte' => $reporte
            ]);*/

            // ====== Traer proveedor (después) ======
            $proveedor = $soporteController->getProveedorById($idProveedor) ?? [];

            $nombreProv = $proveedor['NOMBRE'] ?? 'Desconocido';
            $emailProv = $proveedor['EMAIL'] ?? 'Desconocido';
            $phoneProv = $proveedor['TELEFONO'] ?? 'Desconocido';

            $fechaConsulta = date('Y-m-d');

            file_put_contents(
                __DIR__ . '/../reporteProveedor.log',
                date('Y-m-d H:i:s') . " | PROVEEDOR: " . json_encode($proveedor, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL,
                FILE_APPEND
            );


            ReportesPdfProveedor::outputReporteProveedorFPDF(
                $reporte,
                [
                    'filename' => "reporte_proveedor_{$nombreProv}_{$fecha}.pdf",
                    'nombreProv' => $nombreProv,
                    'emailProv' => $emailProv,
                    'phoneProv' => $phoneProv,
                    'fechaReporte' => $fechaConsulta
                ]
            );
            break;

        case 'reporteSemanal':
            $anio = (int) ($_GET['anio'] ?? date('Y'));
            $semana = (int) ($_GET['Semana'] ?? $_GET['semana'] ?? date('W'));

            file_put_contents(
                __DIR__ . '/../reporteSemanal.log',
                date('Y-m-d H:i:s') . " | ANIO: $anio | SEMANA: $semana\n",
                FILE_APPEND
            );

            $reporteSemanal = $soporteController->datosReporteSemanal($anio, $semana);

            ReportesPdfProveedor::reporteSemanal(
                $reporteSemanal,
                [
                    'filename' => "reporte_semanal_{$anio}_semana_{$semana}.pdf",
                    'anio' => $anio,
                    'semana' => $semana
                ]
            );
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
