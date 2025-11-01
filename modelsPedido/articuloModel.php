<?php

class ArticuloModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllArticulos()
    {
        $articulos = [];
        $stmt = $this->conn->prepare("CALL sp_Articulos(?, null, null, null)");
        $op = 1;
        $stmt->bind_param("i", $op);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $articulos[] = $row;
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        return $articulos;
    }

    /*public function getArticuloById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM articulos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createArticulo($nombre, $descripcion, $precio)
    {
        $stmt = $this->conn->prepare("INSERT INTO articulos (nombre, descripcion, precio) VALUES (:nombre, :descripcion, :precio)");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':precio', $precio, PDO::PARAM_STR);
        return $stmt->execute();
    }*/
}