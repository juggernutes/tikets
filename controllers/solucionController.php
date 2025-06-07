<?php

class SolucionController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function getAllSoluciones() {
        return $this->model->obtenerTodasSoluciones();
    }

    public function getSolucionById($id) {
        return $this->model->obtenerSolucionPorId($id);
    }
}
