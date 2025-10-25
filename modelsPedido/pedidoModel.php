<?php
class PedidoModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllPedidos()
    {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPedidoById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createPedido($nombre, $descripcion)
    {
        $stmt = $this->conn->prepare("INSERT INTO pedidos (nombre, descripcion) VALUES (:nombre, :descripcion)");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        return $stmt->execute();
    }
}