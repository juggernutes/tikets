<?php
require_once __DIR__ . '/../models/empleado.php';

class EmpleadoController {
    private Empleado $empleadoModel;

    public function __construct(mysqli $conn) {
        $this->empleadoModel = new Empleado($conn);
    }

    /**
     * Listado de empleados.
     * - Si $idsucursal es NULL => lista activos (sp_Empleado op=1).
     * - Si $idsucursal tiene valor => filtra por sucursal (consulta directa del modelo).
     */
    public function obtenerEmpleados(?int $idsucursal = null): array {
        try {
            if ($idsucursal !== null) {
                return $this->empleadoModel->obtenerEmpleadosPorSucursal($idsucursal);
            }
            return $this->empleadoModel->listarActivos();
        } catch (Throwable $e) {
            // log_error($e); // si tienes logger
            return [];
        }
    }

    /**
     * Básico por número de empleado (activo).
     * Mantengo el nombre original para compatibilidad.
     */
    public function obtenerEmpleadoPorId(int $numeroEmpleado): ?array {
        try {
            return $this->empleadoModel->obtenerEmpleadoActivoPorNumero($numeroEmpleado);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Listado básico (activos) con columnas resumidas (sp_Empleado op=3).
     */
    public function obtenerTodosLosEmpleados(): array {
        try {
            return $this->empleadoModel->listarBasico();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Detalle completo por número (incluye usuario/área y desencripta credenciales).
     * Respeta tu nombre original obtenerEmpleadoporNumeroC().
     */
    public function obtenerEmpleadoporNumeroC(int $numeroEmpleado): ?array {
        try {
            return $this->empleadoModel->obtenerDetalle($numeroEmpleado);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Sucursales activas (para combos).
     */
    public function obtenerSucursales(): array {
        try {
            return $this->empleadoModel->obtenerSucursales();
        } catch (Throwable $e) {
            return [];
        }
    }

    /* ============================
       Nuevos métodos (Altas/Actualización)
       ============================ */

    /**
     * Alta de empleado (creación). Encripta campos sensibles en el modelo.
     * Espera un arreglo con claves coherentes a la tabla (ver modelo->crearEmpleado()).
     */
    public function crearEmpleado(array $data): bool {
        try {
            return $this->empleadoModel->crearEmpleado($data);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Actualiza empleado por número. Solo actualiza los campos provistos.
     */
    public function actualizarEmpleado(int $numeroEmpleado, array $data): bool {
        try {
            return $this->empleadoModel->actualizarEmpleado($numeroEmpleado, $data);
        } catch (Throwable $e) {
            return false;
        }
    }

    /* ============================
       (Opcionales) Filtros extra
       ============================ */

    public function obtenerEmpleadosPorSucursal(int $sucursalId): array {
        try {
            return $this->empleadoModel->obtenerEmpleadosPorSucursal($sucursalId);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function obtenerEmpleadosPorPuesto(int $puestoId): array {
        try {
            return $this->empleadoModel->obtenerEmpleadosPorPuesto($puestoId);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function obtenerEmpleadosPorArea(int $areaId): array {
        try {
            return $this->empleadoModel->obtenerEmpleadosPorArea($areaId);
        } catch (Throwable $e) {
            return [];
        }
    }
}
