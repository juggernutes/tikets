<?php
require_once __DIR__ . '/../helpers/encryption_helper.php';

class equipo
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function obtenerEquiposEmpleado($numeroEmpleado)
    {
        $equipos = [];
        $Op_     = 1;

        $p_ID_Equipo           = null;
        $p_ID_TipoEquipo       = null;
        $p_Marca               = '';
        $p_Modelo              = '';
        $p_NumeroSerie         = '';
        $p_IPDireccion         = '';
        $p_MacDireccion        = '';
        $p_NuActvoFijo         = '';
        $p_SistemaOperativo    = '';
        $p_ClaveUsuarioWindows = '';
        $p_Descripcion         = '';
        $p_FechaCompra         = null;
        $p_Active              = null;

        $params = [
            $Op_,
            $numeroEmpleado,$p_ID_Equipo,$p_ID_TipoEquipo,$p_Marca,$p_Modelo,$p_NumeroSerie,$p_IPDireccion,$p_MacDireccion,$p_NuActvoFijo,
            $p_SistemaOperativo,$p_ClaveUsuarioWindows,$p_Descripcion,$p_FechaCompra,$p_Active
        ];
                                    
        $types = 'iiiisssssssssii'; 

        if ($stmt = $this->conn->prepare("CALL sp_equipo(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $equipos[] = $row;
            }
            $stmt->close();
            while ($this->conn->more_results() && $this->conn->next_result()) {
                $this->conn->use_result();
            }
        }
        return $equipos;
    }

    public function obtenerEquipoPorId($Id_Equipo)
    {

    }

}
