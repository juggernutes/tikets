<?php

class EncuestaController {
    private $encuestaModel;

    public function __construct($dbConnection) {
        $this->encuestaModel = new Encuesta($dbConnection);
    }
    
    public function calificarEncuesta($idTiket, $calificacion, $comentarios) {
        return $this->encuestaModel->calificarEncuesta($idTiket, $calificacion, $comentarios);
    }

    public function getEncuestaByIdTiket($idTiket) {
        return $this->encuestaModel->getEncuestaByIdTiket($idTiket);
    }
}
