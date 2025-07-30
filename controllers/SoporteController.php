<?php 
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'SOPORTE') {
    header("Location: ../public/index.php");
    exit;
}

require_once __DIR__ . '/../models/login.php';

class soporteController{
    private $loginModel;

    public function __construct($conn) {
        $this->loginModel = new Login($conn);
    }

    
}

?>