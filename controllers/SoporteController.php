<?php 
require_once '../models/proveedor.php';

class soporteController{
    private $proveedorModel;

    public function __construct($conn) {
        $this->proveedorModel = new Proveedor($conn);
    }

    public function getAllProveedores() {
        return $this->proveedorModel->obtenerProveedores();
    }

    public function getProveedorById($id) {
        return $this->proveedorModel->obtenerProveedorPorId($id);
    }    
}