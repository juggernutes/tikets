<?php
require_once '../models/empleado.php';

class EmpleadoController {
    private $empleadoModel;

    public function __construct($conn) {
        $this->empleadoModel = new Empleado($conn);
    }

    public function obtenerEmpleados($idsucursal) {
        return $this->empleadoModel->obtenerEmpleados($idsucursal);
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

    public function obtenerSucursales(){
        return $this->empleadoModel->obtenerSucursales();
    }
}
