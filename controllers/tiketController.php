<?php
class TiketController
{
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getTicketsByUserId($usuarioId)
    {
        return $this->model->getTiketsByUser($usuarioId);
    }

    public function createTicket($numEmpleado, $idSistema, $descripcion)
    {
        return $this->model->creartiket($numEmpleado, $idSistema, $descripcion);
    }

    public function getTicketById($idTiket)
    {
        return $this->model->getTiketById($idTiket);
    }

    public function getAllTickets($idSoporte)
    {
        return $this->model->obtenerTodosTikets($idSoporte);
    }

    public function updateTicket($idTiket, $numEmpleado, $idSistema, $descripcion, $idSoporte, $idSolucion)
    {
        return $this->model->updatetiket($idTiket, $numEmpleado, $idSistema, $descripcion, $idSoporte, $idSolucion);
    }

    public function assignSupport($idTiket, $idSoporte)
    {
        return $this->model->asignarSoporte($idTiket, $idSoporte);
    }

    public function tomarControlDeTiket($idTiket, $idSoporte)
    {
        if ($this->model->tomarTiket($idTiket, $idSoporte)) {
            header("Location: ../views/resolver_tiket.php?id=$idTiket");
            exit;
        } else {
            return false;
        }
    }

    public function resolverTiket($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion)
    {
        // Validar que los IDs sean válidos
        if (empty($idTiket) || empty($idSoporte) || empty($idError) || empty($idSolucion) || empty($descripcionSolucion)) {
            echo "Todos los campos son obligatorios.";
            return false;
        } elseif ($this->model->resolver($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion)) {
            header("Location: ../views/dashboard.php");
            exit;
        } else {
            return false;
        }
    }

    public function closeTicket($idTiket, $idUsuario)
    {
        if ($this->model->cerrar($idTiket, $idUsuario)) {
            header("Location: ../views/encuesta.php?id_tiket=$idTiket");
            exit;
        } else {
            return false;
        }
    }

    public function getTicketsCerrados($idUsuario, $rol)
    {
        //echo "Rol: $rol";
        //echo "ID Usuario: $idUsuario";
        if ($rol === 'EMPLEADO') {
            return $this->model->getTicketsCerradosPorEmpleado($idUsuario);
        } elseif ($rol === 'SOPORTE' || $rol === 'ADMINISTRADOR') {
            return $this->model->getTicketsCerrados();
        }
        return false;
    }

    public function activarTiket($idTiket)
    {
        if ($this->model->activarTiket($idTiket)) {
            header("Location: ../views/detalles_tiket.php?ID_Tiket=$idTiket");
            exit;
        } else {
            return false;
        }
    }

    public function cancelarTiket($idTiket, $idUsuario) {
        $idError = 29;
        $idSolucion = 11;
        $descripcionSolucion = "CANCELADO POR EL USUARIO";

        if($this->model->resolver($idTiket, $idUsuario, $idError, $idSolucion, $descripcionSolucion)){
            header("Location: ../views/dashboard.php");
        }else{
            echo "BUELBE A INTENTARLO";
        }
    }

    public function avanzarTiket($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion)
    {
        // Validar que los IDs sean válidos
        if (empty($idTiket) || empty($idSoporte) || empty($descripcionSolucion)) {
            echo "Descripcion obligatoria.";
            return false;
        } elseif ($this->model->updatetiket($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion)) {
            header("Location: ../views/dashboard.php");
            exit;
        } else {
            return false;
        }
    }

    public function getTicketbyProveedor($usuario)
    {
        return $this->model->getTicketbyProveedor($usuario);
    }

    public function enviarTiketProveedor($idTiket, $idSoporte)
    {
        return $this->model->enviarTiketProveedor($idTiket, $idSoporte);
    }

    public function datosReportePorProveedor($idProveedor, $fecha)
    {
        return $this->model->datosReportePorProveedor($idProveedor, $fecha);
    }
}
