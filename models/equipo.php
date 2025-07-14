<?php
require_once __DIR__ . '/../helpers/encryption_helper.php';

class equipo{
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function obtenerEquiposEmpleado($numeroEmpleado){
        $equipos = [];
        if ($stmt = $this->conn->prepare("CALL sp_equipo(?,?)")){
            $op = 1;
            $stmt->bind_param("ii",$op,$numeroEmpleado);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()){
                $equipos[] = $row;
            }
            $stmt->close();
            while($this->conn->more_results()&& $this->conn->next_result()){
                $this ->conn->use_result();
            }
        }
        return $equipos;
    }

    

}