<?php

require_once __DIR__ . '/../modelsPedido/articuloModel.php';

class ArticuloController
{
    private $articuloModel;

    public function __construct($conn)
    {
        $this->articuloModel = new ArticuloModel($conn);
    }

    public function getAllArticulos()
    {
        return $this->articuloModel->getAllArticulos();
    }

   

}
