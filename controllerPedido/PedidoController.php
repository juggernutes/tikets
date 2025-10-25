<?php

class PedidoController
{
    private $pedidoModel;

    public function __construct($pedidoModel)
    {
        $this->pedidoModel = $pedidoModel;
    }

    public function getAllPedidos()
    {
        return $this->pedidoModel->getAllPedidos();
    }

    public function getPedidoById($id)
    {
        return $this->pedidoModel->getPedidoById($id);
    }

    public function createPedido($nombre, $descripcion)
    {
        return $this->pedidoModel->createPedido($nombre, $descripcion);
    }
}
