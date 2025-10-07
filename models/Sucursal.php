<?php
class Sucursal
{
    private mysqli $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listarActivas(): array
    {
        $sql = 'SELECT ID_Sucursal, Nombre, Ciudad FROM sucursal WHERE Active = 1 ORDER BY Nombre';
        $rows = [];
        if ($result = $this->conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->free();
        }
        return $rows;
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT ID_Sucursal, Nombre, Ciudad, Active FROM sucursal WHERE ID_Sucursal = ?';
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $data = null;
            if ($result = $stmt->get_result()) {
                $data = $result->fetch_assoc() ?: null;
                $result->free();
            }
            $stmt->close();
            return $data;
        }
        return null;
    }

    public function crear(string $nombre, ?string $ciudad = null, bool $activo = true): bool
    {
        $sql = 'INSERT INTO sucursal (Nombre, Ciudad, Active) VALUES (?, ?, ?)';
        $flag = $activo ? 1 : 0;
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('ssi', $nombre, $ciudad, $flag);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }

    public function actualizar(int $id, string $nombre, ?string $ciudad = null, bool $activo = true): bool
    {
        $sql = 'UPDATE sucursal SET Nombre = ?, Ciudad = ?, Active = ? WHERE ID_Sucursal = ?';
        $flag = $activo ? 1 : 0;
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('ssii', $nombre, $ciudad, $flag, $id);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $sql = 'UPDATE sucursal SET Active = ? WHERE ID_Sucursal = ?';
        $flag = $activo ? 1 : 0;
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('ii', $flag, $id);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }
}