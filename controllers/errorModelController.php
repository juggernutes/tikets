<?php

class ErrorModelController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function getAllErrors() {
        return $this->model->obtenerErrores();
    }

    public function createError($descripcion, $tipoError) {
        return $this->model->insertarError($descripcion, $tipoError);
    }

    public function updateError($idError, $descripcion, $tipoError) {
        return $this->model->actualizarError($idError, $descripcion, $tipoError);
    }

    public function deleteError($idError) {
        return $this->model->eliminarError($idError);
    }

    public function getErrorById($idError) {
        $errors = $this->model->obtenerErrores();
        foreach ($errors as $error) {
            if ($error['ID_Error'] == $idError) {
                return $error;
            }
        }
        return null; // Si no se encuentra el error
    }
}