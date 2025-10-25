<?php

class UnidadOperacionalController
{
    private $unidadOperacionalModel;

    public function __construct($unidadOperacionalModel)
    {
        $this->unidadOperacionalModel = $unidadOperacionalModel;
    }

    public function getAllUnidadesOperacionales()
    {
        return $this->unidadOperacionalModel->getAllUnidadesOperacionales();
    }

    public function getUnidadOperacionalById($id)
    {
        return $this->unidadOperacionalModel->getUnidadOperacionalById($id);
    }

    public function createUnidadOperacional($nombre, $descripcion)
    {
        return $this->unidadOperacionalModel->createUnidadOperacional($nombre, $descripcion);
    }
}
