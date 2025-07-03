<?php
require_once '../models/empleado.php';

class EmpleadoController {
    private $empleadoModel;

    public function __construct($conn) {
        $this->empleadoModel = new Empleado($conn);
    }

    public function obtenerEmpleados($idUsuario) {
        return $this->empleadoModel->obtenerEmpleados($idUsuario);
    }

    public function obtenerEmpleadoPorId($numeroEmpleado) {
        return $this->empleadoModel->obtenerEmpleadoPorId($numeroEmpleado);
    }

    public function obtenerTodosLosEmpleados() {
        return $this->empleadoModel->obtenerTodosLosEmpleados();
    }

    public function obtenerEmpleadoporNumeroC($numeroEmpleado) {
        return $this->empleadoModel->obtenerEmpleadoporNumero($numeroEmpleado);
    }
}
