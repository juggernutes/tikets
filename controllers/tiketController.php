<?php
class TiketController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function getTicketsByUserId($usuarioId) {
        return $this->model->getTiketsByUser($usuarioId);
    }

    public function createTicket($numEmpleado, $idSistema, $descripcion) {
        return $this->model->creartiket($numEmpleado, $idSistema, $descripcion);
    }

    public function getTicketById($idTiket) {
        return $this->model->getTiket($idTiket);
    }

    public function getAllTickets($idSoporte) {
        return $this->model->obtenerTodosTikets($idSoporte);
    }

    public function updateTicket($idTiket, $numEmpleado, $idSistema, $descripcion, $idSoporte, $idSolucion) {
        return $this->model->updatetiket($idTiket, $numEmpleado, $idSistema, $descripcion, $idSoporte, $idSolucion);
    }

    public function assignSupport($idTiket, $idSoporte) {
        return $this->model->asignarSoporte($idTiket, $idSoporte);
    }

    public function tomarControlDeTiket($idTiket, $idSoporte) {
        if ($this->model->tomarTiket($idTiket, $idSoporte)) {
            header("Location: ../views/resolver_tiket.php?id_tiket=" . $idTiket);
            exit;
        } else {
            return false;
        }
    }
}
