<?php
require_once '../models/equipo.php';

class EpicoController{
    private $equipoModel;

    public function __construct($conn){
        $this->equipoModel = new equipo($conn);
    }

    public function obtenerEquiposporEmpleado($numeroEmpleado){
        return $this->equipoModel->obtenerEquiposEmpleado($numeroEmpleado);
    }

    
}