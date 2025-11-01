<?php
class UnidadOperacionalModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllUnidadesOperacionales()
    {
        $stmt = $this->conn->prepare("SELECT * FROM unidades_operacionales");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnidadOperacionalById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM unidades_operacionales WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getIdUsuario($usuario) 
    {
        $unidaddeventa = [];
        $op = 3;
        if($stmt = $this->conn->prepare("CALL sp_unidad_operacional(?,null, ?)")) {
            $stmt->bind_param("ii", $op, $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $unidaddeventa[] = $row;
            }
            $stmt->close();
            while ($this->conn->more_results() && $this->conn->next_result()) {
                $this->conn->use_result();
            }
        }

        return $unidaddeventa;
    }


}