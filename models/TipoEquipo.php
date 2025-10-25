<?php
class TipoEquipo
{
    private mysqli $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listarActivos(): array
    {
        $sql = 'SELECT ID_TipoEquipo, Nombre, Descripcion FROM tipo_equipo WHERE Active = 1 ORDER BY Nombre';
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
        $sql = 'SELECT ID_TipoEquipo, Nombre, Descripcion, Active FROM tipo_equipo WHERE ID_TipoEquipo = ?';
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

    public function crear(string $nombre, ?string $descripcion = null, bool $activo = true): bool
    {
        $sql = 'INSERT INTO tipo_equipo (Nombre, Descripcion, Active) VALUES (?, ?, ?)';
        $flag = $activo ? 1 : 0;
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('ssi', $nombre, $descripcion, $flag);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }

    public function actualizar(int $id, string $nombre, ?string $descripcion = null, bool $activo = true): bool
    {
        $sql = 'UPDATE tipo_equipo SET Nombre = ?, Descripcion = ?, Active = ? WHERE ID_TipoEquipo = ?';
        $flag = $activo ? 1 : 0;
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('ssii', $nombre, $descripcion, $flag, $id);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $sql = 'UPDATE tipo_equipo SET Active = ? WHERE ID_TipoEquipo = ?';
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