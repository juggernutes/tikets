<?php

class ArticuloController
{
    private $articuloModel;

    public function __construct($articuloModel)
    {
        $this->articuloModel = $articuloModel;
    }

    public function getAllArticulos()
    {
        return $this->articuloModel->getAllArticulos();
    }

    public function getArticuloById($id)
    {
        return $this->articuloModel->getArticuloById($id);
    }

    public function createArticulo($nombre, $descripcion)
    {
        return $this->articuloModel->createArticulo($nombre, $descripcion);
    }
}
