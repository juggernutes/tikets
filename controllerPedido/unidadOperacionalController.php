<?php

require_once __DIR__ . '/../modelsPedido/unidadOperacionalModel.php';

class UnidadOperacionalController
{
    private $unidadOperacionalModel;

    public function __construct($conn)
    {
        $this->unidadOperacionalModel = new UnidadOperacionalModel($conn);
    }

    public function getAllUnidadesOperacionales()
    {
        return $this->unidadOperacionalModel->getAllUnidadesOperacionales();
    }

    public function getUnidadOperacionalById($id)
    {
        return $this->unidadOperacionalModel->getUnidadOperacionalById($id);
    }

    public function getIdUsuario($usuario)
    {
        return $this->unidadOperacionalModel->getIdUsuario($usuario);
    }
}
