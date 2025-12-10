<?php 
require_once __DIR__ . '/../models/proveedor.php';

class soporteController{
    private $proveedorModel;

    public function __construct($conn) {
        $this->proveedorModel = new Proveedor($conn);
    }

    public function getAllProveedores() {
        return $this->proveedorModel->obtenerProveedores();
    }

    
}