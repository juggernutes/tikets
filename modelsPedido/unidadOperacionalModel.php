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

    


}