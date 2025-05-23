<?php
require_once '../models/Sistema.php';

class SistemaController {
    private $sistemaModel;

    public function __construct($conn) {
        // Asumes que estás pasando la conexión desde fuera (buena práctica para inyección de dependencias)
        $this->sistemaModel = new Sistema($conn);
    }

    public function obtenerSistemas() {
        return $this->sistemaModel->obtenerTodos();
    }

    public function obtenerSistemaPorId($id) {
        return $this->sistemaModel->obtenerPorId($id);
    }

    public function comboSistemas($selected = null) {
        $sistemas = $this->obtenerSistemas();
        $options = '';

        foreach ($sistemas as $sistema) {
            $isSelected = ($selected !== null && $selected == $sistema['ID_Sistema']) ? ' selected' : '';
            $options .= '<option value="' . htmlspecialchars($sistema['ID_Sistema']) . '"' . $isSelected . '>' . htmlspecialchars($sistema['Nombre']) . '</option>';
        }

        return $options;
    }

    public function obtenerDescripcionSistema($id) {
        $sistema = $this->obtenerSistemaPorId($id);
        return $sistema ? $sistema['Descripcion'] : 'Descripción no disponible';
    }
}
