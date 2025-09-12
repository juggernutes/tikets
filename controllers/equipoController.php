<?php
require_once '../models/equipo.php';

class EquipoController{
    private $equipoModel;

    public function __construct($conn){
        $this->equipoModel = new equipo($conn);
    }

    public function obtenerEquiposporEmpleado($numeroEmpleado){
        return $this->equipoModel->obtenerEquiposEmpleado($numeroEmpleado);
    }

    public function actualizarCampoDeEquipo($idEquipo, $campo, $valor) {
        return $this->equipoModel->actualizarCampoDeEquipo($idEquipo, $campo, $valor);
    }    
}