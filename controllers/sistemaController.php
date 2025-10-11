<?php
require_once __DIR__ . '/../models/Sistema.php';

class SistemaController {
    private Sistema $sistemaModel;

    public function __construct(mysqli $conn) {
        $this->sistemaModel = new Sistema($conn);
    }

    /** Listar activos (por compatibilidad con tu vista) */
    public function obtenerSistemas(): array {
        try {
            return $this->sistemaModel->listarActivos();
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Listar todos (activos e inactivos) */
    public function obtenerTodos(): array {
        try {
            return $this->sistemaModel->listarTodos();
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Obtener por ID (solo si está activo, conforme al SP) */
    public function obtenerSistemaPorId(int $id): ?array {
        try {
            return $this->sistemaModel->obtenerPorId($id);
        } catch (Throwable $e) {
            return null;
        }
    }

    /** Crear sistema: devuelve ID creado o null */
    public function crearSistema(string $nombre, ?string $descripcion = null): ?int {
        try {
            return $this->sistemaModel->crear($nombre, $descripcion);
        } catch (Throwable $e) {
            return null;
        }
    }

    /** Actualizar: devuelve true si afectó filas */
    public function actualizarSistema(int $id, ?string $nombre = null, ?string $descripcion = null, ?int $active = null): bool {
        try {
            $rows = $this->sistemaModel->actualizar($id, $nombre, $descripcion, $active);
            return $rows > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** Desactivar sistema (Active = 0) */
    public function desactivarSistema(int $id): bool {
        try {
            return $this->sistemaModel->desactivar($id) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** Reactivar sistema (Active = 1) */
    public function reactivarSistema(int $id): bool {
        try {
            return $this->sistemaModel->reactivar($id) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** Buscar por texto; $soloEstado: null (todos) | 1 (activos) | 0 (inactivos) */
    public function buscarSistemas(string $texto, ?int $soloEstado = null): array {
        try {
            return $this->sistemaModel->buscar($texto, $soloEstado);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Generar <option> para <select>.
     * $selected: ID_Sistema seleccionado o null
     * $incluirInactivos: si true, usa listarTodos(); si false, solo activos.
     */
    public function comboSistemas(?int $selected = null, bool $incluirInactivos = false): string {
        $sistemas = $incluirInactivos ? $this->obtenerTodos() : $this->obtenerSistemas();
        $options = '';

        foreach ($sistemas as $sistema) {
            $id   = htmlspecialchars((string)($sistema['ID_Sistema'] ?? ''), ENT_QUOTES, 'UTF-8');
            $name = htmlspecialchars((string)($sistema['Nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
            $sel  = ($selected !== null && (int)$selected === (int)$id) ? ' selected' : '';
            $badge = (isset($sistema['Active']) && (int)$sistema['Active'] === 0) ? ' (inactivo)' : '';
            $options .= "<option value=\"{$id}\"{$sel}>{$name}{$badge}</option>";
        }

        return $options;
    }
}
