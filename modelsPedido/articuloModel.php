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
        $stmt = $this->conn->prepare("SELECT * FROM articulos");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArticuloById($id)
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
    }
}