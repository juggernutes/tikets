<?php
require_once __DIR__ . '/../helpers/encryption_helper.php';

class Empleado
{

    private $conn;



    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function obtenerEmpleados($userId)
    {
        $empleados = [];

        if ($stmt = $this->conn->prepare("CALL sp_empleados(?, ?, null)")) {
            $op = 1;
            $stmt->bind_param("ii", $op, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $empleados[] = $row;
            }
            $stmt->close();
            while ($this->conn->more_results() && $this->conn->next_result()) {
                $this->conn->use_result();
            }
        }

        return $empleados;
    }

    public function obtenerEmpleadoPorId($numeroEmpleado)
    {
        $sql = "CALL sp_empleados(2, ?, 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $numeroEmpleado);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function obtenerTodosLosEmpleados()
    {
        $op = 3;
        $params = [null, null];

        $stmt = $this->conn->prepare("CALL sp_empleados(?, ?, ?)");
        $stmt->bind_param("iii", $op, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $empleados = [];
        while ($row = $result->fetch_assoc()) {
            $empleados[] = $row;
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        return $empleados;
    }

    public function obtenerEmpleadoporNumero($numeroEmpleado)
    {
        $op = 4;
        $userId = null;
        $sql = "CALL sp_empleados(?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $op, $userId, $numeroEmpleado);
        $stmt->execute();
        $result = $stmt->get_result();
        $empleado = $result->fetch_assoc();

        if (!$empleado) {
            return null; // No se encontró el empleado
        }
        // Desencriptar los campos sensibles
        $empleado['UsuarioSAP'] = !empty($empleado['UsuarioSAP']) ? desencriptar($empleado['UsuarioSAP']) : null;
        $empleado['ClaveSAP'] = !empty($empleado['ClaveSAP']) ? desencriptar($empleado['ClaveSAP']) : null;
        $empleado['ClaveUsuarioWindows'] = !empty($empleado['ClaveUsuarioWindows']) ? desencriptar($empleado['ClaveUsuarioWindows']) : null;
        $empleado['UsuarioAnyDesk'] = !empty($empleado['UsuarioAnyDesk']) ? desencriptar($empleado['UsuarioAnyDesk']) : null;
        $empleado['ClaveAnyDesk'] = !empty($empleado['ClaveAnyDesk']) ? desencriptar($empleado['ClaveAnyDesk']) : null;
        $empleado['ClaveCorreo'] = !empty($empleado['ClaveCorreo']) ? desencriptar($empleado['ClaveCorreo']) : null;
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        // Retornar el empleado con los datos desencriptados 
        return $empleado;
    }

    
    public function guardarDatosEncripados($empleado)
    {
        $op = 5;
        $usuario = $empleado['USUARIO'] ?? null;
        $numeroEmpleado = $empleado['Numero_Empleado'] ?? null;
        $nombre = $empleado['Nombre'] ?? null;
        $puesto = $empleado['PUESTO'] ?? null;
        $sucursal = $empleado['SUCURSAL'] ?? null;
        $area = $empleado['AREA'] ?? null;
        $correo = $empleado['Correo'] ?? null;
        $telefono = $empleado['Telefono'] ?? null;
        $extencion = $empleado['Extencion'] ?? null;
        $claveCorreo = !empty($empleado['ClaveCorreo']) ? encriptar($empleado['ClaveCorreo']) : null;
        $usuariAnyDesk = !empty($empleado['UsuarioAnyDesk']) ? encriptar($empleado['UsuarioAnyDesk']) : null;
        $claveAnyDesk = !empty($empleado['ClaveAnyDesk']) ? encriptar($empleado['ClaveAnyDesk']) : null;
        $usuarioSAP = !empty($empleado['UsuarioSAP']) ? encriptar($empleado['UsuarioSAP']) : null;
        $claveSAP = !empty($empleado['ClaveSAP']) ? encriptar($empleado['ClaveSAP']) : null;
        $claveUsuarioWindows = !empty($empleado['ClaveUsuarioWindows']) ? encriptar($empleado['ClaveUsuarioWindows']) : null;

        $stmt = $this->conn->prepare("CALL sp_empleados(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isssssssssssssss", 
            $op,
            $numeroEmpleado,
            $usuario,
            $nombre,
            $puesto,
            $sucursal,
            $area,
            $correo,
            $telefono,
            $extencion,
            $claveSAP,
            $usuarioSAP,
            $claveUsuarioWindows,
            $usuariAnyDesk,
            $claveAnyDesk,
            $claveCorreo
        );

        $stmt->execute();
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }

        return $stmt->affected_rows > 0;
    }

    public function obtenerEmpleadosPorSucursal($sucursalId)
    {
        $empleados = [];
        $stmt = $this->conn->prepare("CALL sp_empleados(6, ?, null)");
        $stmt->bind_param("i", $sucursalId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $empleados[] = $row;
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        return $empleados;
    }

    public function obtenerEmpleadosPorPuesto($puestoId)
    {
        $empleados = [];
        $stmt = $this->conn->prepare("CALL sp_empleados(7, ?, null)");
        $stmt->bind_param("i", $puestoId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $empleados[] = $row;
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        return $empleados;
    }
    public function obtenerEmpleadosPorArea($areaId)
    {
        $empleados = [];
        $stmt = $this->conn->prepare("CALL sp_empleados(8, ?, null)");
        $stmt->bind_param("i", $areaId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $empleados[] = $row;
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        return $empleados;
    }

}
