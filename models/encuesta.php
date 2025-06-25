<?php

class Encuesta {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function calificarEncuesta($idTiket, $calificacion, $comentarios) {
        $op = 1;
        $params = [$idTiket, $calificacion, $comentarios];
        $stmt = $this->conn->prepare("CALL sp_encuesta(?, ?, ?, ?)");
        $stmt->bind_param('iiis', $op, ...$params);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }
}
